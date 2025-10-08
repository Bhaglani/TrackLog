<?php
namespace MageNova\TrackLogger\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Configuration path for module enable/disable setting
     */
    public const XML_PATH_ENABLED = 'track_logs/logs/enable';

    /**
     * Check if module menu is enabled
     *
     * @return bool
     */
    public function isMenuEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
