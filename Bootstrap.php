<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap 
    extends Shopware_Components_Plugin_Bootstrap
{
    public function afterInit()
    {
        $loader = new \Doctrine\Common\ClassLoader('Netzkollektiv\EasyCredit', __DIR__ . '/src');
        $loader->register();
    }

    public function getLabel() {
        return 'Ratenkauf by easyCredit';
    }
 
    public function getVersion()
    {
        return '1.0.0';
    }
 
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'supplier' => 'Teambank AG',
            'author' => 'Teambank AG',
            'description' => 'Dieses Plugin ermöglicht die Zahlung mittels Ratenkauf by easyCredit',
            'support' => 'service@easycredit.de',
            'link' => 'https://www.easycredit.de/Ratenkauf.htm',
        );
    }

    public function getCapabilties() {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true,
            'secureUninstall' => true
        );
    }
 
    public function install()
    {

        $this->createMyEvents();
        $this->createMyMenu();
        $this->createMyForm();
        $this->createMyPayment();

        return true;
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

    /**
     * Disable plugin method and sets the active flag in the payment row
     *
     * @return bool
     */
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

    /**
     * Creates and subscribe the events and hooks.
     */
    private function createMyEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit',
            'onGetControllerPathFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_EasyCreditCheckout',
            'onInitResourceCheckout'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment',
            'onSaveShippingPayment'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Checkout_SaveShippingPayment',
            'afterSaveShippingPayment'
        );
    }

    public function addInterestSurcharge(Enlight_Event_EventArgs $arguments) {


        // Fixed surcharge
        if (!empty($payment['surcharge']) && (empty($dispatch) || $dispatch['surcharge_calculation'] == 3)) {
            $surcharge = round($payment['surcharge'], 2);
            $payment['surcharge'] = 0;
            if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $surcharge_net = $surcharge;
                //$tax_rate = 0;
            } else {
                $surcharge_net = round($surcharge / (100 + $discount_tax) * 100, 2);
            }

            $this->db->insert(
                's_order_basket',
                array(
                    'sessionID' => Shopware()->Session()->offsetGet('sessionId'),
                    'articlename' => 'Zinsen für Ratenzahlung',
                    'articleID' => 0,
//                    'ordernumber' => $surcharge_ordernumber,
                    'quantity' => 1,
                    'price' => $surcharge,
                    'netprice' => $surcharge_net,
                    'tax_rate' => 0,
                    'datum' => new Zend_Date(),
                    'modus' => 4,
                    'currencyFactor' => $currencyFactor
                )
            );
        }
    }

    public function afterSaveShippingPayment(Enlight_Event_EventArgs $arguments) {
echo get_class(Shopware()); exit;
        $controller = $arguments->getSubject();
        $request = $controller->Request();
        $response = $controller->Response();

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            (int)$request->getPost('payment')
        );
        if ($payment['name'] != 'easycredit') {
            return;
        } 

        $this->handleInterestSurcharge();       
    }

    public function onSaveShippingPayment(Enlight_Event_EventArgs $arguments) {
        $checkoutController = $arguments->getSubject();
        $request = $checkoutController->Request();

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            (int)$request->getPost('payment')
        );
        if ($payment['name'] != 'easycredit') {
            return;
        }

        if (false === $this->_handleRedirect($checkoutController, $request)) {
            return false;
        }
    }

    protected function _handleRedirect($controller, $request) {
        if (!$request->isPost() || $request->getParam('isXHR')) {
            return;
        }

        $controller->redirect(array(
            'module'=>'payment_easycredit',
            'action'=>'gateway'
        ));
        return false; // do not save shipping/payment
    }

    public function onInitResourceCheckout()
    {
        $logger = new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Logger();

        $apiClient = new EasyCredit\Api(array(
//            'api_key' => $this->Config()->get('easycreditApiKey'),
//            'api_token' => $this->Config()->get('easycreditApiToken')
            'api_key' => '2.de.9999.10001',
            'api_token' => '3nx-d5d-vpo'
        ), $logger);

        return new EasyCredit\Checkout(
            $apiClient,
            new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Storage()
        );
    }

    /**
     * Fetches and returns easycredit payment row instance.
     *
     * @return \Shopware\Models\Payment\Payment
     */
    public function getPayment()
    {
        return $this->Payments()->findOneBy(
            array('name' => 'easycredit')
        );
    }

    /**
     * Creates and save the payment row.
     */
    private function createMyPayment()
    {
        $this->createPayment(
            array(
                'name' => 'easycredit',
                'description' => 'easyCredit',
                'action' => 'payment_easycredit',
                'active' => 0,
                'position' => 0,
                'additionalDescription' => $this->getPaymentLogo() . 'Ratenkauf by easyCredit.'
            )
        );
    }

    private function getPaymentLogo()
    {
        return;
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return '<!-- PayPal Logo -->' .
        '<a onclick="window.open(this.href, \'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=500\'); return false;"' .
        ' href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank">' .
        '<img src="{link file=\'frontend/_resources/images/paypal_logo.png\' fullPath}" alt="Logo \'PayPal empfohlen\'">' .
        '</a><br>' . '<!-- PayPal Logo -->';
    }

    /**
     * Creates and stores a payment item.
     */
    private function createMyMenu()
    {
        $parent = $this->Menu()->findOneBy(array('label' => 'Zahlungen'));
        $this->createMenuItem(
            array(
                'label' => 'EasyCredit',
                'controller' => 'PaymentEasyCredit',
                'action' => 'Index',
                'class' => 'easycredit--icon',
                'active' => 1,
                'parent' => $parent
            )
        );
    }

    /**
     * Creates and stores the payment config form.
     */
    private function createMyForm()
    {
        $form = $this->Form();
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
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @return string
     */
    public function onGetControllerPathFrontend()
    {
        $templateVersion = $this->get('shop')->getTemplate()->getVersion();
        $this->registerMyTemplateDir($templateVersion >= 3);

        return __DIR__ . '/Controllers/Frontend/PaymentEasycredit.php';
    }

    /**
     * @param bool $responsive
     */
    public function registerMyTemplateDir($responsive = false)
    {
        if ($responsive) {
            $this->get('template')->addTemplateDir(__DIR__ . '/Views/responsive/', 'easycredit_responsive');
        }
        $this->get('template')->addTemplateDir(__DIR__ . '/Views/', 'easycredit');
    }
}
