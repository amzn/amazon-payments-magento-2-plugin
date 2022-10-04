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
        $defaultAddressSelector,
        $openerName,
        $editShippingButton
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $waitTime = 15;

        try {
            $magentoWebDriver->waitForElementClickable($changeAddressSelector, $waitTime);
            $magentoWebDriver->click($changeAddressSelector);
            $magentoWebDriver->waitForElementClickable($addressBackButtonSelector, $waitTime);
            $magentoWebDriver->click($addressBackButtonSelector);

            $magentoWebDriver->waitForElement($defaultAddressSelector, $waitTime);
        } catch (TimeoutException $e) {
            $magentoWebDriver->switchToNextTab();

            try {
                $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
                    $magentoWebDriver,
                    $editShippingButton
                ) {
                    $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::elementToBeClickable($editAddressSelector));
                    $magentoWebDriver->debug('Click Edit button to return to normal flow');
                    $remoteWebDriver->findElement($editAddressSelector)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                    $magentoWebDriver->switchToNextTab();
                });
            } catch (\Exception $e) {

            }
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
