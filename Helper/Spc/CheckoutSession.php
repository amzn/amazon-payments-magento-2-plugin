<?php

namespace Amazon\Pay\Helper\Spc;

use Amazon\Pay\Api\CheckoutSessionManagementInterface;
use Amazon\Pay\Logger\Logger;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

class CheckoutSession
{
    /**
     * @var CheckoutSessionManagementInterface
     */
    protected $checkoutSessionManagement;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param CheckoutSessionManagementInterface $checkoutSessionManagement
     * @param Logger $logger
     */
    public function __construct(
        CheckoutSessionManagementInterface $checkoutSessionManagement,
        Logger $logger
    )
    {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->logger = $logger;
    }

    /**
     * @param $quote
     * @param $cartDetails
     * @param $checkoutSessionId
     * @return array|mixed
     * @throws WebapiException
     */
    public function confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)
    {
        // load session
        $amazonSession = $this->checkoutSessionManagement->getAmazonSession($checkoutSessionId);

        // check status code
        $amazonSessionStatus = $amazonSession['status'] ?? '404';
        if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
            $this->logger->error(
                'SPC ShippingMethod - Session Error: '. $amazonSession['reasonCode'] .'. CartId: '. $quote->getId() .' - ', $cartDetails
            );

            throw new WebapiException(
                new Phrase($amazonSession['message']), $amazonSession['reasonCode'], 400
            );
        }

        // check if still open
        if ($amazonSession['statusDetails']['state'] !== 'Open') {
            $this->logger->error(
                'SPC ShippingMethod: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $quote->getId() .' - ', $cartDetails
            );

            throw new WebapiException(
                new Phrase($amazonSession['statusDetails']['reasonDescription']), $amazonSession['statusDetails']['reasonCode'], 400
            );
        }

        return $amazonSession;
    }

    /**
     * @param $checkoutSessionId
     * @return mixed
     */
    public function getShippingAddress($checkoutSessionId)
    {
        return $this->checkoutSessionManagement->getShippingAddress($checkoutSessionId);
    }

    /**
     * @param $checkoutSessionId
     * @return mixed
     */
    public function getBillingAddress($checkoutSessionId)
    {
        return $this->checkoutSessionManagement->getBillingAddress($checkoutSessionId);
    }
}
