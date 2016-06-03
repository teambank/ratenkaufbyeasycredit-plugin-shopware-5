<?php
use Netzkollektiv\EasyCredit;
use Shopware\Plugins\NetzkollektivEasycredit\Subscriber;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap
    extends Shopware_Components_Plugin_Bootstrap
{
    const INTEREST_REMOVED_ERROR = 'Raten müssen neu berechnet werden';

    public function afterInit()
    {
        $loader = new \Doctrine\Common\ClassLoader('Netzkollektiv\EasyCredit', __DIR__ . '/src');
        $loader->register();

        $this->get('Loader')->registerNamespace(
            'Shopware\Plugins\NetzkollektivEasycredit',
            $this->Path()
        );
    }

    public function getLabel()
    {
        return 'Ratenkauf by Easycredit';
    }

    public function getVersion()
    {
        return '1.1.0';
    }

    public function getInterestOrderName()
    {
        return 'sw-payment-ec-interest';
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
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout',
            'registerCheckoutController'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_EasyCreditCheckout',
            'onInitResourceCheckout'
        );
	    $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout',
            'onFrontendCheckoutPostDispatch'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit',
            'onGetControllerPathFrontend'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentEasycredit',
            'onGetControllerPathPaymentEasycredit'
        );

        if ($this->isShopware5()) {
            $this->subscribeEvent(
                'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment',
                'onSaveShippingPayment'
            );
        } else if ($this->isShopware4()) {
            $this->subscribeEvent(
                'Enlight_Controller_Action_Frontend_Account_SavePayment',
                'onSavePayment'
            );
            $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Account',
                'onFrontendAccountPostDispatch'
            );
        } else {
            throw new Exception('easycredit Plugin does not support this Shopware version');
        }
    }

    public function registerCheckoutController(Enlight_Event_EventArgs $arguments) {
        if ($arguments->getSubject() instanceof Shopware_Controllers_Frontend_Checkout) {
            Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote::setCheckoutController($arguments->getSubject());    
        }
    }

    public function isShopware5() {
        return version_compare(\Shopware::VERSION, '5.0.0','>='); 
    }

    public function isShopware4() {
        return version_compare(\Shopware::VERSION, '4.0.0','>=')
            && version_compare(\Shopware::VERSION, '5.0.0','<=');
    }

    public function getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    public function getBasket()
    {
        return Shopware()->Modules()->Basket()->sGetBasket();
    }

    public function registerTemplateDir() {

        $template = $this->get('template');
        $template->addTemplateDir($this->Path() . 'Views/');
        $template->addTemplateDir($this->Path() . 'Views/common/');

        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
            $template->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $template->addTemplateDir($this->Path() . 'Views/emotion/');
        }
    }

    public function resetPaymentToDefault($paymentId = null) {
        $user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);

        if ($paymentId === null)
            $paymentId = Shopware()->Config()->get('paymentdefault');

        $user->setPaymentId($paymentId);
        Shopware()->Models()->persist($user);
        Shopware()->Models()->flush();
        Shopware()->Models()->refresh($user);
    }

    public function unsetStorage() {
        unset(Shopware()->Session()->EasyCredit["interest_amount"]);
        unset(Shopware()->Session()->EasyCredit["authorized_amount"]);
        unset(Shopware()->Session()->EasyCredit["pre_contract_information_url"]);
        unset(Shopware()->Session()->EasyCredit["redemption_plan"]);
        unset(Shopware()->Session()->EasyCredit["transaction_id"]);
    }

    /**
     * remove interest from basket via DB call
     */
    public function removeInterest()
    {
        $session = Shopware()->Session();

        $this->get('db')->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $session->get('sessionId'),
                'ordernumber = ?' => $this->getInterestOrderName()
            )
        );

        $this->unsetStorage();
    }


    /**
     * checks if the easycredit interest identified by getInterestOrderName() is in the user's basket
     *
     * @return bool
     */
    public function isInterestInBasket()
    {
        $basket = $this->getBasket();

        $content = $basket['content'];
        foreach ($content as $c) {
            if (isset($c['ordernumber']) && $c['ordernumber'] == $this->getInterestOrderName()) {
                return true;
            }
        }
        return false;
    }

    public function redirectCheckoutConfirm($action) {
        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'confirm'
        ));
    }

    public function getAmount(){
        $quote = new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote();
        return $quote->getGrandTotal();
    }

    protected function _getSelectedPaymentMethod() {
        $user = $this->getUser();
        $payment = $user['additional']['payment'];
        $paymentName = $payment['name'];
        return $paymentName;
    }

    public function checkInterest($action, $view) {

        $paymentName = $this->_getSelectedPaymentMethod();

        $amount_basket = round($this->getAmount(), 2);

        $amount_authorized = round(Shopware()->Session()->EasyCredit["authorized_amount"], 2);
        $amount_interest = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);
        $amount_basket_without_interest = round($amount_basket - $amount_interest, 2);

        $interestInBasket = $this->isInterestInBasket();

        // other payment method but interest still in basket => remove interest
        if (
            $paymentName != 'easycredit'
            && $interestInBasket
        ) {
            $this->removeInterest();

            $this->_redirectWithError($action);
            return true;
        } // still easycredit but amount has changed => remove interest and reset payment method to shop default

        if (
            $paymentName == 'easycredit'
            && $interestInBasket
            && $amount_authorized != $amount_basket_without_interest
        ) {

            $this->removeInterest();
            $this->resetPaymentToDefault();

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = self::INTEREST_REMOVED_ERROR;
            }

            $this->_redirectWithError($action);
            return true;
        } // easycredit without interest rates should not be possible => remove it
        elseif (
            $paymentName == 'easycredit'
            && !$interestInBasket
        ) {
            $this->resetPaymentToDefault();

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = self::INTEREST_REMOVED_ERROR;
            }
            $this->_redirectWithError($action);
            return true;
        }

        return false;
    }

    protected function _redirectWithError($action) {
        if ($this->isShopware5() && $action instanceof Shopware_Proxies_ShopwareControllersFrontendCheckoutProxy) {
            $payment = $action->getSelectedPayment();
            if (array_key_exists('validation', $payment) && !empty($payment['validation'])) {
                return $action->redirect(array(
                    'controller' => 'checkout',
                    'action' => 'shippingPayment'
                ));
            }
        }

        return $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'confirm'
        ));
    }

    public function _extendPaymentTemplate($action, $view) {
        $checkout = $action->get('easyCreditCheckout');

        $error = false;
        try {
            $checkout->checkInstallmentValues(round($this->getAmount(), 2));
        } catch(Exception $e) {
            $error = $e->getMessage();
        }

        $view->assign('EasyCreditError', $error)
            ->assign('EasyCreditThemeVersion',Shopware()->Shop()->getTemplate()->getVersion())
            ->assign('EasyCreditPaymentCompanyName', $this->Config()->get('easycreditCompanyName'));
    }

    protected function _redirectToEasycredit($action) {
        try {
            $checkout = $this->get('easyCreditCheckout')
               ->setCancelUrl($this->getUrl('cancel'))
               ->setReturnUrl($this->getUrl('return'))
               ->setRejectUrl($this->getUrl('reject'))
               ->start(new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote($action));

            if ($url = $checkout->getRedirectUrl()) {
                header('Location: '.$url);
                exit;
            }
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->redirectCheckoutConfirm($action);
            return;
        }
    }

    protected function _checkRedirect($action) {
        if (
            isset(Shopware()->Session()->EasyCredit["externRedirect"])
            && Shopware()->Session()->EasyCredit["externRedirect"]
            && empty(Shopware()->Session()->EasyCredit["apiError"])
        ) {
            $this->_redirectToEasycredit($action);
            return true;
        }
        return false;
    }

    public function _extendConfirmTemplate($view) {
        $user = $this->getUser();

        $paymentName = $user['additional']['payment']['name'];

        if ($paymentName != 'easycredit') {
            return;
        }

        if (Shopware()->Shop()->getTemplate()->getVersion() == 2) {
            $view->extendsTemplate('frontend/checkout/confirm_easycredit.tpl');
        }
        $view->assign('EasyCreditPaymentShowRedemption', true)
            ->assign('EasyCreditThemeVersion',Shopware()->Shop()->getTemplate()->getVersion())
            ->assign('EasyCreditPaymentRedemptionPlan', Shopware()->Session()->EasyCredit["redemption_plan"])
            ->assign(
                'EasyCreditPaymentPreContractInformationUrl',
                Shopware()->Session()->EasyCredit["pre_contract_information_url"]
            );
    }

    public function onFrontendAccountPostDispatch(\Enlight_Event_EventArgs $arguments) {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->registerTemplateDir();

        switch ($request->getActionName()) {
            case 'payment':
                $this->_extendPaymentTemplate($action, $view);
                break;
        }        
    }

    protected function _onCheckoutConfirm($view) {
        $checkout = $this->get('easyCreditCheckout');

        try {
            $approved = $checkout->isApproved();
            if (!$approved) {
                throw new Exception($this->getLabel().' wurde nicht genehmigt.');
            }

            $checkout->loadFinancingInformation();
        } catch (Exception $e) {
            $view->sBasketInfo = Shopware()->Snippets()->getSnippet()->get(
                'CheckoutSelectPremiumVariant',
                $e->getMessage(),
                true
            );
        }
    }

    protected function _addInterestSurcharge() {
        if (!isset(Shopware()->Session()->EasyCredit["interest_amount"])
            || empty(Shopware()->Session()->EasyCredit["interest_amount"])
           ) {
            return;
        }

        $interest_order_name = 'sw-payment-ec-interest';

        $interest_amount = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);

        $this->get('db')->delete(
            's_order_basket',
            array(
                'sessionID = ?' => Shopware()->Session()->get('sessionId'),
                'ordernumber = ?' => $interest_order_name
            )
        );

        $this->get('db')->insert(
            's_order_basket',
            array(
                'sessionID' => Shopware()->Session()->offsetGet('sessionId'),
                'articlename' => 'Zinsen für Ratenzahlung',
                'articleID' => 0,
                'ordernumber' => $interest_order_name,
                'quantity' => 1,
                'price' => $interest_amount,
                'netprice' => $interest_amount,
                'tax_rate' => 0,
                'datum' => new Zend_Date(),
                'currencyFactor' => 1,
                'shippingfree' => 1
            )
        );

    }

    protected function _displayError($action) {
        $error = false;
        if (!empty(Shopware()->Session()->EasyCredit["apiError"]) && !$action->Response()->isRedirect()) {
_log(Shopware()->Session()->EasyCredit["apiError"]);
            $error = Shopware()->Snippets()->getSnippet()->get(
                'CheckoutSelectPremiumVariant',
                Shopware()->Session()->EasyCredit["apiError"].' Bitte wählen Sie erneut <strong>'.$this->getLabel().'</strong> in der Zahlartenauswahl.',
                true
            );
            Shopware()->Session()->EasyCredit["apiError"] = null;
_log('error removed');
        }
        return $error;
    }

    public function onFrontendCheckoutPostDispatch(\Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();
        
        $this->registerTemplateDir();

        if ($this->_checkRedirect($action)) {
            return;
        }

        switch ($request->getActionName()) {
            case 'confirm':
                if ($this->checkInterest($action, $view)) {
                    return;
                }
                if ($error = $this->_displayError($action)) {
                    $view->sBasketInfo = $error; 
                }

                if ($this->_getSelectedPaymentMethod() !== 'easycredit') {
                    return;
                }

                $this->_onCheckoutConfirm($view);
                $this->_extendConfirmTemplate($view);
                break;

            case 'shippingPayment':
                if (!$this->isShopware5()) {
                    break;
                }
                if ($error = $this->_displayError($action)) {
                    $view->sBasketInfo = $error;
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'finish':
                $this->unsetStorage();
        }
    }

    public function onSavePayment(Enlight_Event_EventArgs $arguments) {
        $action = $arguments->getSubject();
        $request = $action->Request();

        if (!$request->isPost()) {
            return;
        }

        $values = $request->getPost('register');
	    $paymentId = $values['payment'];

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            $paymentId
        );

        if ($payment['name'] != 'easycredit') {
            return;
        }

        $agreementChecked = $request->getParam('sEasycreditAgreement');
        if (empty($agreementChecked)) {
            return;
        }

        if ($request->getParam('sTarget') !== 'checkout') {
            return;
        }
	    return $this->_handleRedirect($paymentId, $action, true);
    }

    public function onSaveShippingPayment(Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();

        if (!$request->isPost()
            || $request->getParam('isXHR')
        ) {
            return;
        }

	    return $this->_handleRedirect((int)$request->getPost('payment'), $action);
    }

    protected function _handleRedirect($selectedPaymentId, $action, $queue = false) {

        if ($this->isInterestInBasket()) {
            $this->removeInterest();
        }

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            $selectedPaymentId
        );

        if ($payment['name'] != 'easycredit') {
            return;
        }

        $amount_basket = round($this->getAmount(), 2);
        $amount_authorized = round(Shopware()->Session()->EasyCredit["authorized_amount"], 2);
        $amount_interest = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);
        $amount_basket_without_interest = round($amount_basket - $amount_interest, 2);

        if (
            $this->isInterestInBasket()
            && $amount_authorized == $amount_basket_without_interest
        ) {
            return;
        }

        if ($queue) {
            Shopware()->Session()->EasyCredit["externRedirect"] = true;
            return;
        }

        $this->_redirectToEasycredit($action);
    }

    public function getUrl($action) {
        return Shopware()->Front()->Router()->assemble(array(
            'controller' => 'payment_easycredit',
            'action'    => $action,
        ));
    }

    public function onInitResourceCheckout()
    {

        $logger = new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Logger();

        $apiClient = new EasyCredit\Api(array(
            'api_key' => $this->Config()->get('easycreditApiKey'),
            'api_token' => $this->Config()->get('easycreditApiToken')
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
                'name' => 'easycredit',
                'description' => $this->getLabel(),
                'action' => 'payment_easycredit',
                'active' => 0,
                'position' => 0,
                'additionalDescription' => $this->getPaymentLogo(). ' <a href="https://www.easycredit.de/Ratenkauf.htm" onclick="javascript:window.open(\'https://www.easycredit.de/Ratenkauf.htm\',\'whatiseasycredit\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=0, top=0, width=800, height=600\'); return false;" class="easycredit-tooltip" style="color:#000000;">Was ist '.$this->getLabel().'?</a>',
                'template' => 'easycredit.tpl',
                'class' => 'easycredit',
            )
        );
    }

    protected function getPaymentLogo($enabled = true)
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

    protected function _createPaymentConfigForm()
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

        $form->setElement(
            'text',
            'easycreditCompanyName',
            array(
                'label' => 'Rechtlicher Firmenname',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            )
        );
    }

    public function onGetControllerPathFrontend() {
        $this->registerTemplateDir();
        return $this->Path() . 'Controllers/Frontend/PaymentEasycredit.php';
    }

    public function onDispatchLoopStartup(\Enlight_Event_EventArgs $args) {
        $this->get('events')->addSubscriber(new Subscriber\Payment());
    }

    public function onGetControllerPathPaymentEasycredit(\Enlight_Event_EventArgs $args) {
        return $this->Path() . 'Controllers/Backend/PaymentEasycredit.php';
    }
}
