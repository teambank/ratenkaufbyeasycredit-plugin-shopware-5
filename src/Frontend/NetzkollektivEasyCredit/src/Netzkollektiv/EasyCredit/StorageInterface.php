<?php
namespace Netzkollektiv\EasyCredit;

interface StorageInterface {

    public function setData($key, $value);
    public function getData($key);
    public function clear();
}
