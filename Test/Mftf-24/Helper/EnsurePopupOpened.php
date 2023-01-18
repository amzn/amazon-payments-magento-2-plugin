<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;

class EnsurePopupOpened extends Helper
{
    public function ensurePopupOpened($buttonSelector)
    {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $log = [];

        try {
            $magentoWebDriver->executeInSelenium(function (RemoteWebDriver $remoteWebDriver) use (
                $magentoWebDriver,
                $buttonSelector,
                $log
            ) {
                try {
                    $remoteWebDriver->wait(15, 100)->until(
                        WebDriverExpectedCondition::numberOfWindowsToBe(2)
                    );
                } catch (TimeoutException $e) {
                    $log[] = 'Timed out waiting for second window:\n' . $e->getMessage();
                    $log[] = 'Attempting to click button again';
                    $magentoWebDriver->click($buttonSelector);
                    $magentoWebDriver->wait(1);
                } catch (\Exception $e) {
                    $log[] = 'General exception thrown while waiting for second window:\n' . $e->getMessage();
                }
            });
        } catch (\Exception $e) {
            $log[] = 'General exception thrown while executing remote web driver code:\n' . $e->getMessage();
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        } finally {
            var_dump($log);
        }
    }
}
