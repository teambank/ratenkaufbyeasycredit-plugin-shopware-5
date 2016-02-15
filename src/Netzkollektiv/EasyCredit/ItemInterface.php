<?php
namespace Netzkollektiv\EasyCredit;

interface ItemInterface {

    public function getName();
    public function getQty();
    public function getPrice();
    public function getManufacturer();
    public function getCategoryName();
    public function getSku();
}
