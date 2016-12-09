<?php
use Doctrine\Common\Collections\ArrayCollection;
use Netzkollektiv\EasyCredit;
use Shopware\Plugins\NetzkollektivEasycredit\Subscriber;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap
    extends Shopware_Components_Plugin_Bootstrap
{

    const PAYMENT_NAME = 'easycredit';
    const INTEREST_ORDERNUM = 'sw-payment-ec-interest';
    const DEBUG = true;

    public function getLabel()
    {
        return 'Ratenkauf by easyCredit';
    }

    public function getVersion()
    {
        return '1.2.4';
    }

    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'supplier' => 'Teambank AG',
            'author' => 'Teambank AG',
            'description' => 'Dieses Plugin ermöglicht die Zahlung mittels '.$this->getLabel(),
            'support' => 'service@easycredit.de',
            'link' => 'https://www.easycredit.de/Ratenkauf.htm',
        );
    }

    public function getCapabilties()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true,
            'secureUninstall' => true
        );
    }

    public function install()
    {

        $this->_createEvents();
        $this->_createPaymentConfigForm();
        $this->_createPayment();
        $this->_createAdditionalOrderAttributes();

        return true;
    }

    public function afterInit()
    {
        $loader = new \Doctrine\Common\ClassLoader('Netzkollektiv\EasyCredit', __DIR__ . '/src');
        $loader->register();

        $this->get('Loader')->registerNamespace(
            'Shopware\Plugins\NetzkollektivEasycredit',
            $this->Path()
        );
    }

    protected function _createAdditionalOrderAttributes() {
        Shopware()->Models()->addAttribute('s_order_attributes','easycredit','transaction_uuid','varchar(255)');
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels(
            array('s_order_attributes')
        );
    }

    /**
     * Activate the plugin easycredit plugin.
     * Sets the active flag in the payment row.
     *
     * @return bool
     */
    public function enable()
    {
        $payment = $this->getPayment();
        if ($payment !== null) {
            $payment->setActive(true);
            $this->get('models')->flush($payment);
        }

        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'proxy', 'frontend')
        );
    }

    public function disable()
    {
        $payment = $this->getPayment();
        if ($payment !== null) {
            $payment->setActive(false);
            $this->get('models')->flush($payment);
        }

        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend')
        );
    }

    protected function _createEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onDispatchLoopStartup'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_EasyCreditCheckout',
            'onInitResourceCheckout'
        );
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Javascript',
            'onCollectJavascriptFiles'
        );
    }

    public function onCollectJavascriptFiles() {
        $jsDir = __DIR__ . '/Views/frontend/_public/src/js/';

        return new ArrayCollection(array(
            $jsDir . 'pp-plugin.js'
        ));
    }

    public function onDispatchLoopStartup(\Enlight_Event_EventArgs $args) {
        $this->get('events')->addSubscriber(new Subscriber\Frontend($this));
        $this->get('events')->addSubscriber(new Subscriber\Backend($this));
    }

    public function onInitResourceCheckout()
    {
        $logger = new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Logger();

        $apiClient = new EasyCredit\Api(array(
            'api_key' => $this->Config()->get('easycreditApiKey'),
            'api_token' => $this->Config()->get('easycreditApiToken'),
            'debug_logging' => $this->Config()->get('easycreditDebugLogging')
        ), $logger);

        return new EasyCredit\Checkout(
            $apiClient,
            new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Storage()
        );
    }

    public function getPayment()
    {
        return $this->Payments()->findOneBy(
            array('name' => 'easycredit')
        );
    }

    protected function _createPayment()
    {
        $this->createPayment(
            array(
                'name' => self::PAYMENT_NAME,
                'description' => $this->getLabel(),
                'action' => 'payment_easycredit',
                'active' => 0,
                'position' => 0,
                'additionalDescription' => $this->_getPaymentLogo(). ' <a href="https://www.easycredit.de/Ratenkauf.htm" onclick="javascript:window.open(\'https://www.easycredit.de/Ratenkauf.htm\',\'whatiseasycredit\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=0, top=0, width=800, height=600\'); return false;" class="easycredit-tooltip" style="color:#000000;">Was ist '.$this->getLabel().'?</a>',
                'template' => 'easycredit.tpl',
                'class' => 'easycredit',
            )
        );
    }

    protected function _getPaymentLogo($enabled = true)
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        $logo = array('<img id="rk_easycredit_logo"');
        if (!$enabled) {
            $logo[] = 'style="filter: grayscale(100%); -webkit-filter: grayscale(100%)"';
        }
        $logo[] = 'src="https://static.easycredit.de/content/image/logo/ratenkauf_42_55.png"';
        $logo[] = 'alt="easycredit Logo" style="display:inline;">';

        return implode(' ',$logo);
    }

    /**
     * @return array
     */
    private function getOrderStates() {
        /**@var $repository \Shopware\Models\Order\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $filters = array(array('property' => 'status.id', 'expression' => '!=', 'value' => '-1'));
        $orderStatusRaw = $repository->getOrderStatusQuery($filters)->getArrayResult();
        $orderStates = array(
            array(-1, '(Shopware-Standard)') // default: do nothing
        );
        foreach ($orderStatusRaw as $o) {
            $orderStates[] = array($o['id'], $o['description']);
        }
        return  $orderStates;
    }

    protected function _createPaymentConfigForm()
    {
        $form = $this->Form();

        // Frontend settings

        $form->setElement(
            'boolean',
            'easycreditModelWidget',
            array(
                'label' => 'Zeige Modellrechner-Widget neben Produktpreis',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $form->setElement(
            'select',
            'easycreditOrderStatus',
            array(
                'label' => 'Bestellungsstatus',
                'value' => -1, // do nothing
                'store' => $this->getOrderStates(),
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $form->setElement(
            'boolean',
            'easycreditDebugLogging',
            array(
                'label' => 'API Debug Logging',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $form->setElement(
            'text',
            'easycreditApiKey',
            array(
                'label' => 'API-Key',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' '
            )
        );

        $form->setElement(
            'text',
            'easycreditApiToken',
            array(
                'label' => 'API-Token',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' '
            )
        );

        if (is_file(__DIR__ . '/Views/backend/plugins/easycredit/test.js')) {
            $form->setElement(
                'button',
                'easycreditButtonClientTest',
                array(
                    'label' => '<strong>Jetzt API-Zugangsdaten testen<strong>',
                    'handler' => "function(btn) {"
                        . file_get_contents(__DIR__ . '/Views/backend/plugins/easycredit/test.js') . "}"
                )
            );
        }
    }

    protected function _getAmount(){
        $quote = new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote();
        return $quote->getGrandTotal();
    }

    public function isInterestAmountValid() {

        $amountInterest = $this->getInterestAmount();
        if (false == $amountInterest) {
            return false;
        }

        $amountBasket = round($this->_getAmount(), 2);
        $amountInterest = round($amountInterest, 2);
        $amountBasketWithoutInterest = round($amountBasket - $amountInterest, 2);

        $amountAuthorized = round(Shopware()->Session()->EasyCredit["authorized_amount"], 2);
        $this->_debug('basket:'.$amountBasket.', interest: '.$amountInterest);
        $this->_debug($amountAuthorized." == ".$amountBasketWithoutInterest);
        return $amountAuthorized == $amountBasketWithoutInterest; 
    }

    public function getInterestAmount()
    {
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $content = $basket['content'];
        foreach ($content as $c) {
            if (isset($c['ordernumber']) && $c['ordernumber'] == self::INTEREST_ORDERNUM) {
                return $c['netprice'];
            }
        }
        return false;
    }

    public function isInterestInBasket() {
        return $this->getInterestAmount() !== false;
    }

    public function isValid() {
        return $this->isInterestInBasket() 
            && $this->isInterestAmountValid() 
            && $this->isAddressValid();
    }

    public function clear() {
        $this->removeInterest();
        $this->unsetStorage();
    }

    public function unsetStorage() {
        if (count(Shopware()->Session()->EasyCredit) > 0) {
            foreach (Shopware()->Session()->EasyCredit as $key => $value) {
                unset(Shopware()->Session()->EasyCredit[$key]);
            }
        }
    }

    protected function _getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    public function isResponsive() {
        return Shopware()->Shop()->getTemplate()->getVersion() >= 3;
    }

    public function isSelected($paymentId = null) {
        if ($paymentId == null) {
            $user = $this->_getUser();
            $paymentId = $user['additional']['payment']['id'];
        }
        return $paymentId == $this->getPayment()->getId();
    }

    public function addInterest() {
        if (!isset(Shopware()->Session()->EasyCredit["interest_amount"])
            || empty(Shopware()->Session()->EasyCredit["interest_amount"])
           ) {
            return;
        }

        $this->removeInterest();

        $interest_amount = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);

        $this->get('db')->insert(
            's_order_basket',
            array(
                'sessionID' => Shopware()->Session()->offsetGet('sessionId'),
                'articlename' => 'Zinsen für Ratenzahlung',
                'articleID' => 0,
                'ordernumber' => self::INTEREST_ORDERNUM,
                'quantity' => 1,
                'price' => $interest_amount,
                'netprice' => $interest_amount,
                'tax_rate' => 0,
                'datum' => new Zend_Date(),
                'modus' => 4,
                'currencyFactor' => 1
            )
        );
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }

    /**
     * remove interest from basket via DB call
     */
    public function removeInterest()
    {
        $this->_debug('removing interest');
        $session = Shopware()->Session();

        Shopware()->Db()->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $session->get('sessionId'),
                'ordernumber = ?' => self::INTEREST_ORDERNUM
            )
        );
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }

    public function isAddressValid() {
        $authorizedAddress = Shopware()->Session()->EasyCredit['authorized_address'];

        $this->_debug($authorizedAddress);
        $this->_debug($this->_getAddress());
        $this->_debug($this->_array_diff_recursive(
            $authorizedAddress,
            $this->_getAddress()
        ));

        return (
            count($this->_array_diff_recursive(
                $authorizedAddress,
                $this->_getAddress()
            )) == 0 && count($this->_array_diff_recursive(
                $authorizedAddress,
                $this->_getAddress('shipping')
            )) == 0
        );
    }

    protected function _array_diff_recursive($arr1, $arr2)
    {
        $outputDiff = [];

        foreach ($arr1 as $key => $value)
        {
            if (array_key_exists($key, $arr2))
            {
                if (is_array($value))
                {
                    $recursiveDiff = $this->_array_diff_recursive($value, $arr2[$key]);

                    if (count($recursiveDiff))
                    {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                }
                else if (!in_array($value, $arr2))
                {
                    $outputDiff[$key] = $value;
                }
            }
            else if (!in_array($value, $arr2))
            {
                $outputDiff[$key] = $value;
            }
        }
        return $outputDiff;
    }

    public function saveAddress() {
        Shopware()->Session()->EasyCredit['authorized_address'] = $this->_getAddress();
    }

    protected function _getAddress($type = 'billing') {
        $quote = new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote();
        $address = ($type == 'billing') ? $quote->getBillingAddress() : $quote->getShippingAddress();

        return array(
            $address->getStreet(),
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryId()
        );
    }

    protected function _debug($msg) {
        if (self::DEBUG) {

            if (is_array($msg)) {
                $msg = print_r($msg,true);
            }
            file_put_contents('/shopware/debug.log',$msg.PHP_EOL,FILE_APPEND);
        }
    }
}
