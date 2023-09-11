<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class AddressBuilder {

    protected $addressModel;
    protected $_address = array();
    protected $_user = array();

    public function setAddressModel($address) {
        $this->addressModel = $address;
        return $this;
    }

    public function getFirstname() {
        return $this->_address['firstname'];
    }

    public function getLastname() {
        return $this->_address['lastname'];
    }

    public function getStreet() {
        $streetNumber = '';
        if (isset($this->_address['streetnumber'])) { // SW4
            $streetNumber = $this->_address['streetnumber']; 
        }
        if (isset($this->_address['street_number'])) {
            $streetNumber = $this->_address['street_number'];
        }
        return trim($this->_address['street'].' '.$streetNumber);
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

        $this->addressModel['firstName'] = $this->getFirstName();
        $this->addressModel['lastName'] = $this->getLastName();
        $this->addressModel['address'] = $this->getStreet();
        $this->addressModel['additionalAddressInformation'] = $this->getStreetAdditional();
        $this->addressModel['zip'] = $this->getPostcode();
        $this->addressModel['city'] = $this->getCity();
        $this->addressModel['country'] = $this->getCountryId();

        return $this->addressModel;
    }
}
