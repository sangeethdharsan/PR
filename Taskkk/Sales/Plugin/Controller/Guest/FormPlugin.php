<?php

namespace Base\Sales\Plugin\Controller\Guest;

use Magento\Sales\Controller\Guest\Form as GuestFormController;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\Result\Redirect;
use Base\Sales\Helper\Config;
use Magento\Framework\View\Result\Page;

class FormPlugin
{
    /**
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param UrlInterface $urlBuilder
     * @param Config $config
     */
    public function __construct(
        private readonly ResultRedirectFactory $resultRedirectFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly Config $config
    ){}

    /**
     * Disable default guest rma form if returns portal service enabled
     *
     * @return Redirect
     */
    public function aroundExecute(GuestFormController $subject, \Closure $proceed)
    {
        return $this->resultRedirectFactory->create()->setUrl(
            $this->urlBuilder->getUrl('noroute')
        );
    }
}
