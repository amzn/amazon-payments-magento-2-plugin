<?php

namespace Amazon\Pay\Model;

use Magento\Framework\HTTP\Client\Curl;

class Client extends Curl
{
    /**
     * module JSON url
     */
    const URL = 'https://repo.packagist.org/p2/amzn/amazon-pay-magento-2-module.json';

    /**
     * @param string $ip
     * @return string
     */
    public function getJsonData()
    {
        $this->get(self::URL);
        return $this->getBody();
    }
}
