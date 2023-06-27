<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TraversMx\OfflineOrder\Api;

/**
 * Interface for get set admin config
 * @api
 * @since 101.0.0
 */
interface AdminConfigInterface
{
    /**
     * Invetory Api admin config path
     */
    public const API_CONFIG_PATH = 'inventory_check/general/enable';

    /**
     * Get valid sku
     */
    public const SKU = 'inventory_check/general/get_sku';

    /**
     * General Contact Name
     */
    public const CUSTOMER_SUPPORT_NAME = 'trans_email/ident_general/name';

    /**
     * General Contact Email
     */
    public const CUSTOMER_SUPPORT_EMAIL = 'trans_email/ident_general/email';

    /**
     * Get admin config data
     *
     * @param string $path
     * @param string $store
     * @since 101.0.0
     */
    public function getConfigData(string $path, string $store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    
    /**
     * Save admin config data
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @since 101.0.0
     */
    public function saveConfigData(string $path, $value, $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
}
