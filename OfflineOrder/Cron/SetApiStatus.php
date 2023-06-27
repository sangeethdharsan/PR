<?php

namespace TraversMx\OfflineOrder\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use TraversMx\Inventory\Model\APIWrapper;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use TraversMx\OfflineOrder\Api\AdminConfigInterface;

class SetApiStatus
{

    /**
     * @var \TraversMx\Inventory\Model\APIWrapper
     */
    private APIWrapper $apiWrapper;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    protected TypeListInterface $cacheTypeList;
    
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool $cacheTypeList
     */
    protected Pool $cacheFrontendPool;

    /**
     * @var \TraversMx\OfflineOrder\Api\AdminConfigInterface $scopeConfig
     */
    protected AdminConfigInterface $scopeConfig;

    /**
     * @param APIWrapper $apiWrapper
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param AdminConfig $scopeConfig
     */
    public function __construct(
        APIWrapper       $apiWrapper,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        AdminConfigInterface $scopeConfig
    ) {
        $this->apiWrapper = $apiWrapper;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check the api status to set the api config enable or disable
     */
    public function execute()
    {
        $_type = 'config';
        $setValue = 1;
        $response = $this->apiWrapper->getInventoryApiStatus(
            $this->scopeConfig->getConfigData(AdminConfigInterface::SKU)
        );
        $bodyStatus = false;
        if (!empty($response['body'])) {
            if (array_key_exists('status', $response['body'])) {
                $bodyStatus = $response['body']['status'] != 200 ? true : false;
            }
        }
        if ($response['status'] != 200 || $bodyStatus) {
            $setValue = 0;
        }

        $currentStatus = $this->scopeConfig->getConfigData(AdminConfigInterface::API_CONFIG_PATH);
        if ($setValue != $currentStatus) {
            $this->scopeConfig->saveConfigData(AdminConfigInterface::API_CONFIG_PATH, $setValue, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->cacheTypeList->cleanType($_type);
            $this->cacheTypeList->cleanType('reflection');
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }
        return $this;
    }
}
