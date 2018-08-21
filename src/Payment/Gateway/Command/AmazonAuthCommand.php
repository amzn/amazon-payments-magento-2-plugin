<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amazon\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;
use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Payment\Gateway\Config\Config;

/**
 * Class AmazonAuthCommand
 *
 * Enables customized error handling for Amazon Payment
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AmazonAuthCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorMessageMapperInterface
     */
    private $errorMessageMapper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param HandlerInterface $handler
     * @param ValidatorInterface $validator
     * @param ErrorMessageMapperInterface|null $errorMessageMapper
     * @param Config $config
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ErrorMessageMapperInterface $errorMessageMapper = null,
        Config $config
    )
    {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->errorMessageMapper = $errorMessageMapper;
        $this->config = $config;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface|null|void
     * @throws AmazonWebapiException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function execute(array $commandSubject)
    {
        $isTimeout = 0;

        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->client->placeRequest($transferO);
        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                // when Amazon Pay is set to receive asynchronous calls, we need to allow timeouts to pass validation and
                // flag the handler to save the order for later processing.
                $auth_mode = '';
                if (isset($response['auth_mode'])) {
                    $auth_mode = $response['auth_mode'];
                }
                $isTimeout = $this->processErrors($result, $auth_mode);
            }
        }

        $response['timeout'] = $isTimeout;

        if ($isTimeout) {
            $response['status'] = true;
        }

        if ($this->handler) {
            $this->handler->handle(
                $commandSubject,
                $response
            );
        }
    }

    /**
     * Tries to map error messages from validation result and logs processed message.
     * Throws an exception with mapped message or default error.
     *
     * @throws AmazonWebapiException
     */
    private function processErrors(ResultInterface $result, $mode = '')
    {

        $isDecline = false;
        $isTimeout = false;
        $code = false;
        $messages = [];
        foreach ($result->getFailsDescription() as $failPhrase) {
            $message = (string)$failPhrase;

            if ($this->errorMessageMapper !== null) {
                $mapped = (string)$this->errorMessageMapper->getMessage($message);
                if (!empty($mapped) && !in_array($mapped, $messages)) {
                    $messages[] = $mapped;
                }
            }

            $this->logger->critical('Payment Error: ' . $message . ': ' . $mapped);

            if ($message == 'AmazonRejected' || $message == 'TransactionTimedOut') {
                $code = (int)$this->config->getValue('hard_decline_code');
                $isDecline = true;
            } elseif ($message == 'InvalidPaymentMethod' || $message == 'Declined') {
                $code = (int)$this->config->getValue('soft_decline_code');
            }

            if ($mode == 'synchronous_possible' && $message == 'TransactionTimedOut') {
                $isTimeout = true;
                $isDecline = false;
            }
        }

        if ($isDecline) {
            $messages[] = __("You will be redirected to the cart shortly.");
        }

        if ($isTimeout) {
            return true;
        }

        throw new AmazonWebapiException(
            !empty($messages)
                ? __(implode(PHP_EOL, $messages))
                : __('Transaction has been declined. Please try again later.'),
            $code
        );

        return $false;
    }
}
