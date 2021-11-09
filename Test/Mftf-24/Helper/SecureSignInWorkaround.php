<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;

class SecureSignInWorkaround extends Helper
{
    public function continueAsUser($openerName, $editAddress)
    {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        try {
            $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use ($openerName, $editAddress, $magentoWebDriver) {
                // Do nothing here unless the new 'Continue as...' button is present
                $continueAs = $remoteWebDriver->findElements(WebDriverBy::cssSelector('#maxo_buy_now input[type=submit]'));

                if (!empty($continueAs)) {
                    // Click Continue as... button and return to checkout
                    $continueAs[0]->click();
                    $remoteWebDriver->switchTo()->window($openerName);
                    $magentoWebDriver->waitForPageLoad(30);
    
                    // Wait for Edit button in address details
                    $editAddressSelector = WebDriverBy::cssSelector($editAddress);
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::elementToBeClickable($editAddressSelector));
                    // Click Edit button to return to normal flow
                    $remoteWebDriver->findElement($editAddressSelector)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                    $magentoWebDriver->switchToNextTab();
                }
            });
        } catch (\Exception $e) {
            print($e->getMessage());
        }
    }
}
