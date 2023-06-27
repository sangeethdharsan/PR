<?php
declare(strict_types=1);

namespace Base\Sales\Plugin\Model\Method;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CancelOrderLogger
{
    /**
     * @param State $state
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly State $state,
        private readonly LoggerInterface $logger,
        private readonly RequestInterface $request,
        private readonly SerializerInterface $serializer
    ){}

    /**
     * @param $result
     * @return mixed
     */
    public function afterCancel(AbstractMethod $subject, $result, InfoInterface $payment)
    {
        try {
            $this->addLogMessage('Order ID: ' . $payment->getOrder()->getIncrementId());
            $this->addLogMessage('Area Code: ' . $this->state->getAreaCode());
            $params = $this->serializer->serialize($this->request->getParams());
            $this->addLogMessage('Params: ' . $params);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    private function addLogMessage(string $message)
    {
        $this->logger->info('Order Cancel Catcher: ' . $message);
    }
}
