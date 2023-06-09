<?php

namespace TraversMx\OfflineOrder\Model\Order\Email;

use Magento\Sales\Model\Order\Email\SenderBuilder as OrderMailBuilder;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use TraversMx\OfflineOrder\Api\AdminConfigInterface;
use TraversMx\AsyncOrder\Helper\Email;

class SenderBuilder extends OrderMailBuilder
{
    /**
     * @var \TraversMx\OfflineOrder\Api\AdminConfigInterface
     */
    protected AdminConfigInterface $scopeConfig;

    /**
     * @var TraversMx\AsyncOrder\Helper\Email
     */
    protected Email $helperOrder;
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     * @param AdminConfigInterface $scopeConfig
     * @param Email $helperOrder
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null,
        AdminConfigInterface $scopeConfig,
        Email $helperOrder
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
        $this->scopeConfig = $scopeConfig;
        $this->helperOrder = $helperOrder;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
        $this->configureEmailTemplate();
        $order = $this->helperOrder->getOrder();
        $axIntegrationStatus = '';
        if ($order) {
            $axIntegrationStatus = $order->getAxIntegrationStatus()??'';
            if ($axIntegrationStatus == 'failed' && $order->getAxRetryCount() === null) {
                $this->identityContainer->setCustomerEmail(
                    $this->scopeConfig->getConfigData(AdminConfigInterface::CUSTOMER_SUPPORT_EMAIL)
                );
                $this->identityContainer->setCustomerName(
                    $this->scopeConfig->getConfigData(AdminConfigInterface::CUSTOMER_SUPPORT_NAME)
                );
            }
        }

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $copyTo = $this->identityContainer->getEmailCopyTo();
        if ($axIntegrationStatus != 'failed') {
            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyTo()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();
        $order = $this->helperOrder->getOrder();
        if ($order) {
            if (!empty($copyTo) && $order->getAxIntegrationStatus() != 'failed') {
                foreach ($copyTo as $email) {
                    $this->configureEmailTemplate();
                    $this->transportBuilder->addTo($email);
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                }
            }
        } else {
            if (!empty($copyTo)) {
                foreach ($copyTo as $email) {
                    $this->configureEmailTemplate();
                    $this->transportBuilder->addTo($email);
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                }
            }
        }
    }
}
