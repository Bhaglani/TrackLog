<?php
namespace MageNova\TrackLogger\Controller\Adminhtml\Tracklogs;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use MageNova\TrackLogger\Helper\Data;

/**
 * Class Index
 *
 * Admin controller for Track Logs page
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'MageNova_TrackLogger::tracklogs';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Check if module menu is enabled
        if (!$this->helper->isMenuEnabled()) {
            $this->messageManager->addErrorMessage(__('This feature is currently disabled.'));
            return $this->_redirect('admin/dashboard');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageNova_TrackLogger::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Track Logs'));

        return $resultPage;
    }

    /**
     * Check if user has permission to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        // Check both ACL permission and config setting
        return parent::_isAllowed() && $this->helper->isMenuEnabled();
    }
}
