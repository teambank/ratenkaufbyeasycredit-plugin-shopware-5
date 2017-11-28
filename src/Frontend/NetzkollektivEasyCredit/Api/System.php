<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class System implements \Netzkollektiv\EasyCreditApi\SystemInterface {

    public function getSystemVendor() {
        return 'Shopware';
    }

    public function getSystemVersion() {
        return \Shopware::VERSION;
    }

    public function getModuleVersion() {
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit()->getVersion();
    }

    public function getIntegration() {
        return 'PAYMENT_PAGE';
    }
}
