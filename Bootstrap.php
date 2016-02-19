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
        return '1.0.0';
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
        //$e = new Exception();
        //print($e.getTraceAsString());

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
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch',
            110
        );


    }


    public function getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return null;
        }
    }

    public function getBasket()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        } else {
            return null;
        }
    }


    public function getAmount()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])) {
            return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
        } else {
            return $basket['AmountNetNumeric'];
        }
    }

    public function registerMyTemplateDir($responsive = false)
    {
        if ($responsive) {
            $this->get('template')->addTemplateDir(__DIR__ . '/Views/responsive/', 'easycredit_responsive');
        }
        $this->get('template')->addTemplateDir(__DIR__ . '/Views/', 'easycredit');
    }


    protected function setErrorMessages(\Enlight_View_Default $view, $message)
    {
        $errorMessages = $view->getAssign('sErrorMessages');
        $errorMessages[] = $message;

        $view->assign('sErrorMessages', $errorMessages);
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

    public function resetPaymentIdToDefaultId() {
        $this->setPaymentId();
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

        unset(Shopware()->Session()->EasyCredit["interest_amount"]);
        unset(Shopware()->Session()->EasyCredit["authorized_amount"]);
        unset(Shopware()->Session()->EasyCredit["pre_contract_information_url"]);
        unset(Shopware()->Session()->EasyCredit["redemption_plan"]);

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


    public function showInterestRemovedError($view) {
        if (
            (isset(Shopware()->Session()->EasyCredit["info_interest_removed"]))
            && (Shopware()->Session()->EasyCredit["info_interest_removed"])
        ) {
            $this->setErrorMessages(
                $view,
                "EasyCredit Raten müssen neu berechnet werden. " .
                "Bitte wählen Sie erneut die Zahlungsart <strong>Ratenkauf by easyCredit</strong>."
            );
            Shopware()->Session()->EasyCredit["info_interest_removed"] = false;
        }
    }

    public function reloadCheckoutConfirm($action) {
        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'confirm'
        ));
    }

    public function checkInterest($action, $view) {

        $this->showInterestRemovedError($view);

        $user = $this->getUser();
        $payment = $user['additional']['payment'];
        $paymentName = $payment['name'];

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

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));

            return;
        } // still easycredit but amount has changed => remove interest and reset payment method to shop default

        if (
            $paymentName == 'easycredit'
            && $interestInBasket
            && $amount_authorized != $amount_basket_without_interest
        ) {

            $this->removeInterest();
            $this->resetPaymentIdToDefaultId();

            Shopware()->Session()->EasyCredit["info_interest_removed"] = true;

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));

            return;
        } // easycredit without interest rates should not be possible => remove it
        elseif (
            $paymentName == 'easycredit'
            && !$interestInBasket
        ) {
            $this->resetPaymentIdToDefaultId();
            $this->reloadCheckoutConfirm($action);

            Shopware()->Session()->EasyCredit["info_interest_removed"] = true;

            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));
        }

    }

    public function alterChangePaymentTemplate($action, $view) {
        $checkout = $action->get('easyCreditCheckout');

        $installementValues = $checkout->getInstallementValues(round($this->getAmount(), 2));

        // get the currently selected payment name from the view (not the one saved in the session)
        $selectedPaymentName = $view->sUserData['additional']['payment']['name'];

        if (
            !$installementValues['status']
            || $selectedPaymentName == 'easycredit'
        ) {
            $this->registerMyTemplateDir();
            $view->extendsTemplate('frontend/checkout/change_payment.tpl');
        }


        if (!$installementValues['status']) {

            $view->assign('EasyCreditPaymentDisabled', true);

            $descriptionDisabled = $this->getPaymentLogoDisabled()
                . '<br />' . $this->getAdditionalDescriptionText()
                . '<br />' . $installementValues['error'];

            $view->assign('EasyCreditDescriptionDisabled', $descriptionDisabled);

            return;

        }

        if ($selectedPaymentName == 'easycredit') {

            $view->assign('EasyCreditPaymentSelected', true);

            $descriptionSelected = $this->getPaymentLogo()
                . '<br />' . $this->getAdditionalDescriptionText();

            $view->assign('EasyCreditDescriptionSelected', $descriptionSelected);

            return;
        }
    }


    public function redirectToTeamBank($action) {
        Shopware()->Session()->EasyCredit["externRedirect"] = false;


        $action->redirect(array(
            'module' => 'payment_easycredit',
            'action' => 'gateway'
        ));
    }

    public function alterConfirmTemplate($action, $view) {
        $user = $this->getUser();
        $payment = $user['additional']['payment'];
        $paymentName = $payment['name'];

        if ($paymentName != 'easycredit') {
            return;
        }

        $this->registerMyTemplateDir();
        $view->extendsTemplate('frontend/checkout/confirm.tpl');

        $view->assign('EasyCreditPaymentShowRedemption', true);
        $view->assign('EasyCreditPaymentRedemptionPlan', Shopware()->Session()->EasyCredit["redemption_plan"]);
        $view->assign(
            'EasyCreditPaymentPreContractInformationUrl',
            Shopware()->Session()->EasyCredit["pre_contract_information_url"]
        );

    }

    public function onPostDispatch(\Enlight_Event_EventArgs $arguments)
    {

        $action = $arguments->getSubject();
        $request = $action->Request();

        $user = $this->getUser();
        $payment = $user['additional']['payment'];


        if (
            isset(Shopware()->Session()->EasyCredit["externRedirect"])
            && Shopware()->Session()->EasyCredit["externRedirect"]
        ) {
            $this->redirectToTeamBank($action);
            return;
        }

        if ($request->getControllerName() != 'checkout') {
            return;
        }

        $response = $action->Response();
        $view = $action->View();
        $config = $this->config;


        if ($request->getActionName() == "confirm") {
            $this->checkInterest($action, $view);
            $this->alterConfirmTemplate($action, $view);
        } // shippingPayment template adjustments
        else if ($request->getActionName() == "shippingPayment") {
            $this->alterChangePaymentTemplate($action, $view);
        }

    }

    public function onSaveShippingPayment(Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $response = $action->Response();


        if ($this->isInterestInBasket()) {
            $this->removeInterest();
        }

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            (int)$request->getPost('payment')
        );

        if ($payment['name'] != 'easycredit') {
            return;
        }

        if (false === $this->_handleRedirect($action, $request, $response)) {
            return false;
        }

    }

    protected function _handleRedirect($controller, $request, $response)
    {

        if (!$request->isPost() || $request->getParam('isXHR')) {
            return;
        }

        Shopware()->Session()->EasyCredit["externRedirect"] = true;

//        $controller->redirect(array(
//            'module' => 'payment_easycredit',
//            'action' => 'gateway'
//        ));
//
//
//        return false; // do not save shipping/payment
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

    private function getAdditionalDescriptionText() {
        return 'Ratenkauf by easyCredit.';
    }

    private function createMyPayment()
    {
        $this->createPayment(
            array(
                'name' => 'easycredit',
                'description' => 'easyCredit',
                'action' => 'payment_easycredit',
                'active' => 0,
                'position' => 0,
                'additionalDescription' => $this->getPaymentLogo() . $this->getAdditionalDescriptionText()
            )
        );
    }

    private function getPaymentLogo()
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return '<!-- easycredit Logo -->' .
        '<img id="rk_easycredit_logo" src="{link file=\'frontend/_resources/images/ratenkauf_easycredit_logo.png\' fullPath}" alt="easycredit Logo\'">' .
        '</a><br>' .
        '<!-- easycredit Logo -->';
    }

    private function getPaymentLogoDisabled() {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return '<!-- easycredit Logo -->' .
        '<img style="filter: grayscale(100%); -webkit-filter: grayscale(100%)" id="rk_easycredit_logo" src="{link file=\'frontend/_resources/images/ratenkauf_easycredit_logo.png\' fullPath}" alt="easycredit Logo\'">' .
        '</a><br>' .
        '<!-- easycredit Logo -->';
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

}
