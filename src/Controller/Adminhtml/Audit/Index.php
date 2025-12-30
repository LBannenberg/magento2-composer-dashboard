<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Controller\Adminhtml\Audit;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Corrivate_ComposerDashboard::composerdashboard';

    public function __construct(private readonly PageFactory $resultPageFactory)
    {
    }

    public function execute(): ResultInterface
    {
        $title = 'Composer > Security Advisories';
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system'); // @phpstan-ignore method.notFound
        $resultPage->addBreadcrumb(__($title), __($title)); // @phpstan-ignore method.notFound
        $resultPage->getConfig()->getTitle()->prepend((string)__($title));
        return $resultPage;
    }
}

