<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Storage implements EasyCredit\StorageInterface {
    public function setData($key,$value) {
        if (!isset(Shopware()->Session()->EasyCredit)) {
            Shopware()->Session()->EasyCredit = array();
        }
        Shopware()->Session()->EasyCredit[$key] = $value;
    }

    public function getData($key) {
        return Shopware()->Session()->EasyCredit[$key];
    }

    public function clear() {
        unset(Shopware()->Session()->EasyCredit);
    }
}
