<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

class WaitForPopup extends Helper {

    public function waitForPopup() {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $allMods = $this->getModules();

        try {
            $webDriver->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
                // Allow popup to appear before switching tabs
                $handles = $webdriver->getWindowHandles();
                while(count($handles) < 2) {
                    $handles = $webdriver->getWindowHandles();
                }
            });
        } catch(\Exception $e) {
            print($e);
        }
        
    }

}
