<?php
namespace MageNova\TrackLogger\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File as IoFile;

/**
 * Class Download
 *
 * Controller to handle downloading log files from admin
 */
class Download extends Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var IoFile
     */
    protected $ioFile;

    /**
     * Download constructor.
     *
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param IoFile $ioFile
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        IoFile $ioFile
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
    }

    /**
     * Execute download action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');

        // Sanitize file name
        $file = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file);

        // Get log directory
        $rootDir = $this->directoryList->getRoot();
        $logDir = $rootDir . '/var/log';

        // Use Io\File::getPathInfo()
        $fileInfo = $this->ioFile->getPathInfo($file);
        $fileName = $fileInfo['basename'] ?? '';
        $filePath = $logDir . '/' . $fileName;

        try {
            if (!$this->ioFile->fileExists($filePath)) {
                $this->messageManager->addErrorMessage(__('Log file not found.'));
                return $this->_redirect('*/*/index');
            }

            $content = $this->ioFile->read($filePath);

            // Provide download
            return $this->fileFactory->create(
                $fileName,
                $content,
                'var',
                'application/octet-stream'
            );
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('Error reading log file: %1', $e->getMessage()));
            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An unexpected error occurred: %1', $e->getMessage()));
            return $this->_redirect('*/*/index');
        }
    }
}
