<?php

namespace Corrivate\ComposerDashboard\Cron;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class SendAdvisoryReminders
{
    private const TEMPLATE = 'corrivate_composer_security_advisories';

    public function __construct(
        private readonly Settings         $settings,
        private readonly Audit            $audit,
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface   $inlineTranslation,
        private readonly LoggerInterface  $logger
    ) {
    }

    public function execute(): void
    {
        if (!$this->settings->getAdvisoryRecipients()) {
            return; // Nobody listening, boo!
        }

        if (!$this->audit->getRows(forceRefresh: true)) {
            return; // No security advisories, yay!
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
                    'store_url' => $this->settings->getStoreUrl()
                    // package information will be fetched in the block
                ])
                ->setFromByScope($this->settings->getSender());
            foreach ($this->settings->getAdvisoryRecipients() as $recipient) {
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
