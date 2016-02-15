<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote implements EasyCredit\QuoteInterface {

    protected $basket = null;

    public function __construct($paymentController) {
        $this->_paymentController = $paymentController;

        $this->_basket = $paymentController->get('modules')->Basket()->sGetBasket();
        $this->_user = $paymentController->getUser();
    }

    public function isLoggedIn() {
        return $this->_paymentController->isUserLoggedIn();
    }

    public function getGrandTotal() {
        return $this->_paymentController->getAmount();
    }

    public function getBillingAddress() {
        return new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Address(
            $this->_user,
            $this->_user['billingaddress']
        );
    }
    public function getShippingAddress() {
        return new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Address(
            $this->_user,
            $this->_user['shippingaddress']
        );
    }

    public function getCustomerPrefix() {
        return $this->_user['billingaddress']['salutation'];
    }
    public function getCustomerFirstname() {
        return $this->_user['billingaddress']['firstname'];
    }

    public function getCustomerLastname() {
        return $this->_user['billingaddress']['lastname'];
    }
    public function getCustomerEmail() {
        return $this->_user['additional']['user']['email'];
    }
    public function getCustomerDob() {
        return '';
    }

    public function getRiskProducts() {
        return array();
    }

    public function getAllVisibleItems() {

    }
}
