<?php
use Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;
use Shopware\Plugins\NetzkollektivEasyCredit\Api;
use Doctrine\Common\Collections\ArrayCollection;
use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap
    extends Shopware_Components_Plugin_Bootstrap
{

    const PAYMENT_NAME = 'easycredit';
    const INTEREST_ORDERNUM = 'sw-payment-ec-interest';
    const DEBUG = true;

    public function getLabel()
    {
        return 'ratenkauf by easyCredit';
    }

    public function getVersion()
    {
        return '1.8.6';
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
            'link' => 'https://www.easycredit-ratenkauf.de/haendler.htm',
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
        $this->_createMenuItem();


        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'proxy', 'template','frontend', 'theme'),
        );
    }

   /**
     * @param $version string
     * @return array
     */
    public function update($version)
    {
        $this->_createPaymentConfigForm();
        $this->_createMenuItem();

        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'proxy', 'template','frontend', 'theme'),
        );
    }

    public function afterInit()
    {
        $loader = new \Doctrine\Common\ClassLoader('Netzkollektiv\EasyCreditApi', __DIR__ . '/Library/');
        $loader->register();

        $this->get('Loader')->registerNamespace(
            'Shopware\Plugins\NetzkollektivEasyCredit',
            $this->Path()
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

    public function updateSpecialFieldTypes($type = 'easycreditIntro') {
        $this->get('db')->query("UPDATE s_core_config_elements Set type = ? WHERE name IN ('easycreditBehavior','easycreditCredentials','easycreditMarketing','easycreditClickAndCollectIntro');", 
          array(
            $type
          )
        );
    }

    public function disable()
    {
        $payment = $this->getPayment();
        if ($payment !== null) {
            $payment->setActive(false);
            $this->get('models')->flush($payment);
            $this->updateSpecialFieldTypes('button');
        }

        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend')
        );
    }

    protected function _createEvents()
    {
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Css',
            'addCssFiles'
        );
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Javascript',
            'addJsFiles'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onDispatchLoopStartup'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_EasyCreditCheckout',
            'onInitResourceCheckout'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_EasyCreditMerchant',
            'onInitResourceMerchant'
        );
    }

    public function addJsFiles() {
        $jsDir = $this->Path() . '/Views/frontend/_public/src/js/';
        return new ArrayCollection(array(
            $jsDir . 'jquery.easycredit-address-editor.js',
            $jsDir . 'easycredit-widget.js',
            $jsDir . 'easycredit.js'
        ));
    }

    public function addCssFiles() {
        return new ArrayCollection(array(
            $this->Path() . '/Views/frontend/_public/src/css/easycredit-widget.css',
            $this->Path() . '/Views/frontend/_public/src/css/easycredit.css'
        ));
    }

    public function onDispatchLoopStartup(\Enlight_Event_EventArgs $args) {
        $modelEventManager = Shopware()->Container()->get('models')->getConnection()->getEventManager();
        $modelEventManager->addEventSubscriber(new Subscriber\OrderShipped());
        $modelEventManager->addEventSubscriber(new Subscriber\OrderRefunded());

        $this->get('events')->addSubscriber(new Subscriber\Frontend($this));
        $this->get('events')->addSubscriber(new Subscriber\Backend($this));
        $this->get('events')->addSubscriber(new Subscriber\BackendMerchant($this));
    }

    protected function getLogger() {
        $logger = Shopware()->Container()->get('pluginlogger');

        /*
        if (Shopware()->Config()->get('easycreditDebugLogging')) {
            $this->debug = true;
            $this->allowLineBreaks(true); // allow line breaks in log globally, when easycredit Debug Logging is active
            $handler = $this->_logger->getHandlers();
            if (
                is_array($handlers) 
                && isset($handlers[0]) 
                && $handlers[0] instanceof Monolog\Logger\StreamHandler
                && $handlers[0]->getFormatter() instanceof Monolog\Logger\LineFormatter
            ) {
                $handlers[0]->getFormatter()->allowInlineLineBreaks($bool); 
            }
        }
        */
        return $logger;
    }

    protected function getClient () {
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $this->getLogger(),
                new MessageFormatter(MessageFormatter::DEBUG)
            )
        );

        return new \GuzzleHttp\Client([
            'debug'=> false,
            'handler' => $stack
        ]);
    }

    public function getConfig() {
        $config = Shopware()->Config();

        return ApiV3\Configuration::getDefaultConfiguration()
            ->setHost('https://ratenkauf.easycredit.de')
            ->setUsername($config->get('easycreditApiKey'))
            ->setPassword($config->get('easycreditApiPassword'))
            ->setAccessToken($config->get('easycreditApiSignature'));
    }

    public function onInitResourceCheckout()
    {
        $client = $this->getClient();
        $config = $this->getConfig();

        $webshopApi = new ApiV3\Service\WebshopApi(
            $client,
            $config
        );
        $transactionApi = new ApiV3\Service\TransactionApi(
            $client,
            $config
        );
        $installmentplanApi = new ApiV3\Service\InstallmentplanApi(
            $client,
            $config
        );

        return new ApiV3\Integration\Checkout(
            $webshopApi,
            $transactionApi,
            $installmentplanApi,
            new Api\Storage(),
			new ApiV3\Integration\Util\AddressValidator(),
            new ApiV3\Integration\Util\PrefixConverter(),
            $this->getLogger()
        );
    }

    public function onInitResourceMerchant()
    {
        $client = $this->getClient();
        $config = $this->getConfig()
            ->setHost('https://partner.easycredit-ratenkauf.de');

        $transactionApi = new Api\Service\TransactionApi(
            $client,
            $config
        );

        return new Api\Integration\Merchant(
            $transactionApi,
            $this->getLogger()
        );
    }

    public function registerTemplateDir($viewDir = 'Views') {
        $template = $this->get('template');
        $template->addTemplateDir($this->Path() . $viewDir.'/');
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
                'additionalDescription' => '',
                'template' => 'easycredit.tpl',
                'class' => 'easycredit',
            )
        );
    }

    protected function _createMenuItem()
    {
        Shopware()->Db()->delete('s_core_menu',['controller = ?' => 'EasycreditMerchant']);
        $parent = $this->Menu()->findOneBy(array('label' => 'Zahlungen'));

        $this->createMenuItem(
            array(
                'label' => 'ratenkauf by easyCredit',
                'controller' => 'EasycreditMerchant',
                'action' => 'Index',
                'class' => 'easycredit--icon',
                'active' => 1,
                'parent' => $parent,
            )
        );
    }

    protected function _createPaymentConfigForm()
    {
        $form = $this->Form();

        $position = 10;
        // Frontend settings

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditCredentials',
            array(
                'label' => 'API-Zugangsdaten',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>API-Zugangsdaten</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'text',
            'easycreditApiKey',
            array(
                'label' => 'Webshop-ID',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' ',
                'description' => 'Ihre Webshop-ID finden Sie nach erfolgreicher Anmeldung im easyCredit Händlerinterface im Unterpunkt Shopadministration (z.B. 1.de.xxxx.1).',
                'position' => $position++
            )
        );

        $form->setElement(
            'text',
            'easycreditApiToken',
            array(
                'label' => 'API-Kennwort',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' ',
                'description' => 'Ihr API-Kennwort legen Sie im easyCredit Händlerinterface im Unterpunkt Shopadministration selbst fest.',
                'position' => $position++
            )
        );

        if (is_file(__DIR__ . '/Views/backend/plugins/easycredit/test.js')) {
            $form->setElement(
                'button',
                'easycreditButtonClientTest',
                array(
                    'label' => '<strong>Jetzt API-Zugangsdaten testen<strong>',
                    'handler' => "function(btn) {"
                        . file_get_contents(__DIR__ . '/Views/backend/plugins/easycredit/test.js') . "}",
                    'position' => $position++
                )
            );
        }

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditBehavior',
            array(
                'label' => 'Verhalten',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>Verhalten</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditDebugLogging',
            array(
                'label' => 'API Debug Logging',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditOrderStatus',
            array(
                'label' => 'Bestellungsstatus',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditOrderErrorStatus',
            array(
                'label' => 'Bestellungsstatus bei Fehlern',
                'value' => 4,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditPaymentStatus',
            array(
                'label' => 'Zahlungsstatus',
                'value' => 12,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.PaymentStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );


        $form->setElement(
            'boolean',
            'easycreditRemoveInterestFromOrder',
            array(
                'label' => 'Zinsen nach Bestellabschluss aus Bestellung entfernen',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Die Ausweisung der beim Ratenkauf anfallenden Zinsen ggü. dem Kunden ist rechtlich erforderlich. Für die Klärung, wie Sie die Zinsen mit in Ihre Buchhaltung übernehmen, empfehlen wir Ihnen sich mit Ihrem Steuerberater abzustimmen.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditMarkShipped',
            array(
                'label' => '„Lieferung melden“ automatisch durchführen?',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Bei Aktivierung dieser Option wird die Lieferung bei dem in der folgenden Option eingestellten Bestellstatus automatisch an ratenkauf by easyCredit übermittelt.',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditMarkShippedStatus',
            array(
                'label' => 'Lieferung bei folgendem Bestellstatus melden',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditMarkRefunded',
            array(
                'label' => 'Rückabwicklung automatisch durchführen?',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Bei Aktivierung dieser Option wird die Rückabwicklung bei dem in der folgenden Option eingestellten Bestellstatus automatisch an ratenkauf by easyCredit übermittelt.',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditMarkRefundedStatus',
            array(
                'label' => 'Rückabwicklung bei folgendem Bestellstatus durchführen',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditMarketing',
            array(
                'label' => 'Marketing',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>Marketing</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditModelWidget',
            array(
                'label' => 'Zeige Modellrechner-Widget neben Produktpreis',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Für den größten Erfolg mit dem ratenkauf by easyCredit empfehlen wir, das Widget zu aktivieren.',
                'position' => $position++
            )
        );


        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditClickAndCollectIntro',
            array(
                'label' => 'Click & Collect',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => file_get_contents(dirname(__FILE__).'/Views/backend/easycredit_config/clickandcollect.html'),
                'handler' => "function(btn) { Ext.Msg.alert('Click & Collect', btn.helpText); }",
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditClickAndCollectShippingMethod',
            array(
                'label' => 'Versandart',
                'value' => '',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.Dispatch',
                'displayField' => 'name',
                'valueField' => 'id',
                'position' => $position++
            )
        );
    }

    public function getCheckout() {
        return $this->get('easyCreditCheckout');
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
        $quote = new Api\Quote();

        return $this->isInterestInBasket()
            && $this->getCheckout()->isAmountValid($quote)
            && $this->getCheckout()->verifyAddressNotChanged($quote);
    }

    public function clear() {
        $this->getCheckout()->clear();
        $this->removeInterest();
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

    public function addInterest($refresh = true) {
        $interestAmount = Shopware()->Session()->EasyCredit['interest_amount'];
        if (empty($interestAmount)) {
            return;
        }

        $this->removeInterest($refresh);

        $this->get('db')->insert(
            's_order_basket',
            array(
                'sessionID' => Shopware()->Session()->offsetGet('sessionId'),
                'articlename' => 'Zinsen für Ratenzahlung',
                'articleID' => 0,
                'ordernumber' => self::INTEREST_ORDERNUM,
                'quantity' => 1,
                'price' => $interestAmount,
                'netprice' => $interestAmount,
                'tax_rate' => 0,
                'datum' => new Zend_Date(),
                'modus' => 4,
                'shippingfree' => 1,
                'currencyFactor' => 1
            )
        );

        if ($refresh) {
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }
    }

    /**
     * remove interest from basket via DB call
     */
    public function removeInterest($refresh = true)
    {
        $session = Shopware()->Session();

        Shopware()->Db()->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $session->get('sessionId'),
                'ordernumber = ?' => self::INTEREST_ORDERNUM
            )
        );

        if ($refresh) {
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }
    }
}
