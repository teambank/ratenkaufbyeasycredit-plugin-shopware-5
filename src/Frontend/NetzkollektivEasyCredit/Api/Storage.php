<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Storage implements \Netzkollektiv\EasyCreditApi\StorageInterface {

    public function set($key,$value) {
        if (!isset(Shopware()->Session()->EasyCredit) || !is_array(Shopware()->Session()->EasyCredit)) {
            Shopware()->Session()->offsetSet('EasyCredit',array());
        }
        Shopware()->Session()->offsetSet('EasyCredit',array_merge(Shopware()->Session()->offsetGet('EasyCredit'),array(
            $key => $value
        )));
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
