<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote implements EasyCredit\QuoteInterface {

    protected $basket = null;

    public function __construct($paymentController) {
        $this->_paymentController = $paymentController;

        $this->_basket = $paymentController->get('modules')->Basket()->sGetBasket();


        $this->_user = $paymentController->getUser();
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
        $dob = $this->_user['billingaddress']['birthday'];

        if ($dob == '0000-00-00') {
            $dob = null;
        }

        return $dob;
    }

    public function isRiskProductInCart() {
        return false;
    }

    public function getCustomer() {
        return new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Customer();
    }

    public function getAllVisibleItems() {
        $basketContent = $this->_basket['content'];

        $items = [];
        foreach($basketContent as $rawItem) {
            $item = new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Item($rawItem);
            array_push($items, $item);
        }

        return $items;
    }
}
