<?php

namespace Amazon\Pay\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

class UseAddressOrPaymentMethod extends Helper
{
    public function clickUseAddressOrPaymentMethod(
        $addressOrPaymentMethodRadioButtonSelector,
        $useAddressOrPaymentMethodSelector
    ) {
        /** @var \Magento\FunctionalTestingFramework\Module\MagentoWebDriver $webDriver */
        $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
        $waitTime = 15000;

        try {
            $webDriver->waitForElementClickable($addressOrPaymentMethodRadioButtonSelector, $waitTime);
            $webDriver->click($addressOrPaymentMethodRadioButtonSelector);
            $webDriver->waitForElementClickable($useAddressOrPaymentMethodSelector, $waitTime);
            $webDriver->click($useAddressOrPaymentMethodSelector);
        } catch (\Exception $e) {
            // Avoid out of memory error sometimes caused by print_r
            // print_r($e);
        }
    }
}
