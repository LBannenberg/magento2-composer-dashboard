<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Controller\Adminhtml\Installed;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;


class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Corrivate_ComposerDashboard::composerdashboard';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $title = 'Composer > Installed Packages';
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system'); // @phpstan-ignore method.notFound
        $resultPage->addBreadcrumb(__($title), __($title)); // @phpstan-ignore method.notFound
        $resultPage->getConfig()->getTitle()->prepend((string)__($title));
        return $resultPage;
    }
}
