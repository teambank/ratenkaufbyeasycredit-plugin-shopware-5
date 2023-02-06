<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Storage implements \Teambank\RatenkaufByEasyCreditApiV3\Integration\StorageInterface
{

    public function set($key,$value) {
        if (Shopware()->Config()->get('easycreditDebugLogging')) {
            Shopware()->Container()->get('pluginlogger')->debug('Storage::set('.$key.') => '.$value);
        }
        if (!isset(Shopware()->Session()->EasyCredit) || !is_array(Shopware()->Session()->EasyCredit)) {
            Shopware()->Session()->offsetSet('EasyCredit', array());
        }
        Shopware()->Session()->offsetSet('EasyCredit', array_merge(Shopware()->Session()->offsetGet('EasyCredit'), array(
            $key => $value
        )));
        return $this;
    }

    public function get($key) {
        if (Shopware()->Config()->get('easycreditDebugLogging')) {
            Shopware()->Container()->get('pluginlogger')->debug('Storage::get('.$key.') => '.Shopware()->Session()->EasyCredit[$key]);
        }
        return Shopware()->Session()->EasyCredit[$key];
    }

    public function all()
    {
        return Shopware()->Session()->offsetGet('EasyCredit');
    }

    public function clear() {
        unset(Shopware()->Session()->EasyCredit);
        return $this;
    }
}
