<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class SystemBuilder {

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
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit()->getVersion();
    }

    public function build () {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem([
            'shopSystemManufacturer' => implode(' ',[$this->getSystemVendor(),$this->getSystemVersion()]),
            'shopSystemModuleVersion' => $this->getModuleVersion()
        ]);
    }
}
