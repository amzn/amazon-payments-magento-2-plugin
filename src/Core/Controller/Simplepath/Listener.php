<?php

namespace Amazon\Core\Controller\Simplepath;

class Listener extends \Magento\Framework\App\Action\Action
{

    // @var \Magento\Framework\Controller\Result\JsonFactory
    private $jsonResultFactory;

    // @var \Amazon\Core\Model\Config\SimplePath
    private $simplepath;

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
        $url = parse_url($this->simplepath->getEndpointRegister());

        header('Access-Control-Allow-Origin: https://' . $url['host']);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

        $payload = $this->_request->getParam('payload');

        $result = $this->jsonResultFactory->create();

        $return = ['result' => 'error', 'message' => 'Empty payload'];

        try {
            if (strpos($payload, 'encryptedKey') === false) {
                $return = ['result' => 'error', 'message' => 'Invalid payload: ' . $payload];
            } else if ($payload) {
                $json = $this->simplepath->decryptPayload($payload, false);

                if ($json) {
                    $return = ['result' => 'success'];
                }
            } else {
                $return = ['result' => 'error', 'message' => 'payload parameter not found.'];
            }
        } catch (\Exception $e) {
            $return = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if ($this->_request->isPost() && (empty($return['result']) || $return['result'] == 'error')) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
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
                        \Magento\Framework\Json\Helper\Data::class
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
