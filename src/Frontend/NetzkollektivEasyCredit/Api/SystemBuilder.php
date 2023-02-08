<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class SystemBuilder {

    private $helper;

    public function __construct() {
        $this->helper = new \EasyCredit_Helper();
    }

    public function getSystemVendor() {
        return 'Shopware';
    }

    public function getSystemVersion() {
        if (defined('\Shopware::VERSION')) {
            return \Shopware::VERSION;
        }
        return Shopware()->Container()->getParameter('shopware.release.version');
    }

    public function getModuleVersion() {
        return $this->helper->getPlugin()->getVersion();
    }

    public function build () {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem([
            'shopSystemManufacturer' => implode(' ',[$this->getSystemVendor(),$this->getSystemVersion()]),
            'shopSystemModuleVersion' => $this->getModuleVersion()
        ]);
    }
}
