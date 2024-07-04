<?php
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
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

    public function getLabel()
    {
        return 'easyCredit-Ratenkauf';
    }

    public function getVersion()
    {
        return '2.1.8';
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
            'link' => 'https://www.easycredit-ratenkauf.de/',
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
        $this->_createAttributes();

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
        $this->_createAttributes();
        $this->_applyBrandRelaunch();

        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'proxy', 'template','frontend', 'theme'),
        );
    }

    public function afterInit()
    {
        $this->get('loader')->registerNamespace(
            'Shopware\Plugins\NetzkollektivEasyCredit',
            $this->Path()
        );
        $this->get('loader')->registerNamespace(
            'EasyCredit',
            $this->Path() . 'Components/'
        );
    }

    /**
     * Activate the plugin easycredit plugin.
     * Sets the active flag in the payment row.
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
        $this->get('db')->query("UPDATE s_core_config_elements Set type = ? WHERE name IN ('easyCreditIntro','easycreditBehavior','easycreditCredentials','easyCreditClickAndCollectIntro','easyCreditClickAndCollectHeading','easycreditMarketing','easyCreditMarketingExpressHeading','easyCreditMarketingWidgetHeading','easyCreditMarketingModalHeading','easyCreditMarketingCardHeading','easyCreditMarketingFlashboxHeading','easyCreditMarketingBarHeading');",
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
            $jsDir . 'easycredit-express-button.js',
            $jsDir . 'easycredit.js',
            $jsDir . 'easycredit-marketing.js'
        ));
    }

    public function addCssFiles() {
        return new ArrayCollection(array(
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

    protected function getClient () {
        if (class_exists('\GuzzleHttp\Client') && method_exists('\GuzzleHttp\Client','sendRequest')) {
            $stack = HandlerStack::create();

            if (Shopware()->Config()->get('easycreditDebugLogging')) {
                $stack->push(
                    Middleware::log(
                        Shopware()->Container()->get('pluginlogger'),
                        new MessageFormatter(MessageFormatter::DEBUG)
                    )
                );
            }

            return new \GuzzleHttp\Client([
                'handler' => $stack
            ]);
        }
        return new ApiV3\Client(
            Shopware()->Config()->get('easycreditDebugLogging') ? Shopware()->Container()->get('pluginlogger') : null
        );
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
            Shopware()->Container()->get('pluginlogger')
        );
    }

    public function getStorage() {
        return new Api\Storage();
    }

    public function onInitResourceMerchant()
    {
        $client = $this->getClient();
        $config = $this->getConfig()
            ->setHost('https://partner.easycredit-ratenkauf.de');

        return new ApiV3\Service\TransactionApi(
            $client,
            $config
        );
    }

    public function registerTemplateDir($viewDir = 'Views') {
        $template = $this->get('template');
        $template->addTemplateDir($this->Path() . $viewDir.'/');
    }

    protected $paymentsCache = null;
    public function getPayment()
    {
        if (null === $this->paymentsCache) {
            $this->paymentsCache = $this->Payments()->findOneBy(
                array('name' => 'easycredit')
            );
        }
        return $this->paymentsCache;
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
        Shopware()->Db()->delete('s_core_snippets',['name = ?' => 'EasycreditMerchant']);

        $parent = $this->Menu()->findOneBy(array('label' => 'Zahlungen'));

        $this->createMenuItem(
            array(
                'label' => 'easyCredit-Ratenkauf',
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
        $pluginForm = $this->Form();

        if (!class_exists('EasyCredit_BackendFormBuilder')) {
            // fix for SW <= 5.2
            require_once __DIR__.'/Components/BackendFormBuilder.php';
        }
        $builder = new EasyCredit_BackendFormBuilder();
        $builder->build($pluginForm);
    }

    protected function _createAttributes()
    {
        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');
    
        if ($this->assertMinimumVersion('5.2.0')) {
            /** @var CrudService $service */
            $service = Shopware()->Container()->get('shopware_attribute.crud_service');
            $service->update('s_order_attributes', 'easycredit_token', 'varchar', [
                'label' => 'easyCredit-Ratenkauf TransactionId (technical)',
                'displayInBackend' => false,
                'position' => 900,
                'custom' => false,
                'translatable' => false,
            ]);
        } else {
            $em->addAttribute(
                's_order_attributes',
                'easycredit',
                'token',
                'varchar(255)',
                true,
                null
            );
        }
        
        $metaDataCache = $em->getConfiguration()->getMetadataCacheImpl();
        if ($metaDataCache !== null && method_exists($metaDataCache, 'deleteAll')) {
            $metaDataCache->deleteAll();
        }
        $em->generateAttributeModels(['s_order_attributes']);
    }

    public function _applyBrandRelaunch() {
        Shopware()->Db()->query("
            UPDATE s_core_plugins Set description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf') WHERE name = 'NetzkollektivEasyCredit';
            UPDATE s_core_paymentmeans Set description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf') WHERE name = 'easycredit';
            UPDATE s_core_config_forms Set
                description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf'),
                label = REPLACE(label, 'ratenkauf by easyCredit','easyCredit-Ratenkauf')
            WHERE name = 'NetzkollektivEasyCredit';
        ");
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
        $quote = $this->getQuote();
        return $this->isInterestInBasket()
            && $this->getCheckout()->isAmountValid($quote)
            && $this->getCheckout()->verifyAddress($quote);
    }

    public function clear() {
        $this->getCheckout()->clear();
        $this->removeInterest();
    }

    protected $quoteCache = null;

    public function getQuote() {
        if ($this->quoteCache === null) {
            $quote = new Api\QuoteBuilder();
            $this->quoteCache = $quote->build();
        }
        return $this->quoteCache;
    }

    public function isResponsive() {
        return Shopware()->Shop()->getTemplate()->getVersion() >= 3;
    }

    public function isSelected($paymentId = null) {
        if ($paymentId == null) {
            $helper = new \EasyCredit_Helper();
            $user = $helper->getUser();
            $paymentId = $user['additional']['payment']['id'];
        }
        return $paymentId == $this->getPayment()->getId();
    }

    public function addInterest($refresh = true) {
        $interestAmount = Shopware()->Session()->offsetGet('EasyCredit')['interest_amount'];
        if ($interestAmount === null) {
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
                'shippingfree' => 0,
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
