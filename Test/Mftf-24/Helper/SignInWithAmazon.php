<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;

class SignInWithAmazon extends Helper
{
    public function clickSignInWithAmazon($siwaLocator)
    {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $waitTime = 15000;

        try {
            $webDriver->waitForJS("
                try {
                    return !!document.querySelector('${siwaLocator}')
                        .shadowRoot
                        .querySelector('div > div.amazonpay-button-view1.amazonpay-button-view1-gold');
                } catch (err) {
                    return false;
                }", $waitTime);
            $webDriver->click($siwaLocator);
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
