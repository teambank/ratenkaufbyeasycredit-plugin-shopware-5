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

    public function getLabel()
    {
        return 'Ratenkauf by easyCredit';
    }

    public function getVersion()
    {
        return '1.0.2.1';
    }

    public function getInterestOrderName()
    {
        return 'sw-payment-ec-interest';
    }

    public function getCommunicationError() {
        return 'Kommunikationsproblem zum EasyCredit Server. Bitte versuchen sie es später nocheinmal.';
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

        $this->createMyEvents();
        $this->createMyMenu();
        $this->createMyForm();
        $this->createMyPayment();
        $this->createAdditionalOrderAttributes();

        return true;
    }

    public function createAdditionalOrderAttributes() {
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
            'Enlight_Bootstrap_InitResource_EasyCreditCheckout',
            'onInitResourceCheckout'
        );
	    $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'onFrontendCheckoutPostDispatch'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit',
            'onGetControllerPathFrontend'
        );

        if ($this->isShopware5()) {
            $this->subscribeEvent(
                'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment',
                'onSaveShippingPayment'
            );
        }
        else if ($this->isShopware4()) {
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

    /**
     * Sets the payment method for the current to paymentId
     * if no ID given the shops default payment method is used
     *
     * @param null $paymentId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function setPaymentId($paymentId = null) {
        $user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);

        if ($paymentId === null)
            $paymentId = Shopware()->Config()->get('paymentdefault');

        $user->setPaymentId($paymentId);
        Shopware()->Models()->persist($user);
        Shopware()->Models()->flush();
        Shopware()->Models()->refresh($user);
    }

    public function resetPaymentToDefault() {
        $this->setPaymentId();
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
Shopware()->PluginLogger()->info('removing Interest Rate');
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

    public function reloadCheckoutConfirm($action) {
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

    protected $_interestRemovedError = 'Raten müssen neu berechnet werden. Bitte wählen Sie erneut Ratenkauf by Easycredit in der Zahlartenauswahl.';

    public function checkInterest($action, $view) {

        //$this->showInterestRemovedError($view);

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
Shopware()->PluginLogger()->info('checkInterest: other payment method but interest still in basket => remove interest');
            $this->removeInterest();

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));

            return true;
        } // still easycredit but amount has changed => remove interest and reset payment method to shop default

        if (
            $paymentName == 'easycredit'
            && $interestInBasket
            && $amount_authorized != $amount_basket_without_interest
        ) {
Shopware()->PluginLogger()->info('checkInterest: '.$amount_authorized.' != '.$amount_basket_without_interest);

            $this->removeInterest();
            $this->resetPaymentToDefault();

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = $this->_interestRemovedError;
            }

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));

            return true;
        } // easycredit without interest rates should not be possible => remove it
        elseif (
            $paymentName == 'easycredit'
            && !$interestInBasket
        ) {
Shopware()->PluginLogger()->info('checkInterest: easycredit without interest rates should not be possible => remove it');
            $this->resetPaymentToDefault();
            $this->reloadCheckoutConfirm($action);

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = $this->_interestRemovedError;
            }

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));
            return true;
        }

        return false;
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
        $action->redirect(array(
            'module' => 'payment_easycredit',
            'action' => 'gateway'
        ));
    }

    protected function _checkRedirect($action) {
        if (
            isset(Shopware()->Session()->EasyCredit["externRedirect"])
            && Shopware()->Session()->EasyCredit["externRedirect"]
        ) {
Shopware()->PluginLogger()->info(__METHOD__.': redir to gatway');
            $this->_redirectToEasycredit($action);
            return true;
        }
        return false;
    }

    public function _changeConfirmTemplate($view) {
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
Shopware()->PluginLogger()->info('easycredit approved? '.$approved);
            if (!$approved) {
                throw new Exception('EasyCredit Ratenkauf wurde nicht genehmigt.');
            }

            $checkout->loadFinancingInformation();

            $this->_addInterestSurcharge();

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
Shopware()->PluginLogger()->info(__METHOD__.': interest amount is empty');
            return;
        }
Shopware()->PluginLogger()->info(__METHOD__.': '.Shopware()->Session()->EasyCredit["interest_amount"]);

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
                'modus' => 4,
                'currencyFactor' => 1
            )
        );

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

                if (!empty(Shopware()->Session()->EasyCredit["apiError"])) {
                    $view->sBasketInfo = Shopware()->Snippets()->getSnippet()->get(
                        'CheckoutSelectPremiumVariant',
                        Shopware()->Session()->EasyCredit["apiError"],
                        true
                    );
                    Shopware()->Session()->EasyCredit["apiError"] = null;
                }

                if ($this->_getSelectedPaymentMethod() !== 'easycredit') {
                    return;
                }

                $this->_onCheckoutConfirm($view);
                $this->_changeConfirmTemplate($view);
                break;

            case 'shippingPayment':
                if (!$this->isShopware5()) {
                    break;
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

        $agreementChecked = $request->getParam('easycredit-agreement');
        if (empty($agreementChecked)) {
            $action->redirect(array('controller'=>'account', 'action'=>'payment','sTarget'=>'checkout'));
            return false;
        }

        if ($request->getParam('sTarget') !== 'checkout') {
            return;
        }


	    return $this->_handleRedirect($paymentId);
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

	    return $this->_handleRedirect((int)$request->getPost('payment'));
    }

    protected function _handleRedirect($selectedPaymentId) {
Shopware()->PluginLogger()->info('_handleRedirect (payment = '.$selectedPaymentId.')');
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
Shopware()->PluginLogger()->info('_handleRedirect: externRediret = true');
        Shopware()->Session()->EasyCredit["externRedirect"] = true;
    }

    /**
     * Fetches and returns easycredit payment row instance.
     * @version 5.0.0
     * @return \Shopware\Models\Payment\Payment
     */
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
                'description' => 'Ratenkauf by easyCredit',
                'action' => 'payment_easycredit',
                'active' => 0,
                'position' => 0,
                'additionalDescription' => $this->getPaymentLogo(). ' <a href="https://www.easycredit.de/Ratenkauf.htm" onclick="javascript:window.open(\'https://www.easycredit.de/Ratenkauf.htm\',\'whatiseasycredit\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=0, top=0, width=800, height=600\'); return false;" class="easycredit-tooltip" style="color:#000000;">Was ist Ratenkauf by easyCredit?</a>',
                'template' => 'easycredit.tpl'
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

    /**
     * Returns the path to a frontend controller for an event.
     * @version 5.0.0
     * @return string
     */
    public function onGetControllerPathFrontend()
    {
        $this->registerTemplateDir();

        return __DIR__ . '/Controllers/Frontend/PaymentEasycredit.php';
    }

}
