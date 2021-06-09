<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

class WaitForPopup extends Helper {

    public function waitForPopup() {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        try {
            $webDriver->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
                // Wait for up to 30 seconds, poll every 100ms
                $webdriver->wait(30, 100)->until(\Facebook\WebDriver\WebDriverExpectedCondition::numberOfWindowsToBe(2));
            });
        } catch(\Exception $e) {
            print($e);
        }
        
    }

}
