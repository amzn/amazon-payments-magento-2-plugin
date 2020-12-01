<?php
/**
 * Created by PhpStorm.
 * User: Michele
 * Date: 5/3/2018
 * Time: 11:07 AM
 */

namespace Amazon\Payment\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Session;
use Amazon\Login\Api\CustomerLinkRepositoryInterface;
use Amazon\Core\Helper\Data;

/**
 * @api
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class ResetPassword extends Template
{

    private $urlModel;

    private $session;

    private $customerLink;

    private $coreHelper;

    public function __construct(
        Context $context,
        Url $urlModel,
        Session $session,
        CustomerLinkRepositoryInterface $customerLink,
        Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlModel = $urlModel;
        $this->session = $session;
        $this->customerLink = $customerLink;
        $this->coreHelper = $coreHelper;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('widget/resetpassword.phtml');
        }
        return $this;
    }

    public function displayAmazonInfo()
    {
        $id = $this->session->getCustomer()->getId();

        $amazon = $this->customerLink->get($id);

        if ($amazon && $amazon->getAmazonId()) {
            return true;
        }

        return false;
    }

    public function getLink()
    {
        $url = $this->urlModel->getLogoutUrl();

        return $url;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->coreHelper->isLoginButtonEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
