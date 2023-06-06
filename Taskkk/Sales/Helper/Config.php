<?php
declare(strict_types=1);

namespace Base\Sales\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Base\FeatureToggle\Api\Options\ReturnServiceInterface as ToggleReturnService;
use Base\FeatureToggle\Api\Options\FedexTrackingInterface as ToggleFedexTracking;

class Config
{
    final const XML_CONFIG_PATH_SALES_RETURN_SERVICE_URL_PATH = 'sales/return_service/url_template';
    
    final const XML_CONFIG_PATH_SALES_FEDEX_TRACKING_URL_PATH = 'shipping/fedex_tracking/url_template';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ToggleReturnService $toggleReturnService
     * @param ToggleFedexTracking $toggleFedexTracking
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ToggleReturnService $toggleReturnService,
        private readonly ToggleFedexTracking $toggleFedexTracking
    ){}

    /**
     * Returns sales order return service url
     */
    public function getReturnServiceUrlTemplate(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_SALES_RETURN_SERVICE_URL_PATH);
    }

    /**
     * Returns fedex tracking url
     */
    public function getFedexTrackingUrlTemplate(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_SALES_FEDEX_TRACKING_URL_PATH);
    }

    /**
     * Check if return service is enabled from launch darkly
     */
    public function isReturnServiceEnabled(): bool
    {
        return $this->toggleReturnService->isReturnServiceEnabled();
    }

    /**
     * Check if fedex trackinng is enabled from launch darkly
     */
    public function isFedexTrackingEnabled(): bool
    {
        return $this->toggleFedexTracking->isFedexTrackingEnabled();
    }
}
