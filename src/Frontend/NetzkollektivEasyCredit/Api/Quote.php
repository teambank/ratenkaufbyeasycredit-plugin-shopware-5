<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class FakeCheckoutController extends \Shopware_Controllers_Frontend_Checkout {
    public function __construct() {
        $this->admin = Shopware()->Modules()->Admin();
        $this->basket = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
        $this->container = Shopware()->Container();
        $this->setView(new \Enlight_View_Default(
            Shopware()->Container()->get('Template')
        ));
    }
    
    public function getSelectedPayment() {
        return 'easycredit';
    }
}

class Quote implements \Netzkollektiv\EasyCreditApi\Rest\QuoteInterface {

    protected $basket = null;

    public function __construct() {
        $this->_basket = Shopware()->Modules()->Basket()->sGetBasket(); 
        $this->_user = $this->_getUser();
        $this->_config = Shopware()->Config();
    }

    protected function _getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    public function getId() {
        return Shopware()->Session()->get('sessionId');
    }

    public function getShippingMethod() {
        $controller = new FakeCheckoutController();
        $dispatch = $controller->getSelectedDispatch();
        if (isset($dispatch['name'])) {
            return strip_tags($dispatch['name']);
        }
    }

    public function getIsClickAndCollect() {
        $controller = new FakeCheckoutController();
        $dispatch = $controller->getSelectedDispatch();
        return (
            isset($dispatch['id'])
            && $dispatch['id'] == $this->_config->get('easycreditClickAndCollectShippingMethod')
        );
    }

    public function getGrandTotal() {
        $controller = new FakeCheckoutController();
        $basket = $controller->getBasket();
        return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
    }

    public function getDuration() {
        return null;
    }

    public function getBillingAddress() {
        return new Quote\Address(
            $this->_user,
            $this->_user['billingaddress']
        );
    }
    public function getShippingAddress() {
        return new Quote\ShippingAddress(
            $this->_user,
            $this->_user['shippingaddress']
        );
    }

    public function getCustomer() {
        return new Quote\Customer();
    }

    public function getItems() {
        return $this->_getItems(
            $this->_basket['content']
        );
    }

    protected function _getItems($items) {
        $_items = array();
        foreach ($items as $item) {
            $quoteItem = new Quote\Item(
                $item
            );
            if ($quoteItem->getPrice() == 0) {
                continue;
            }
            $_items[] = $quoteItem;
        }
        return $_items;
    }

    public function getSystem() {
        return new System();
    }
}
