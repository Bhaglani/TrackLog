<?php
namespace MageNova\TrackLogger\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Escaper;

/**
 * Class Logs
 *
 * Block for displaying log files in admin panel
 */
class Logs extends Template
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $ioFile;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Logs constructor.
     *
     * @param Template\Context $context
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param File $ioFile
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        File $ioFile,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->escaper = $escaper;
    }

    /**
     * Return Escaper instance
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Get list of log files from log directory
     *
     * @return array
     */
    public function getLogFiles()
    {
        $logDir = $this->directoryList->getPath(DirectoryList::LOG);
        $files = Glob::glob($logDir . '/*.log');
        $result = [];

        if (is_array($files)) {
            foreach ($files as $file) {
                $fileInfo = $this->ioFile->getPathInfo($file);
                $fileName = $fileInfo['basename'] ?? '';
                $result[$fileName] = $fileName;
            }
        }

        return $result;
    }

    /**
     * Get AJAX URL for viewing logs
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('magenovalog/log/viewlogs');
    }
}
