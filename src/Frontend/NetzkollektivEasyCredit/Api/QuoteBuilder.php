<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

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
        return null;
    }

    public function getInvoiceAddress() {
        return $this->addressBuilder
            ->setAddress(new InvoiceAddress())
            ->build(
                $this->_user,
                $this->_user['billingaddress']
            );
    }
    public function getShippingAddress() {
        return $this->addressBuilder
            ->setAddress(new ShippingAddress())
            ->build(
                $this->_user,
                $this->_user['shippingaddress']
            );
    }

    public function getCustomer() {

        return $this->customerBuilder->build(
            $this->customer,
            $this->customer->getActiveBillingAddress()
        );
    }

    public function getItems() {
        return $this->_getItems(
            $this->_basket['content']
        );
    }

    protected function _getItems(array $items): array
    {
        $_items = [];
        foreach ($items as $item) {
            $quoteItem = $this->itemBuilder->build($item, $this->context);
            if ($quoteItem->getPrice() <= 0) {
                continue;
            }
            $_items[] = $quoteItem;
        }

        return $_items;
    }

    public function getSystem() {
        return $this->systemBuilder->build();
    }

    public function build($cart, SalesChannelContext $context): TransactionInitRequestWrapper {
        $this->cart = $cart;
        $this->context = $context;
        $this->customer = $context->getCustomer();

        if ($cart instanceof Cart && $cart->getDeliveries()->getAddresses()->first() === null) {
            throw new QuoteInvalidException();
        }
        if (!$this->customer) {
            throw new QuoteInvalidException();
        }

        $transactionInitRequest = new TransactionInitRequest([
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
            'customer' => $this->getCustomer(),
            'customerRelationship' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship([
                'customerSince' => ($this->customer->getCreatedAt() instanceof \DateTimeImmutable) ? $this->customer->getCreatedAt()->format('Y-m-d') : null,
                'orderDoneWithLogin' => !$this->customer->getGuest(),
                'numberOfOrders' => $this->customer->getOrderCount(),
                'logisticsServiceProvider' => $this->getShippingMethod()      
            ]),
            'redirectLinks' => $this->getRedirectLinks()
        ]);

        return new \Teambank\RatenkaufByEasyCreditApiV3\Integration\TransactionInitRequestWrapper([
            'transactionInitRequest' => $transactionInitRequest,
            'company' => $this->customer->getActiveBillingAddress() ? $this->customer->getActiveBillingAddress()->getCompany() : null
        ]);
    }

}
