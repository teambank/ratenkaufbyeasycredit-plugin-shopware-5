<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

use Teambank\RatenkaufByEasyCreditApiV3\Integration;
use Teambank\RatenkaufByEasyCreditApiV3\Model\Transaction;
use Teambank\RatenkaufByEasyCreditApiV3\Model\ShippingAddress;
use Teambank\RatenkaufByEasyCreditApiV3\Model\InvoiceAddress;

class FakeCheckoutController extends \Shopware_Controllers_Frontend_Checkout {

    public function __construct() {
        $this->init();
        $this->container = Shopware()->Container();
        $this->setView(new \Enlight_View_Default(
            Shopware()->Container()->get('Template')
        ));
    }
    
    public function getSelectedPayment() {
        return 'easycredit';
    }
}

class QuoteBuilder {

    protected $basket = null;

    public function __construct() {
        $this->_basket = Shopware()->Modules()->Basket()->sGetBasket(); 
        $this->_user = $this->_getUser();
        $this->_config = Shopware()->Config();
        $this->storage = new Storage();
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
            $shippingMethod = strip_tags($dispatch['name']);
            if ($this->getIsClickAndCollect()) {
                $shippingMethod = '[Selbstabholung] '.$shippingMethod;
            }
            return $shippingMethod;
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
        return $this->storage->get('duration');
    }

    public function getInvoiceAddress() {
        $addressBuilder = new Quote\AddressBuilder();
        return $addressBuilder
            ->setAddress(new InvoiceAddress())
            ->build(
                $this->_user,
                $this->_user['billingaddress']
            );
    }
    public function getShippingAddress() {
        $addressBuilder = new Quote\AddressBuilder();
        return $addressBuilder
            ->setAddress(new ShippingAddress())
            ->build(
                $this->_user,
                $this->_user['shippingaddress']
            );
    }

    public function getCustomer() {
        return new Quote\CustomerBuilder();
    }

    public function getItems() {
        return $this->_getItems(
            $this->_basket['content']
        );
    }

    protected function _getItems($items)
    {
        $_items = [];
        foreach ($items as $item) {
            $itemBuilder = new Quote\ItemBuilder($item);
            $quoteItem = $itemBuilder->build($item);
            if ($quoteItem->getPrice() <= 0) {
                continue;
            }
            $_items[] = $quoteItem;
        }

        return $_items;
    }

    public function getSystem() {
        $systemBuilder = new SystemBuilder();
        return $systemBuilder->build();
    }

    protected function _getUrl($action) {
        return Shopware()->Front()->Router()->assemble(array(
            'controller' => 'payment_easycredit',
            'action'    => $action,
        ));
    }

    public function createPaymentUniqueId()
    {
        if (class_exists('\Shopware\Components\Random')) {
            return \Shopware\Components\Random::getAlphanumericString(32);
        }
        return md5(uniqid(mt_rand(), true));
    }

    protected function getRedirectLinks() {
        if (!$this->storage->get('sec_token')) {
            $this->storage->set('sec_token', $this->createPaymentUniqueId());
        }

        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\RedirectLinks([
            'urlSuccess' => $this->_getUrl('return'),
            'urlCancellation' => $this->_getUrl('cancel'),
            'urlDenial' => $this->_getUrl('reject'),
            'urlAuthorizationCallback' =>  $this->_getUrl('authorize') . '?secToken='. $this->storage->get('sec_token')
        ]);
    }

    public function build($cart): Transaction {
        $this->cart = $cart;

        if ($cart instanceof Cart && $cart->getDeliveries()->getAddresses()->first() === null) {
            throw new QuoteInvalidException();
        }

        return new Transaction([
            'financingTerm' => $this->getDuration(),
            'orderDetails' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\OrderDetails([
                'orderValue' => $this->getGrandTotal(),
                'orderId' => $this->getId(),
                'numberOfProductsInShoppingCart' => 1,
                'invoiceAddress' => $this->getInvoiceAddress(),
                'shippingAddress' => $this->getShippingAddress(),
                'shoppingCartInformation' => $this->getItems()
            ]),
            'shopsystem' => $this->getSystem(),
            'customer' => $this->getCustomer()->build(),
            'customerRelationship' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship([
                'customerSince' => ($this->getCustomer()->getCreatedAt() instanceof \DateTime) ? $this->getCustomer()->getCreatedAt()->format('Y-m-d') : null,
                'orderDoneWithLogin' => !$this->getCustomer()->isLoggedIn(),
                'numberOfOrders' => $this->getCustomer()->getOrderCount(),
                'logisticsServiceProvider' => $this->getShippingMethod()      
            ]),
            'redirectLinks' => $this->getRedirectLinks()
        ]);
    }
}
