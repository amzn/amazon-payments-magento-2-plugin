<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;

class SecureSignInWorkaround extends Helper
{
    public function continueAsUser(
        $openerName,
        $editAddressSelector,
        $addressIdSelector,
        $changePaymentSelector,
        $usePaymentSelector
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        try {
            $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
                $openerName,
                $editAddressSelector,
                $addressIdSelector,
                $changePaymentSelector,
                $usePaymentSelector,
                $magentoWebDriver
            ) {
                // Do nothing here unless the new 'Continue as...' button is present
                $continueAs = $remoteWebDriver->findElements(
                    WebDriverBy::cssSelector('#maxo_buy_now input[type=submit]')
                );

                if (!empty($continueAs)) {
                    // Click Continue as... button and return to checkout
                    $continueAs[0]->click();
                    $remoteWebDriver->switchTo()->window($openerName);
                    $magentoWebDriver->waitForPageLoad(30);
                    $magentoWebDriver->waitForLoadingMaskToDisappear(30);

                    // Wait for Edit button in address details
                    $editAddress = WebDriverBy::cssSelector($editAddressSelector);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::elementToBeClickable($editAddress)
                    );
                    // Click Edit button to return to normal flow
                    $remoteWebDriver->findElement($editAddress)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                    $magentoWebDriver->switchToNextTab();

                    $addressId = WebDriverBy::cssSelector($addressIdSelector);
                    $changePayment = WebDriverBy::cssSelector($changePaymentSelector);
                    $usePayment = WebDriverBy::cssSelector($usePaymentSelector);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($addressId)
                    );
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::elementToBeClickable($changePayment)
                    );
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($usePayment)
                    );
                }
            });
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }
}
