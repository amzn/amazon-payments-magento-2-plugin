<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Codeception\Step\Argument\PasswordArgument;

class SignInOrContinue extends Helper
{
    public function signInOrContinue(
        $emailField,
        $passwordField,
        $signInButton,
        $openerName,
        $continueButton,
        $editShippingButton,
        $addressId
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $emailField,
            $passwordField,
            $signInButton,
            $openerName,
            $continueButton,
            $editShippingButton,
            $addressId
        ) {
            $email = $remoteWebDriver->findElements(
                WebDriverBy::cssSelector($emailField)
            );

            if (!empty($email)) {
                $magentoWebDriver->fillField($emailField, new PasswordArgument('pay-demo-eu@amazon.com'));
                $magentoWebDriver->fillField($passwordField, new PasswordArgument('demo123'));

                try {
                    $remoteWebDriver->findElement(
                        WebDriverBy::cssSelector($signInButton)
                    )->click();
                    $magentoWebDriver->switchToNextTab();
                    $magentoWebDriver->wait(3);
                } catch (\Exception $ex) {
                    $magentoWebDriver->debug('exception caught');
                } catch (\Error $err) {
                    $magentoWebDriver->debug('error caught');
                }
            } else {
                $magentoWebDriver->debug('Popup appeared as normal');
                $continueAs = $remoteWebDriver->findElements(WebDriverBy::cssSelector($continueButton));

                if (!empty($continueAs)) {
                    $magentoWebDriver->debug('Click Continue as... button and return to checkout');
                    $continueAs[0]->click();
                    $remoteWebDriver->switchTo()->window($openerName);
                    $magentoWebDriver->waitForPageLoad(30);

                    $magentoWebDriver->debug('Wait for Edit button in address details');
                    $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::elementToBeClickable($editAddressSelector)
                    );
                    $magentoWebDriver->debug('Click Edit button to return to normal flow');
                    $remoteWebDriver->findElement($editAddressSelector)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                    $magentoWebDriver->switchToNextTab();
                    $addressIdSelector = WebDriverBy::cssSelector($addressId);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector)
                    );
                } else {
                    $addressIdSelector = WebDriverBy::cssSelector($addressId);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector)
                    );
                }
            }
        });
    }

    public function handleSecondScreen(
        $openerName,
        $continueButton,
        $editShippingButton,
        $addressId
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
            $magentoWebDriver,
            $openerName,
            $continueButton,
            $editShippingButton,
            $addressId
        ) {
            try {
                if (count($remoteWebDriver->getWindowHandles()) > 1) {
                    if ($magentoWebDriver->executeJS('return window.name;') === $openerName) {
                        $magentoWebDriver->debug('Popup remained open, switching back to it');
                        $magentoWebDriver->switchToNextTab();
                    }
    
                    $continueAs = $remoteWebDriver->findElements(WebDriverBy::cssSelector($continueButton));

                    if (!empty($continueAs)) {
                        $magentoWebDriver->debug('Click Continue as... button and return to checkout');
                        $continueAs[0]->click();
                        $remoteWebDriver->switchTo()->window($openerName);
                        $magentoWebDriver->waitForPageLoad(30);

                        $magentoWebDriver->debug('Wait for Edit button in address details');
                        $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                        $remoteWebDriver->wait(30, 100)->until(
                            WebDriverExpectedCondition::elementToBeClickable($editAddressSelector)
                        );
                        $magentoWebDriver->debug('Click Edit button to return to normal flow');
                        $remoteWebDriver->findElement($editAddressSelector)->click();
                        
                        $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::numberOfWindowsToBe(2));
                        $magentoWebDriver->switchToNextTab();
                        $addressIdSelector = WebDriverBy::cssSelector($addressId);
                        $remoteWebDriver->wait(30, 100)->until(
                            WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector)
                        );
                    } else {
                        $addressIdSelector = WebDriverBy::cssSelector($addressId);
                        $remoteWebDriver->wait(30, 100)->until(
                            WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector)
                        );
                    }
                } else {
                    $magentoWebDriver->debug('Popup closed, allowing checkout page to load');
                    $magentoWebDriver->debug('Wait for Edit button in address details');
                    $editAddressSelector = WebDriverBy::cssSelector($editShippingButton);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::elementToBeClickable($editAddressSelector)
                    );
                    $magentoWebDriver->debug('Click Edit button to return to normal flow');
                    $remoteWebDriver->findElement($editAddressSelector)->click();
                    
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::numberOfWindowsToBe(2)
                    );
                    $magentoWebDriver->switchToNextTab();
                    $addressIdSelector = WebDriverBy::cssSelector($addressId);
                    $remoteWebDriver->wait(30, 100)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($addressIdSelector)
                    );
                }
            } catch (\Exception $ex) {

            }
        });
    }
}
