<?php
namespace Shopware\Plugins\NetzkollektivEasycredit\Subscriber;

use Enlight\Event\SubscriberInterface;

class Frontend implements SubscriberInterface
{
    const INTEREST_REMOVED_ERROR = 'Raten müssen neu berechnet werden';
    const INTEREST_ORDERNUM = 'sw-payment-ec-interest';

    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout'                   => 'registerCheckoutController',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit'   => 'onGetControllerPathFrontend',
            'Enlight_Controller_Action_Frontend_Account_SavePayment'                    => 'onSavePayment',
            'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment'           => 'onSaveShippingPayment',
            'Enlight_Controller_Action_PostDispatch_Frontend_Account'                   => 'onFrontendAccountPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'            => 'onFrontendCheckoutPostDispatch',
        );
    }

    public function __construct(Bootstrap $bootstrap) {
        $this->bootstrap = $bootstrap;
    }

    public function registerCheckoutController(Enlight_Event_EventArgs $arguments) {
        if ($arguments->getSubject() instanceof Shopware_Controllers_Frontend_Checkout) {
            \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote::setCheckoutController($arguments->getSubject());
        }
    }

    public function onGetControllerPathFrontend() {
        $this->_registerTemplateDir();
        return $this->Path() . 'Controllers/Frontend/PaymentEasycredit.php';
    }

    /**
     * Handle redirect to payment terminal, Emotion Template only
     */
    public function onSavePayment(Enlight_Event_EventArgs $arguments) {
        if ($this->_isResponsive()) {
            return;
        } 

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

    /**
     * Handle redirect to payment terminal, Responsive only
     */
    public function onSaveShippingPayment(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->_isResponsive()) {
            return;
        }

        $action = $arguments->getSubject();
        $request = $action->Request();

        if (!$request->isPost()
            || $request->getParam('isXHR')
        ) {
            return;
        }

        return $this->_handleRedirect((int)$request->getPost('payment'), $action);
    }

    /**
     * Add legal text, agreement & amount logic to payment selection, Emotion only
     */
    public function onFrontendAccountPostDispatch(\Enlight_Event_EventArgs $arguments) {
        if ($this->_isResponsive()) {
            return;
        }

        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->_registerTemplateDir();

        switch ($request->getActionName()) {
            case 'payment':
                $this->_extendPaymentTemplate($action, $view);
                break;
        }
    }

    /**
     * Checkout related modifications, both templates
     */
    public function onFrontendCheckoutPostDispatch(\Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->_registerTemplateDir();

        if ($this->_checkRedirect($action)) {
            return;
        }

        switch ($request->getActionName()) {
            case 'confirm':
                if ($this->_checkInterest($action, $view)) {
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

/*            case 'cart':
                if ($error = $this->_displayError($action)) {
                    $view->sBasketInfo = $error;
                }
                break;
*/
            case 'shippingPayment': // Responsive Template only
                if (!$this->_isResponsive()) {
                    return;
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'finish':
                $this->_unsetStorage();
                $this->_resetPaymentToDefault();
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

    protected function _redirectToEasycredit($action) {
        try {
            $checkout = $this->bootstrap->get('easyCreditCheckout')
               ->setCancelUrl($this->_getUrl('cancel'))
               ->setReturnUrl($this->_getUrl('return'))
               ->setRejectUrl($this->_getUrl('reject'))
               ->start(new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote($action));

            if ($url = $checkout->getRedirectUrl()) {
                header('Location: '.$url);
                exit;
            }
        } catch (\Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->_redirectCheckoutConfirm($action);
            return;
        }
    }

    protected function _redirectCheckoutConfirm($action) {
        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'confirm'
        ));
    }

    protected function _extendPaymentTemplate($action, $view) {
        $checkout = $action->get('easyCreditCheckout');

        $error = false;
        try {
            $checkout->checkInstallmentValues(round($this->_getAmount(), 2));
        } catch(\Exception $e) {
            $error = $e->getMessage();
        }

        $view->assign('EasyCreditError', $error)
            ->assign('EasyCreditThemeVersion',Shopware()->Shop()->getTemplate()->getVersion())
            ->assign('EasyCreditPaymentCompanyName', Shopware()->Config()->get('easycreditCompanyName'));
    }

    protected function _onCheckoutConfirm($view) {
        $checkout = $this->bootstrap->get('easyCreditCheckout');

        try {
            $approved = $checkout->isApproved();
            if (!$approved) {
                throw new \Exception($this->getLabel().' wurde nicht genehmigt.');
            }

            $checkout->loadFinancingInformation();
        } catch (\Exception $e) {
            $view->sBasketInfo = Shopware()->Snippets()->getSnippet()->get(
                'CheckoutSelectPremiumVariant',
                $e->getMessage(),
                true
            );
        }
    }

    protected function _extendConfirmTemplate($view) {
        $user = $this->_getUser();

        $paymentName = $user['additional']['payment']['name'];

        if ($paymentName != 'easycredit') {
            return;
        }

        if (!$this->_isResponsive()) {
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

    protected function _handleRedirect($selectedPaymentId, $action, $queue = false) {

        if ($this->_isInterestInBasket()) {
            $this->_removeInterest();
        }

        $payment = Shopware()->Modules()->Admin()->sGetPaymentMeanById(
            $selectedPaymentId
        );

        if ($payment['name'] != 'easycredit') {
            return;
        }

        $amount_basket = round($this->_getAmount(), 2);
        $amount_authorized = round(Shopware()->Session()->EasyCredit["authorized_amount"], 2);
        $amount_interest = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);
        $amount_basket_without_interest = round($amount_basket - $amount_interest, 2);

        if (
            $this->_isInterestInBasket()
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

    protected function _registerTemplateDir() {

        $template = $this->bootstrap->get('template');
        $template->addTemplateDir($this->Path() . 'Views/');
        $template->addTemplateDir($this->Path() . 'Views/common/');

        if ($this->_isResponsive()) {
            $template->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $template->addTemplateDir($this->Path() . 'Views/emotion/');
        }
    }

    protected function _checkInterest($action, $view) {

        $paymentName = $this->_getSelectedPaymentMethod();

        $amount_basket = round($this->_getAmount(), 2);

        $amount_authorized = round(Shopware()->Session()->EasyCredit["authorized_amount"], 2);
        $amount_interest = round(Shopware()->Session()->EasyCredit["interest_amount"], 2);
        $amount_basket_without_interest = round($amount_basket - $amount_interest, 2);

        $interestInBasket = $this->_isInterestInBasket();

        if (
            $paymentName != 'easycredit'
            && $interestInBasket
        ) {
            $this->_debug('other payment method but interest in basket => remove interest');

            $this->_removeInterest();
            $this->_redirectWithError($action);
            return true;
        } 

        if (
            $paymentName == 'easycredit'
            && $interestInBasket
            && $amount_authorized != $amount_basket_without_interest
        ) {
            $this->_debug('easycredit selected, amount changed => remove interest, reset payment method to default');

            $this->_removeInterest();
            $this->_resetPaymentToDefault();

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = self::INTEREST_REMOVED_ERROR;
            }

            $this->_redirectWithError($action);
            return true;
        } elseif (
            $paymentName == 'easycredit'
            && !$interestInBasket
        ) {
            $this->_debug('easycredit without interest rates => reset payment method to default');

            $this->_resetPaymentToDefault();

            if (empty(Shopware()->Session()->EasyCredit["apiError"])) {
                Shopware()->Session()->EasyCredit["apiError"] = self::INTEREST_REMOVED_ERROR;
            }
            $this->_redirectWithError($action);
            return true;
        }

        return false;
    }

    protected function _redirectWithError($action) {
/*        return $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'cart'
        ));
*/
        if ($this->_isResponsive() && $action instanceof Shopware_Proxies_ShopwareControllersFrontendCheckoutProxy) {
            $payment = $action->getSelectedPayment();
            if((array_key_exists('validation', $payment) && !empty($payment['validation']))
                || isset(Shopware()->Session()->EasyCredit["apiError"])
                ) {
                return $action->redirect(array(
                    'controller' => 'checkout',
                    'action' => 'shippingPayment'
                ));
            }
        }
        return $this->_redirectCheckoutConfirm($action);
    }

    protected function _resetPaymentToDefault($paymentId = null) {
        $user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);

        if ($paymentId === null)
            $paymentId = Shopware()->Config()->get('paymentdefault');

        $user->setPaymentId($paymentId);
        Shopware()->Models()->persist($user);
        Shopware()->Models()->flush();
        Shopware()->Models()->refresh($user);
    }

    /**
     * remove interest from basket via DB call
     */
    protected function _removeInterest()
    {
        $session = Shopware()->Session();

        Shopware()->Db()->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $session->get('sessionId'),
                'ordernumber = ?' => self::INTEREST_ORDERNUM
            )
        );

        $this->_unsetStorage();
    }

    protected function _displayError($action) {
        $error = false;
        if (!empty(Shopware()->Session()->EasyCredit["apiError"]) && !$action->Response()->isRedirect()) {
            $error = Shopware()->Snippets()->getSnippet()->get(
                'CheckoutSelectPremiumVariant',
                Shopware()->Session()->EasyCredit["apiError"].' Bitte wählen Sie erneut <strong>'.$this->bootstrap->getLabel().'</strong> in der Zahlartenauswahl.',
                true
            );
            Shopware()->Session()->EasyCredit["apiError"] = null;
        }
        return $error;
    }

    /**
     * checks if the easycredit interest is in the user's basket
     *
     * @return bool
     */
    protected function _isInterestInBasket()
    {
        $basket = $this->_getBasket();

        $content = $basket['content'];
        foreach ($content as $c) {
            if (isset($c['ordernumber']) && $c['ordernumber'] == self::INTEREST_ORDERNUM) {
                return true;
            }
        }
        return false;
    }

    protected function _unsetStorage() {
        unset(Shopware()->Session()->EasyCredit["interest_amount"]);
        unset(Shopware()->Session()->EasyCredit["authorized_amount"]);
        unset(Shopware()->Session()->EasyCredit["pre_contract_information_url"]);
        unset(Shopware()->Session()->EasyCredit["redemption_plan"]);
        unset(Shopware()->Session()->EasyCredit["transaction_id"]);
    }

    protected function _getSelectedPaymentMethod() {
        $user = $this->_getUser();
        $payment = $user['additional']['payment'];
        $paymentName = $payment['name'];
        return $paymentName;
    }

    protected function _getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    protected function _getAmount(){
        $quote = new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote();
        return $quote->getGrandTotal();
    }

    protected function _getBasket()
    {
        return Shopware()->Modules()->Basket()->sGetBasket();
    }

    protected function _getUrl($action) {
        return Shopware()->Front()->Router()->assemble(array(
            'controller' => 'payment_easycredit',
            'action'    => $action,
        ));
    }
    
    public function Path() {
        return $this->bootstrap->Path();
    }

    protected function _isResponsive() {
        return Shopware()->Shop()->getTemplate()->getVersion() >= 3;
    }

    protected function _debug($msg) {
        file_put_contents('/shopware/debug.log',$msg.PHP_EOL,FILE_APPEND);
    }
}
