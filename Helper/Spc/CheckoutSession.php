<?php

namespace Amazon\Pay\Helper\Spc;

use Amazon\Pay\Logger\Logger;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

class CheckoutSession
{
    /**
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Logger $logger
     */
    public function __construct(
        AmazonPayAdapter $amazonPayAdapter,
        Logger $logger
    )
    {
        $this->amazonPayAdapter = $amazonPayAdapter;
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
        $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

        // check status code
        $amazonSessionStatus = $amazonSession['status'] ?? '404';
        if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
            $this->logger->logError(
                'SPC ShippingMethod: '. $amazonSession['reasonCode'] .'. CartId: '. $quote->getId() .' - ', $cartDetails
            );

            throw new WebapiException(
                new Phrase($amazonSession['reasonCode'])
            );
        }

        // check if still open
        if ($amazonSession['statusDetails']['state'] !== 'Open') {
            $this->logger->logError(
                'SPC ShippingMethod: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $quote->getId() .' - ', $cartDetails
            );

            throw new WebapiException(
                new Phrase($amazonSession['statusDetails']['reasonCode'])
            );
        }

        return $amazonSession;
    }
}
