<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class Address implements \Netzkollektiv\EasyCreditApi\Rest\AddressInterface {
    protected $_address = array();
    protected $_user = array();

    public function __construct($user, $address) {
        $this->_user = $user;
        $this->_address = $address;
    }

    public function getFirstname() {
        return $this->_address['firstname'];
    }

    public function getLastname() {
        return $this->_address['lastname'];
    }

    public function getStreet() {
        /* Shopware 4 saves street number with index 'streetnumber' */
        if (isset($this->_address['streetnumber']) && $this->_address['streetnumber'] != '') {
            $this->_address['street_number'] = $this->_address['streetnumber'];
        }
        return trim($this->_address['street'].' '.$this->_address['street_number']);
    }

    public function getStreetAdditional() {
        return trim(implode(' ',array(
            $this->_address['street2'],
            $this->_address['additional_address_line1'],
            $this->_address['additional_address_line2']
        )));
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
