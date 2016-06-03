<?php
namespace Netzkollektiv\EasyCredit;

interface AddressInterface {

    public function getStreet();
    public function getPostcode();
    public function getCity();
    public function getCountryId();

}
