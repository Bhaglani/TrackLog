<?php
namespace MageNova\TrackLogger\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

/**
 * Class ViewLogs
 *
 * Controller to view log files in admin panel
 */
class ViewLogs extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var FileDriver
     */
    protected $fileDriver;

    /**
     * ViewLogs constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param DirectoryList $directoryList
     * @param FileDriver $fileDriver
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        DirectoryList $directoryList,
        FileDriver $fileDriver
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $file = $this->getRequest()->getParam('file');

        // Sanitize file name to prevent directory traversal
        // Remove any characters that could be used for path traversal
        $file = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file);

        // Get Magento root and log directory
        $rootDir = $this->directoryList->getRoot();
        $logDir = $rootDir . '/var/log';

        // Full path to the log file
        // basename() removed as sanitization already ensures no path separators exist
        $filePath = $logDir . '/' . $file;

        if (!$this->fileDriver->isExists($filePath)) {
            return $result->setData([
                'success' => false,
                'message' => __('Log file not found.')
            ]);
        }

        try {
            // Read last 1000 lines using Magento file driver
            $lines = $this->tailFile($filePath, 1000);
            return $result->setData([
                'success' => true,
                'content' => $lines
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('Error reading log file: %1', $e->getMessage())
            ]);
        }
    }

    /**
     * Get last N lines from a file
     *
     * @param string $file
     * @param int $lines
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function tailFile($file, $lines = 1000)
    {
        $fileSize = $this->fileDriver->stat($file)['size'] ?? 0;
        $buffer = '';
        $chunkSize = 4096;
        $pos = $fileSize;

        $handle = $this->fileDriver->fileOpen($file, 'r');

        while ($pos > 0 && $lines >= 0) {
            $seek = max($pos - $chunkSize, 0);
            $readSize = $pos - $seek;
            $this->fileDriver->fileSeek($handle, $seek);
            $chunk = $this->fileDriver->fileRead($handle, $readSize);
            $buffer = $chunk . $buffer;
            $pos = $seek;
            $lines = substr_count($chunk, "\n") >= $lines ? -1 : $lines - substr_count($chunk, "\n");
        }

        $this->fileDriver->fileClose($handle);

        $bufferLines = explode("\n", $buffer);
        return implode("\n", array_slice($bufferLines, -1000));
    }
}
