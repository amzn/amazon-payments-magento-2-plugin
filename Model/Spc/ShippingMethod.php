<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\ShippingMethod as ShippingMethodHelper;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\CartRepositoryInterface;

class ShippingMethod implements ShippingMethodInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var ShippingMethodHelper
     */
    protected $shippingMethodHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     * @param ShippingMethodHelper $shippingMethodHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        ShippingMethodHelper $shippingMethodHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
        $this->shippingMethodHelper = $shippingMethodHelper;
    }

    /**
     * @inheritdoc
     */
    public function shippingMethod(int $cartId, $cartDetails = null)
    {
        // Get quote
        try {
            /** @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->cartRepository->getActive($cartId);
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC ShippingMethod: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase('InvalidCartId'), 404, 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Check checkout session to
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                $this->cartHelper->logError(
                    'SPC ShippingMethod: '. $amazonSession['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
                $this->cartHelper->logError(
                    'SPC ShippingMethod: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['statusDetails']['reasonCode'])
                );
            }

            // Set the shipping method
            $methodCode = $cartDetails['delivery_options'][0]['shipping_method']['shipping_method_code'] ?? false;

            $this->shippingMethodHelper->setShippingMethodOnQuote($quote, $methodCode);
        }

        // Construct response
        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
