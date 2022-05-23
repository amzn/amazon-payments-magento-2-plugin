<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;

class AddToCart extends Helper
{
    public function clickAddToCart($addToCartSelector)
    {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        try {
            $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use ($addToCartSelector) {
                $addToCart = WebDriverBy::cssSelector($addToCartSelector);
                $remoteWebDriver->wait(30, 100)->until(WebDriverExpectedCondition::elementToBeClickable($addToCart));
            });
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
