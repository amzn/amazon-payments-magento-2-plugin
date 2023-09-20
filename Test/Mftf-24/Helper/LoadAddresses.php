<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Exception\TimeoutException;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class LoadAddresses extends Helper
{
    public function loadAddresses(
        $changeAddressSelector,
        $addressBackButtonSelector,
        $defaultAddressSelector
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $waitTime = 15;
        $stepLog = [];

        try {
            $stepLog[] = 'Waiting for Change Address button';
            $magentoWebDriver->waitForElementClickable($changeAddressSelector, $waitTime);
            $magentoWebDriver->click($changeAddressSelector);
            $stepLog[] = 'Clicked Change Address, waiting for Back button';
            $magentoWebDriver->waitForElementClickable($addressBackButtonSelector, $waitTime);
            $magentoWebDriver->click($addressBackButtonSelector);

            $stepLog[] = 'Looking for default address';
            $magentoWebDriver->waitForElement($defaultAddressSelector, $waitTime);
            $stepLog[] = 'Found default address';
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
