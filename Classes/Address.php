<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Address implements EasyCredit\AddressInterface {
    
    public function __construct($user, $address) {
        $this->_user = $user;
        $this->_address = $address;
    }

    public function getStreet($line = null) {
        $address = array_filter(array(
            trim($this->_address['street'].' '.$this->_address['street_number']),
            $this->_address['street2'],
            $this->_address['additional_address_line1'],
            $this->_address['additional_address_line2']
        ));
        return ($line == null || !isset($address[$line-1])) ? $address : $address[$line-1];
    }
    public function getPostcode() {
        return $this->_address['zipcode'];
    }
    public function getCity() {
        return $this->_address['city'];
    }
    public function getCountryId() {
        return $this->_user['additional']['countryShipping']['countryiso'];
    }
}
