<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class ShippingAddress extends Address implements \Netzkollektiv\EasyCreditApi\Rest\ShippingAddressInterface {

    public function getFirstname() {
        return $this->_address['firstname'];
    }

    public function getLastname() {
        return $this->_address['lastname'];
    }

    public function getIsPackstation() {
        $street = $this->getStreet();
        if (is_array($street)) {
            $street = implode(' ',$street);
        }
        $street.= $this->getStreetAdditional();
        return stripos($street, 'packstation');
    }
}