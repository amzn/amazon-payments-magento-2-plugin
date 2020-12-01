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
namespace Amazon\Core\Model\Config\Credentials;

use Amazon\Core\Helper\Data;
use Amazon\Core\Model\Validation\JsonConfigDataValidatorFactory;
use Amazon\Core\Model\Config\SimplePath;
use Magento\Config\Model\ResourceModel\Config as ConfigWriter;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Json
{
    const AMAZON_CONFIG_PREFIX = 'payment/amazon_payment/';
    const AMAZON_CREDENTIALS_JSON = 'credentials_json';

    /**
     * @var Data
     */
    private $amazonCoreHelper;

    /**
     * @var JsonConfigDataValidatorFactory
     */
    private $jsonConfigDataValidatorFactory;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var EncryptorInterface $encryptor
     */
    private $encryptor;

    /**
     * @var SimplePath
     */
    private $simplePath;

    /**
     * @param Data                           $amazonCoreHelper
     * @param JsonConfigDataValidatorFactory $jsonConfigDataValidator
     * @param ConfigWriter                   $configWriter
     * @param MessageManager                 $messageManager
     * @param DecoderInterface               $jsonDecoder
     * @param EncryptorInterface             $encryptor
     */
    public function __construct(
        Data $amazonCoreHelper,
        JsonConfigDataValidatorFactory $jsonConfigDataValidator,
        ConfigWriter $configWriter,
        MessageManager $messageManager,
        DecoderInterface $jsonDecoder,
        EncryptorInterface $encryptor,
        SimplePath $simplePath
    ) {
        $this->amazonCoreHelper               = $amazonCoreHelper;
        $this->jsonConfigDataValidatorFactory = $jsonConfigDataValidator;
        $this->configWriter                   = $configWriter;
        $this->messageManager                 = $messageManager;
        $this->jsonDecoder                    = $jsonDecoder;
        $this->encryptor                      = $encryptor;
        $this->simplePath                     = $simplePath;
    }

    /**
     * @param string $jsonCredentials
     * @param array  $scopeData
     */
    public function processCredentialsJson($jsonCredentials, $scopeData)
    {
        $validator = $this->jsonConfigDataValidatorFactory->create();

        if ($validator->isValid($jsonCredentials)) {
            $this->applyCredentialsFromJson($jsonCredentials, $scopeData);
        }

        foreach ($validator->getMessages() as $message) {
            $this->messageManager->addErrorMessage($message);
        }
    }

    protected function applyCredentialsFromJson($jsonCredentials, $scopeData)
    {
        $arrayCredentials = $this->jsonDecoder->decode($jsonCredentials);
        $this->wipeJsonCredentialsConfig($scopeData);

        // Decrypt SimplePath JSON
        if (isset($arrayCredentials['encryptedKey'])) {
            $arrayCredentials = $this->jsonDecoder->decode(
                $this->simplePath->decryptPayload(
                    json_encode($arrayCredentials),
                    false,
                    false
                )
            );
        }

        foreach ($this->amazonCoreHelper->getAmazonCredentialsFields() as $mandatoryField) {
            $valueToSave     = $arrayCredentials[$mandatoryField];
            $encryptedFields = array_flip($this->amazonCoreHelper->getAmazonCredentialsEncryptedFields());

            if (isset($encryptedFields[$mandatoryField])) {
                $valueToSave = $this->encryptor->encrypt($valueToSave);
            }

            $this->configWriter->saveConfig(
                self::AMAZON_CONFIG_PREFIX . $mandatoryField,
                $valueToSave,
                $scopeData['scope'],
                $scopeData['scope_id']
            );
        }
    }

    protected function wipeJsonCredentialsConfig($scopeData)
    {
        $this->configWriter->deleteConfig(
            self::AMAZON_CONFIG_PREFIX . self::AMAZON_CREDENTIALS_JSON,
            $scopeData['scope'],
            $scopeData['scope_id']
        );
    }
}
