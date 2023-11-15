<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config\Fieldset;

use Amazon\Pay\Model\Client;
use Amazon\Pay\Model\Serializer;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Module\Dir\Reader;

class Payment extends \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Payment
{

    const MODULE_CODE = 'Amazon_Pay';
    const MODULE_PACKAGE_NAME = 'amzn/amazon-pay-magento-2-module';
    const MODULE_COMPOSER_FILE = '/composer.json';
    const MODULE_CHANGELOG_URL = 'https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/CHANGELOG.md';
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $backendConfig,
        Reader $moduleReader,
        File $filesystem,
        Serializer $serializer,
        Client $client,
        SecureHtmlRenderer $secureHtmlRenderer,
        array $data = [],

    ) {
        parent::__construct($context, $authSession, $jsHelper, $backendConfig, $data, $secureHtmlRenderer);

        $this->secureRenderer = $secureHtmlRenderer;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
        $this->client = $client;
    }

    /**
     * Read version about extension from composer json file
     *
     * @param string $moduleCode
     *
     * @return mixed
     */
    public function getLocalModuleVersion(): mixed
    {
        try {
            $dir = $this->moduleReader->getModuleDir('', self::MODULE_CODE);
            $file = $dir . self::MODULE_COMPOSER_FILE;

            $jsonFileContent = $this->filesystem->fileGetContents($file);
            $fileData = $this->serializer->unserialize($jsonFileContent);
            $version = $fileData['version'];

        } catch (FileSystemException $e) {
            $version = false;
        }

        return $version;
    }

    /**
     * @return mixed|string
     */
    public function getRemoteModuleVersion(): mixed
    {
        $lastVersion = '';

        try {
            $availableVersionsData = json_decode($this->client->getJsonData(), true);
            $lastVersionData = $availableVersionsData['packages'][self::MODULE_PACKAGE_NAME][0];
            $lastVersion = $lastVersionData['version'];
        } catch (\Exception $e) {
            //nothing
        }

        return $lastVersion;
    }

    /**
     * @return bool
     */
    public function hasUpdates() :bool
    {
        return version_compare(
            $this->getLocalModuleVersion(),
            $this->getRemoteModuleVersion(),
            '<'
        );
    }

    /**
     * @return string
     */
    protected function renderUpdateNotification(): string
    {
        $html ='<br />';
        $html .= '<div class="amazon-module-version">' .
            '<div><span class="upgrade-error message message-warning">' .
            'Module version update is detected ' .
            '<b>(Current version: <span style="color:red">' . $this->getLocalModuleVersion() . '</span></b> ' .
            ' - ' .
            '<b>Latest Version: <span style="color:darkgreen">' . $this->getRemoteModuleVersion() . ')</span></b>. ' .
            'See the <a target="_blank" href="' . self::MODULE_CHANGELOG_URL . '">ChangeLog</a>.' .
            '</span></div></div>';
        $html .='<br /><br />';

        return $html;
    }

    protected function _getHeaderTitleHtml($element): string
    {
        $html = '<div class="config-heading" >';

        if ($this->hasUpdates()) {
            $html .= $this->renderUpdateNotification();
        }

        $groupConfig = $element->getGroup();

        $disabledAttributeString = $this->_isPaymentEnabled($element) ? '' : ' disabled="disabled"';
        $disabledClassString = $this->_isPaymentEnabled($element) ? '' : ' disabled';
        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"' .
            $disabledAttributeString .
            ' class="button action-configure' .
            (empty($groupConfig['paypal_ec_separate']) ? '' : ' paypal-ec-separate') .
            $disabledClassString .
            '" id="' . $htmlId . '-head" >' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "paypalToggleSolution.call(this, '" . $htmlId . "', '" . $this->getUrl('adminhtml/*/state') .
            "');event.preventDefault();",
            'button#' . $htmlId . '-head'
        );

        if (!empty($groupConfig['more_url'])) {
            $html .= '<a class="link-more" href="' . $groupConfig['more_url'] . '" target="_blank">' . __(
                    'Learn More'
                ) . '</a>';
        }
        if (!empty($groupConfig['demo_url'])) {
            $html .= '<a class="link-demo" href="' . $groupConfig['demo_url'] . '" target="_blank">' . __(
                    'View Demo'
                ) . '</a>';
        }

        $html .= '</div>';
        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }

        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
    }
}
