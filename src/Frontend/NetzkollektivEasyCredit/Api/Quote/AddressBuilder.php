<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class AddressBuilder {
    protected $_address = array();
    protected $_user = array();

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function getFirstname() {
        return $this->_address['firstname'];
    }

    public function getLastname() {
        return $this->_address['lastname'];
    }

    public function getStreet() {
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

    public function build($user, $address) {
        $this->_user = $user;
        $this->_address = $address;

        $this->address['firstName'] = $this->getFirstName();
        $this->address['lastName'] = $this->getLastName();
        $this->address['address'] = $this->getStreet();
        $this->address['additionalAddressInformation'] = $this->getStreetAdditional();
        $this->address['zip'] = $this->getPostcode();
        $this->address['city'] = $this->getCity();
        $this->address['country'] = $this->getCountryId();

        return $this->address;
    }
}
