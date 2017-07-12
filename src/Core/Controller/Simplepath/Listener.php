<?php

namespace Amazon\Core\Controller\Simplepath;

class Listener extends \Magento\Framework\App\Action\Action
{

    // @var \Magento\Framework\Controller\Result\JsonFactory
    protected $jsonResultFactory;

    // @var \Amazon\Core\Model\Config\SimplePath
    protected $simplepath;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Amazon\Core\Model\Config\SimplePath $simplepath,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig

    ) {
        $this->simplepath        = $simplepath;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->scopeConfig       = $scopeConfig;
        parent::__construct($context);
    }


    /**
     * Parse POST request from Amazon and import keys
     */
    public function execute()
    {

        $url = parse_url(\Amazon\Core\Model\Config\SimplePath::API_ENDPOINT_DOWNLOAD_KEYS);

        header('Access-Control-Allow-Origin: https://' . $url['host']);
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: Content-Type');

        $payload = $this->_request->getParam('payload');

        $result = $this->jsonResultFactory->create();

        $return = array('result' => 'error', 'message' => 'Empty response.');

        try {
            if ($payload && strpos($payload, 'encryptedKey') !== FALSE) {

                $json = $this->simplepath->decryptPayload($payload, false);

                if ($json) {
                    $return = array('result' => 'success');
                }
            } else {
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
                $return = array('result' => 'error', 'message' => 'payload parameter not found.');
            }

        } catch (Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            $return = array('result' => 'error', 'message' => $e->getMessage());
        }

        $result->setData($return);

        return $result;
    }


    /**
     * Overridden to allow POST without form key
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if ($this->_auth->isLoggedIn()) {
            if ($this->_backendUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode(
                        ['error' => true, 'message' => $_keyErrorMsg]
                    )
                );
            } else {
                $this->_redirect($this->_backendUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

}
