<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

class LoadAddresses extends Helper
{
    public function loadAddresses(
        $changeAddressSelector,
        $addressBackButtonSelector,
        $defaultAddressSelector
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $waitTime = 15000;

        try {
            $webDriver->waitForElementClickable($changeAddressSelector, $waitTime);
            $webDriver->click($changeAddressSelector);
            $webDriver->waitForElementClickable($addressBackButtonSelector, $waitTime);
            $webDriver->click($addressBackButtonSelector);

            $webDriver->waitForElement($defaultAddressSelector, $waitTime);
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
