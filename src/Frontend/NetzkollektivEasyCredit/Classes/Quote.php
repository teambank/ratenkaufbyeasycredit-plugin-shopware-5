<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote implements EasyCredit\QuoteInterface {

    protected $basket = null;

    public function __construct($paymentController = null) {
        $this->_paymentController = $paymentController;

        $this->_basket = Shopware()->Modules()->Basket()->sGetBasket(); 
        $this->_user = $this->_getUser();
    }

    protected static $_checkoutController = null;

    public static function setCheckoutController(Shopware_Controllers_Frontend_Checkout $controller) {
        self::$_checkoutController = $controller;
    }
    public static function getCheckoutController() {
        return self::$_checkoutController;
    }

    protected function _getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    protected function _getBasket()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        } else {
            return null;
        }
    }

    protected function _getAmountViaBasket($basket) {
        $user = $this->_getUser();
        //if (!empty($user['additional']['charge_vat'])) {
            return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
        //} else {
        //    return $basket['AmountNetNumeric'];
        //}
    }

    protected function _getAmount()
    {
        // "reliable" way to get the amount
        // amount is being calculated in here
        if (self::$_checkoutController != null) {
            $basket = self::$_checkoutController->getBasket();
Shopware()->PluginLogger()->info('amount via checkout controller');
            return $this->_getAmountViaBasket($basket);
        }

        // "documented" way to get the full amount, looks like best practice
        // but the controller is not always available & sometimes it returns values without shipping/surcharge/...
        if ($this->_paymentController instanceof Shopware_Controllers_Frontend_PaymentEasycredit && $this->_paymentController->getAmount() !== null) {
Shopware()->PluginLogger()->info('amount via payment controller');
            return $this->_paymentController->getAmount();
        }

        // "payment controller" way to get the full amount
        // sometimes it just returns null ... (e.g. guest checkout with empty session)
        $basket = $this->_getBasket();
        if ($this->_getAmountViaBasket($basket) !== null) {
Shopware()->PluginLogger()->info('amount via payment controller way');
            return $this->_getAmountViaBasket($basket);
        }

        // The "costly" way to get the full amount
        $basket = Shopware()->Modules()->Basket()->sGetBasket();
        if ($this->_getAmountViaBasket($basket) !== null) {
Shopware()->PluginLogger()->info('amount via basket');
            return $this->_getAmountViaBasket($basket);
        }
    }

    public function getGrandTotal() {
        return $this->_getAmount();
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
