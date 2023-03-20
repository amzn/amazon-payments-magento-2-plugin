<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

class HandlePopupSecondScreen extends Helper
{
    public function handleClickOnly($signInButton) 
    {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $signInButton
        ) {
            try {
                $remoteWebDriver->findElement(
                    WebDriverBy::cssSelector($signInButton)
                )->click();
                $magentoWebDriver->switchToNextTab();
                $magentoWebDriver->wait(5);
            } catch (\Exception $ex) {

            } catch (\Error $err) {

            }
        });
    }

    public function navigateToAddressAndPaymentOptions(
        $openerName,
        $continueButton,
        $editShippingButton,
        $addressId,
        $checkoutButton,
        $apButton
    ) {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $stepLog = [];

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $stepLog,
            $openerName,
            $continueButton,
            $editShippingButton,
            $addressId,
            $checkoutButton,
            $apButton
        ) {
            try {
                if (count($remoteWebDriver->getWindowHandles()) > 1) {
                    if ($magentoWebDriver->executeJS('return window.name;') === $openerName) {
                        $stepLog[] = 'Popup remained open, switching back to it';
                        $magentoWebDriver->switchToNextTab();
                    }
    
                    $continueAs = $remoteWebDriver->findElements(WebDriverBy::cssSelector($continueButton));
                    $checkout = $remoteWebDriver->findElements(WebDriverBy::cssSelector($checkoutButton));

                    if (empty($continueAs) && empty($checkout)) {
                        $stepLog[] = 'Popup didn\'t finish loading, closing popup and re-initiating Amazon Pay';

                        $stepLog[] = 'Closing tab';
                        $magentoWebDriver->closeTab();
                        $stepLog[] = 'Switching back to opener';
                        $magentoWebDriver->switchToWindow($openerName);
                        $stepLog[] = 'Clicking Amazon button on checkout';
                        $magentoWebDriver->click($apButton);
                        $stepLog[] = 'Waiting for popup to load and switching back to it';
                        $magentoWebDriver->wait(3);
                        $magentoWebDriver->switchToNextTab();
                        
                        $continueAs = $remoteWebDriver->findElements(WebDriverBy::cssSelector($continueButton));
                    }

                    if (!empty($continueAs)) {
                        $stepLog[] = 'Click Continue as... button and return to checkout';
                        $continueAs[0]->click();
                        $remoteWebDriver->switchTo()->window($openerName);
                        $magentoWebDriver->waitForPageLoad(30);

                        $stepLog[] = 'Wait for Edit button in address details';
                        $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                        $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::elementToBeClickable($editAddressSelector));
                        $stepLog[] = 'Click Edit button to return to normal flow';
                        $remoteWebDriver->findElement($editAddressSelector)->click();
                        
                        $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                        $magentoWebDriver->switchToNextTab();
                        $addressIdSelector = WebDriverBy::cssSelector($addressId);
                        $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector));
                    } else {
                        $stepLog[] = 'No continue button, standard maxo/pay now';
                        $addressIdSelector = WebDriverBy::cssSelector($addressId);
                        $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector));
                    }
                } else {
                    $stepLog[] = 'Popup closed, allowing checkout page to load';
                    $magentoWebDriver->waitForLoadingMaskToDisappear(30);
                    $stepLog[] = 'Wait for Edit button in address details';
                    $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::elementToBeClickable($editAddressSelector));
                    $stepLog[] = 'Click Edit button to return to normal flow';
                    $remoteWebDriver->findElement($editAddressSelector)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                    $magentoWebDriver->switchToNextTab();
                    $addressIdSelector = WebDriverBy::cssSelector($addressId);
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector));
                }
            } catch (\Exception $ex) {
                $stepLog[] = $ex->getMessage();
            } finally {
                var_dump($stepLog);
            }
        });
    }

    public function handleSignInConsent($consentButton) 
    {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $stepLog = [];

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $stepLog,
            $consentButton
        ) {
            try {
                if (count($remoteWebDriver->getWindowHandles()) > 1) {
                    $stepLog[] = 'Popup remained open, switching back to it';
                    $magentoWebDriver->switchToNextTab();

                    $loginConsent = $remoteWebDriver->findElements(WebDriverBy::cssSelector($consentButton));

                    if (!empty($loginConsent)) {
                        $stepLog[] = 'Click Continue button';
                        $loginConsent[0]->click();
                        $magentoWebDriver->switchToNextTab();
                    }
                } else {
                    $stepLog[] = 'Popup closed, following redirect to account screen';
                }
            } catch (\Exception $ex) {
                $stepLog[] = $ex->getMessage();
            } finally {
                var_dump($stepLog);
            }
        });
    }

    public function handleCancelSignIn(
        $loginCancelButton,
        $openerName
    ) {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $stepLog = [];

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $stepLog,
            $loginCancelButton,
            $openerName
        ) {
            try {
                if (count($remoteWebDriver->getWindowHandles()) > 1) {
                    $stepLog[] = 'Popup remained open, switching back to it';
                    $magentoWebDriver->switchToNextTab();
    
                    $loginCancel = $remoteWebDriver->findElements(WebDriverBy::cssSelector($loginCancelButton));
    
                    if (!empty($loginCancel)) {
                        $stepLog[] = 'Cancel login with Amazon and land back on sign-in Magento page';
                        $loginCancel[0]->click();
                        $remoteWebDriver->switchTo()->window($openerName);
                    }
                } else {
                    $stepLog[] = 'Popup closed, signing out manually and returning to login page';
                    $magentoWebDriver->amOnPage('customer/account/logout/');
                    $magentoWebDriver->wait(5);
                    $magentoWebDriver->amOnPage('/customer/account/login/');
                }
            } catch (\Exception $ex) {
                $stepLog[] = $ex->getMessage();
            } finally {
                var_dump($stepLog);
            }
        });
    }
}
