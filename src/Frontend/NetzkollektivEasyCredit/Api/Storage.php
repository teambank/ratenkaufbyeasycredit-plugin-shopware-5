<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Storage implements \Netzkollektiv\EasyCreditApi\StorageInterface {

    public function set($key,$value) {
        if (!isset(Shopware()->Session()->EasyCredit)) {
            Shopware()->Session()->EasyCredit = array();
        }
        Shopware()->Session()->EasyCredit[$key] = $value;
        return $this;
    }

    public function get($key) {
        return Shopware()->Session()->EasyCredit[$key];
    }

    public function clear() {
        unset(Shopware()->Session()->EasyCredit);
        return $this;
    }
}
