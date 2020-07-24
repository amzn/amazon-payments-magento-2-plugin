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
use Amazon\Core\Domain\AmazonAddressFactory;
use Amazon\Core\Exception\AmazonServiceUnavailableException;
use Amazon\Payment\Api\AddressManagementInterface;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Payment\Helper\Address;
use Amazon\Payment\Domain\AmazonOrderStatus;
use Amazon\Payment\Domain\AmazonAuthorizationStatus;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Model\Quote;
use AmazonPay\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressManagement implements AddressManagementInterface
{
    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var Address
     */
    private $addressHelper;

    /**
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var AmazonAddressFactory
     */
    private $amazonAddressFactory;

    /**
     * @var Factory
     */
    private $validatorFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param ClientFactoryInterface    $clientFactory
     * @param Address                   $addressHelper
     * @param QuoteLinkInterfaceFactory $quoteLinkFactory
     * @param Session                   $session
     * @param CollectionFactory         $countryCollectionFactory
     * @param AmazonAddressFactory      $amazonAddressFactory
     * @param Factory                   $validatorFactory
     * @param LoggerInterface           $logger
     * @param AddressFactory            $addressFactory
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        Address $addressHelper,
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        Session $session,
        CollectionFactory $countryCollectionFactory,
        AmazonAddressFactory $amazonAddressFactory,
        Factory $validatorFactory,
        LoggerInterface $logger,
        AddressFactory $addressFactory
    ) {
        $this->clientFactory            = $clientFactory;
        $this->addressHelper            = $addressHelper;
        $this->quoteLinkFactory         = $quoteLinkFactory;
        $this->session                  = $session;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->amazonAddressFactory     = $amazonAddressFactory;
        $this->validatorFactory         = $validatorFactory;
        $this->logger                   = $logger;
        $this->addressFactory           = $addressFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress($amazonOrderReferenceId, $addressConsentToken)
    {
        try {
            $data = $this->getOrderReferenceDetails($amazonOrderReferenceId, $addressConsentToken);

            if ($this->isSuspendedStatus($data)) {
                throw new WebapiException(__('There has been a problem with the selected payment method on your ' .
                    'Amazon account. Please choose another one.'));
            }

            $this->updateQuoteLink($amazonOrderReferenceId);

            if (isset($data['OrderReferenceDetails']['Destination']['PhysicalDestination'])) {
                $shippingAddress = $data['OrderReferenceDetails']['Destination']['PhysicalDestination'];

                return $this->convertToMagentoAddress($shippingAddress, true);
            }

            throw new Exception();
        } catch (SessionException $e) {
            throw $e;
        } catch (WebapiException $e) {
            throw $e;
        } catch (ValidatorException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            $this->throwUnknownErrorException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress($amazonOrderReferenceId, $addressConsentToken)
    {
        try {
            $data = $this->getOrderReferenceDetails($amazonOrderReferenceId, $addressConsentToken);

            $this->updateQuoteLink($amazonOrderReferenceId);

            // Re-open suspended InvalidPaymentMethod decline during ConfirmOrderReference
            $this->session->setData('is_amazon_suspended', $this->isSuspendedStatus($data));

            if (isset($data['OrderReferenceDetails']['BillingAddress']['PhysicalAddress'])) {
                $billingAddress = $data['OrderReferenceDetails']['BillingAddress']['PhysicalAddress'];
                if (!isset($billingAddress['Phone']) || !$billingAddress['Phone']) {
                    $billingAddress['Phone'] = '000-000-0000';
                }

                return $this->convertToMagentoAddress($billingAddress);
            } elseif (isset($data['OrderReferenceDetails']['Destination']['PhysicalDestination'])) {
                $billingAddress = $data['OrderReferenceDetails']['Destination']['PhysicalDestination'];

                return $this->convertToMagentoAddress($billingAddress);
            }

            throw new Exception();
        } catch (WebapiException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->throwUnknownErrorException();
        }
    }

    protected function throwUnknownErrorException()
    {
        throw new WebapiException(
            __('Amazon could not process your request.'),
            0,
            WebapiException::HTTP_INTERNAL_ERROR
        );
    }

    protected function convertToMagentoAddress(array $address, $isShippingAddress = false)
    {
        $amazonAddress  = $this->amazonAddressFactory->create(['address' => $address]);
        $magentoAddress = $this->addressHelper->convertToMagentoEntity($amazonAddress);

        if ($isShippingAddress) {
            $validator = $this->validatorFactory->createValidator('amazon_address', 'on_select');

            if (! $validator->isValid($magentoAddress)) {
                throw new ValidatorException(null, null, [$validator->getMessages()]);
            }

            $countryCollection = $this->countryCollectionFactory->create();

            $collectionSize = $countryCollection->loadByStore()
                ->addFieldToFilter('country_id', ['eq' => $magentoAddress->getCountryId()])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getSize();

            if (1 != $collectionSize) {
                throw new WebapiException(__('the country for your address is not allowed for this store'));
            }

            // Validate address
            $validate = $this->addressFactory->create()->updateData($magentoAddress)->validate();
            if (is_array($validate)) {
                $validate[] = __('Your address may be updated in your Amazon account.');
                throw new ValidatorException(null, null, [$validate]);
            }
        }

        return [$this->addressHelper->convertToArray($magentoAddress)];
    }

    protected function getOrderReferenceDetails($amazonOrderReferenceId, $addressConsentToken)
    {
        $client = $this->clientFactory->create();

        /**
         * @var ResponseInterface $response
         */
        $response = $client->getOrderReferenceDetails(
            [
                'amazon_order_reference_id' => $amazonOrderReferenceId,
                'address_consent_token'     => $addressConsentToken
            ]
        );

        $data = $response->toArray();

        if (200 != $data['ResponseStatus'] || ! isset($data['GetOrderReferenceDetailsResult'])) {
            throw new AmazonServiceUnavailableException();
        }

        return $data['GetOrderReferenceDetailsResult'];
    }

    protected function updateQuoteLink($amazonOrderReferenceId)
    {
        $quote = $this->session->getQuote();

        if (! $quote->getId()) {
            throw new SessionException(__('Your session has expired, please reload the page and try again.'));
        }

        $quoteLink = $this->quoteLinkFactory->create()->load($quote->getId(), 'quote_id');

        if ($quoteLink->getAmazonOrderReferenceId() != $amazonOrderReferenceId) {
            $quoteLink
                ->setAmazonOrderReferenceId($amazonOrderReferenceId)
                ->setQuoteId($quote->getId())
                ->setConfirmed(false)
                ->save();
        }
    }

    protected function isSuspendedStatus($data)
    {
        $orderStatus = $data['OrderReferenceDetails']['OrderReferenceStatus'] ?? false;

        return $orderStatus && $orderStatus['State'] == AmazonOrderStatus::STATE_SUSPENDED;
    }
}
