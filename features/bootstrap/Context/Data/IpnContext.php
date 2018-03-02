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
namespace Context\Data;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\Order as OrderFixture;
use Fixtures\Transaction as TransactionFixture;
use GuzzleHttp\Client;
use PHPUnit_Framework_Assert;

class IpnContext implements SnippetAcceptingContext
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var TransactionFixture
     */
    private $transactionFixture;

    public function __construct($baseUri)
    {
        $this->client             = new Client(['base_uri' => $baseUri]);
        $this->transactionFixture = new TransactionFixture;
        $this->orderFixture       = new OrderFixture;
    }

    /**
     * @When I receive a capture complete IPN for :email's last order
     */
    public function iReceiveACaptureCompleteIpnForSLastOrder($email)
    {
        $this->sendCaptureIpnForLastOrder($email, 'Completed');
    }

    /**
     * @When I receive a capture declined IPN for :email's last order
     */
    public function iReceiveACaptureDeclinedIpnForSLastOrder($email)
    {
        $this->sendCaptureIpnForLastOrder($email, 'Declined');
    }

    protected function sendCaptureIpnForLastOrder($email, $state)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        $amazonCaptureId = $transaction->getTxnId();

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $captureNotification = $dom->createElement('CaptureNotification');
        $captureDetails      = $dom->createElement('CaptureDetails');
        $amazonCaptureId     = $dom->createElement('AmazonCaptureId', $amazonCaptureId);
        $captureStatus       = $dom->createElement('CaptureStatus');
        $captureState        = $dom->createElement('State', $state);

        $captureStatus->appendChild($captureState);
        $captureDetails->appendChild($captureStatus);
        $captureDetails->appendChild($amazonCaptureId);
        $captureNotification->appendChild($captureDetails);
        $dom->appendChild($captureNotification);

        $postData = $this->getJson($dom, 'PaymentCapture');

        $this->postRequest($postData);
    }

    /**
     * @When I receive a refund declined IPN for :email's last order
     */
    public function iReceiveARefundDeclinedIpnForSLastOrder($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        $amazonRefundId = $transaction->getTxnId();

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $refundNotification = $dom->createElement('RefundNotification');
        $refundDetails      = $dom->createElement('RefundDetails');
        $amazonRefundId     = $dom->createElement('AmazonRefundId', $amazonRefundId);
        $refundStatus       = $dom->createElement('RefundStatus');
        $refundState        = $dom->createElement('State', 'Declined');

        $refundStatus->appendChild($refundState);
        $refundDetails->appendChild($refundStatus);
        $refundDetails->appendChild($amazonRefundId);
        $refundNotification->appendChild($refundDetails);
        $dom->appendChild($refundNotification);

        $postData = $this->getJson($dom, 'PaymentRefund');

        $this->postRequest($postData);
    }

    /**
     * @When I receive a authorization open IPN for :email's last order
     */
    public function iReceiveAAuthorizationOpenIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Open');
    }

    /**
     * @When I receive a authorization soft declined IPN for :email's last order
     */
    public function iReceiveAAuthorizationSoftDeclinedIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Declined', 'InvalidPaymentMethod');
    }

    /**
     * @When I receive a authorize and capture soft declined IPN for :email's last order
     */
    public function iReceiveAAuthorizeAndCaptureSoftDeclinedIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Declined', 'InvalidPaymentMethod', true);
    }

    /**
     * @When I receive a authorize and capture complete IPN for :email's last order
     */
    public function iReceiveAAuthorizeAndCaptureCompleteIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Closed', 'MaxCapturesProcessed', true);
    }

    /**
     * @When I receive a authorization hard declined IPN for :email's last order
     */
    public function iReceiveAAuthorizationHardDeclinedIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Declined', 'AmazonRejected');
    }

    /**
     * @When I receive a authorize and capture hard declined IPN for :email's last order
     */
    public function iReceiveAAuthorizeAndCaptureHardDeclinedIpnForSLastOrder($email)
    {
        $this->sendAuthorizeIpnForLastOrder($email, 'Declined', 'AmazonRejected', true);
    }

    /**
     * @Then a authorization open IPN for :email's last order should be rejected
     */
    public function aAuthorizationOpenIpnForSLastOrderShouldBeRejected($email)
    {
        $hasError = false;

        try {
            $this->sendAuthorizeIpnForLastOrder($email, 'Open');
        } catch (\Exception $e) {
            $hasError = true;
        }

        PHPUnit_Framework_Assert::assertTrue($hasError);
    }

    /**
     * @When I receive a order payment open IPN for :email
     */
    public function iReceiveAOrderPaymentOpenIpnFor($email)
    {
        sleep(315); //allow time for amazon to switch the payment method and mark the order as re open
        $order         = $this->orderFixture->getLastOrderForCustomer($email);
        $amazonOrderId = $order->getExtensionAttributes()->getAmazonOrderReferenceId();

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $orderNotification = $dom->createElement('OrderReferenceNotification');
        $orderDetails      = $dom->createElement('OrderReference');
        $amazonOrderId     = $dom->createElement('AmazonOrderReferenceId', $amazonOrderId);
        $orderStatus       = $dom->createElement('OrderReferenceStatus');
        $orderState        = $dom->createElement('State', 'Open');

        $orderStatus->appendChild($orderState);
        $orderDetails->appendChild($orderStatus);
        $orderDetails->appendChild($amazonOrderId);
        $orderNotification->appendChild($orderDetails);
        $dom->appendChild($orderNotification);

        $postData = $this->getJson($dom, 'OrderReferenceNotification');

        $this->postRequest($postData);
    }

    protected function sendAuthorizeIpnForLastOrder($email, $state, $reasonCode = null, $capture = false)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        $amazonAuthorizationId = $transaction->getTxnId();

        if ($capture) {
            $amazonAuthorizationId = substr_replace(
                $amazonAuthorizationId,
                'A',
                strrpos($amazonAuthorizationId, 'C'),
                1
            );
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $authorizationNotification = $dom->createElement('AuthorizationNotification');
        $authorizationDetails      = $dom->createElement('AuthorizationDetails');
        $amazonAuthorizationId     = $dom->createElement('AmazonAuthorizationId', $amazonAuthorizationId);
        $authorizationStatus       = $dom->createElement('AuthorizationStatus');
        $authorizationState        = $dom->createElement('State', $state);

        if ($reasonCode) {
            $authorizationReasonCode = $dom->createElement('ReasonCode', $reasonCode);
            $authorizationStatus->appendChild($authorizationReasonCode);
        }

        if ($capture) {
            $authorizationCaptureNow = $dom->createElement('CaptureNow', 'true');
            $authorizationDetails->appendChild($authorizationCaptureNow);
        }

        $authorizationStatus->appendChild($authorizationState);
        $authorizationDetails->appendChild($authorizationStatus);
        $authorizationDetails->appendChild($amazonAuthorizationId);
        $authorizationNotification->appendChild($authorizationDetails);
        $dom->appendChild($authorizationNotification);

        $postData = $this->getJson($dom, 'PaymentAuthorize');

        $this->postRequest($postData);
    }

    protected function getJson(\DOMDocument $dom, $notificationType)
    {
        $xml = $dom->saveXML();

        return json_encode(
            [
                'Type'      => 'Notification',
                'MessageId' => '',
                'TopicArn'  => '',
                'Message'   => json_encode(
                    [
                        'NotificationReferenceId' => '',
                        'NotificationType'        => $notificationType,
                        'SellerId'                => '',
                        'ReleaseEnvironment'      => '',
                        'NotificationData'        => $xml
                    ],
                    JSON_UNESCAPED_SLASHES
                )
            ]
        );
    }

    protected function postRequest($postData)
    {
        $response = $this->client->request(
            'POST',
            '/amazonpayments/payment/ipn',
            [
                'body' => $postData
            ]
        );

        return $response->getBody()->getContents();
    }
}