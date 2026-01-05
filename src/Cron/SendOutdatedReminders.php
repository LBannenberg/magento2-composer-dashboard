<?php

namespace Corrivate\ComposerDashboard\Cron;

use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class SendOutdatedReminders
{
    private const TEMPLATE = 'corrivate_composer_outdated_packages';

    public function __construct(
        private readonly Settings          $settings,
        private readonly InstalledPackages $packages,
        private readonly TransportBuilder  $transportBuilder,
        private readonly StateInterface    $inlineTranslation,
        private readonly LoggerInterface   $logger
    )
    {
    }

    public function execute(): void
    {
        if (!$this->settings->getOutdatedRecipients()) {
            return; // Nobody listening, boo!
        }

        $outdated = array_filter(
            $this->packages->getRows(forceFresh: true),
            fn (InstalledPackage $r) => $r->direct && $r->isOutdated()
        );

        if (!$outdated) {
            return; // Everything up to date, yay!
        }

        $this->send();
    }

    private function send(): void
    {
        $this->inlineTranslation->suspend();

        try {
            $this->transportBuilder
                ->setTemplateIdentifier(self::TEMPLATE)
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ])
                ->setTemplateVars([
                    'store_url' => $this->settings->getStoreUrl(),
                    'base64_logo' => $this->settings->getBase64Logo()
                    // package information will be fetched in the block
                ])
                ->setFromByScope($this->settings->getSender());
            foreach ($this->settings->getOutdatedRecipients() as $recipient) {
                $this->transportBuilder->addTo($recipient);
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (MailException|LocalizedException $e) {
            $this->logger->critical($e);
        }

        $this->inlineTranslation->resume();

    }


}
