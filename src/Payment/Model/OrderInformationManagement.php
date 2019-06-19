<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Payment\Model;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Core\Exception\AmazonServiceUnavailableException;
use Amazon\Core\Helper\Data as CoreHelper;
use Amazon\Payment\Gateway\Config\Config;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Payment\Api\OrderInformationManagementInterface;
use Amazon\Payment\Domain\AmazonSetOrderDetailsResponse;
use Amazon\Payment\Domain\AmazonSetOrderDetailsResponseFactory;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use AmazonPay\ResponseInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderInformationManagement implements OrderInformationManagementInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var AmazonSetOrderDetailsResponseFactory
     */
    private $amazonSetOrderDetailsResponseFactory;

    /*
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * OrderInformationManagement constructor.
     * @param Session $session
     * @param ClientFactoryInterface $clientFactory
     * @param CoreHelper $coreHelper
     * @param Config $config
     * @param AmazonSetOrderDetailsResponseFactory $amazonSetOrderDetailsResponseFactory
     * @param QuoteLinkInterfaceFactory $quoteLinkFactory
     * @param LoggerInterface $logger
     * @param ProductMetadata $productMetadata
     */
    public function __construct(
        Session $session,
        ClientFactoryInterface $clientFactory,
        CoreHelper $coreHelper,
        Config $config,
        AmazonSetOrderDetailsResponseFactory $amazonSetOrderDetailsResponseFactory,
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        LoggerInterface $logger,
        ProductMetadata $productMetadata,
        UrlInterface $urlBuilder = null
    ) {
        $this->session                              = $session;
        $this->clientFactory                        = $clientFactory;
        $this->coreHelper                           = $coreHelper;
        $this->config                               = $config;
        $this->amazonSetOrderDetailsResponseFactory = $amazonSetOrderDetailsResponseFactory;
        $this->quoteLinkFactory                     = $quoteLinkFactory;
        $this->logger                               = $logger;
        $this->productMetadata                      = $productMetadata;
        $this->urlBuilder = $urlBuilder ?: ObjectManager::getInstance()->get(UrlInterface::class);
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderInformation($amazonOrderReferenceId, $allowedConstraints = [])
    {
        try {
            $quote   = $this->session->getQuote();
            $storeId = $quote->getStoreId();

            $this->validateCurrency($quote->getQuoteCurrencyCode());

            $this->setReservedOrderId($quote);

            $storeName = $this->coreHelper->getStoreName(ScopeInterface::SCOPE_STORE, $storeId);
            if (!$storeName) {
                $storeName = $quote->getStore()->getName();
            }

            $data = [
                'amazon_order_reference_id' => $amazonOrderReferenceId,
                'amount'                    => $quote->getGrandTotal(),
                'currency_code'             => $quote->getQuoteCurrencyCode(),
                'store_name'                => $storeName,
                'custom_information'        =>
                    'Magento Version : ' . $this->productMetadata->getVersion() . ' ' .
                    'Plugin Version : ' . $this->coreHelper->getVersion()
                ,
                'platform_id'               => $this->config->getValue('platform_id')
            ];

            $responseParser = $this->clientFactory->create($storeId)->setOrderReferenceDetails($data);
            $response       = $this->amazonSetOrderDetailsResponseFactory->create(
                [
                'response' => $responseParser
                ]
            );

            $this->validateConstraints($response, $allowedConstraints);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            throw new AmazonServiceUnavailableException();
        }
    }

    protected function validateCurrency($code)
    {
        if ($this->coreHelper->getCurrencyCode() !== $code) {
            throw new LocalizedException(__('The currency selected is not supported by Amazon Pay'));
        }
    }

    protected function validateConstraints(AmazonSetOrderDetailsResponse $response, $allowedConstraints)
    {
        foreach ($response->getConstraints() as $constraint) {
            if (! in_array($constraint->getId(), $allowedConstraints)) {
                throw new ValidatorException(__($constraint->getErrorMessage()));
            }
        }
    }

    protected function setReservedOrderId(Quote $quote)
    {
        if (! $quote->getReservedOrderId()) {
            $quote
                ->reserveOrderId()
                ->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function confirmOrderReference($amazonOrderReferenceId, $storeId = null)
    {
        try {
            $response = $this->clientFactory->create($storeId)->confirmOrderReference(
                [
                    'amazon_order_reference_id' => $amazonOrderReferenceId,
                    'success_url' => $this->urlBuilder->getUrl('amazonpayments/payment/completecheckout'),
                    'failure_url' => $this->urlBuilder->getUrl('amazonpayments/payment/completecheckout')
                ]
            );

            $this->validateResponse($response);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            throw new AmazonServiceUnavailableException();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function closeOrderReference($amazonOrderReferenceId, $storeId = null)
    {
        try {
            $response = $this->clientFactory->create($storeId)->closeOrderReference(
                [
                    'amazon_order_reference_id' => $amazonOrderReferenceId
                ]
            );

            $this->validateResponse($response);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            throw new AmazonServiceUnavailableException();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function cancelOrderReference($amazonOrderReferenceId, $storeId = null)
    {
        try {
            $response = $this->clientFactory->create($storeId)->cancelOrderReference(
                [
                    'amazon_order_reference_id' => $amazonOrderReferenceId
                ]
            );

            $this->validateResponse($response);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            throw new AmazonServiceUnavailableException();
        }
    }

    protected function validateResponse(ResponseInterface $response)
    {
        $data = $response->toArray();

        if (200 != $data['ResponseStatus']) {
            throw new AmazonServiceUnavailableException();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeOrderReference()
    {
        $quote = $this->session->getQuote();
        
        if ($quote->getId()) {
            $quoteLink = $this->quoteLinkFactory->create()->load($quote->getId(), 'quote_id');

            if ($quoteLink->getId()) {
                $quoteLink->delete();
            }
        }
    }
}
