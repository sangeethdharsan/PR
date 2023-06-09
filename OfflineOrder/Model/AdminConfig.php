<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TraversMx\OfflineOrder\Model;

use TraversMx\OfflineOrder\Api\AdminConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class AdminConfig implements AdminConfigInterface
{

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     */
    public ScopeConfigInterface $scopeConfigInterface;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */

    public LoggerInterface $logger;

    /**
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param WriterInterface $configWriter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        WriterInterface $configWriter,
        LoggerInterface $logger
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->logger = $logger;
    }

    /**
     * Get admin config data
     *
     * @param string $path
     * @param string $store
     */
    public function getConfigData(string $path, string $store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        try {
            return $this->scopeConfigInterface->getValue($path, $store);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Save admin config data
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @since 101.0.0
     */
    public function saveConfigData(string $path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        try {
            return $this->configWriter->save($path, $value, $scope, $scopeId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
