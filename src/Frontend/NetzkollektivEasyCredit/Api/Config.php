<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Config extends \Netzkollektiv\EasyCreditApi\Config {

    protected $config;

    public function __construct() {
        $this->_config = Shopware()->Config();

        $this->_apiKey = $this->_config->get('easycreditApiKey');;
        $this->_apiToken = $this->_config->get('easycreditApiToken');
    }

    public function getWebshopId() {
        if (!isset($this->_apiKey) || empty($this->_apiKey)) {
            throw new \Exception('api key not configured');
        }
        return $this->_apiKey;
    }

    public function getWebshopToken() {
        if (!isset($this->_apiToken) || empty($this->_apiToken)) {
            throw new \Exception('api token not configured');
        }
        return $this->_apiToken;
    }
}
