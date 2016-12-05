<?php
namespace Shopware\Plugins\NetzkollektivEasycredit\Subscriber;

use Enlight\Event\SubscriberInterface;

class Frontend implements SubscriberInterface
{
    const INTEREST_REMOVED_ERROR = 'Raten müssen neu berechnet werden';
    const INTEREST_ORDERNUM = 'sw-payment-ec-interest';

    protected $bootstrap;

    /**
     * @var \Enlight_Config $config
     */
    protected $config;

    /**
     * Frontend constructor.
     *
     * @param \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap $bootstrap
     */
    public function __construct(\Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->config = $bootstrap->Config();
    }

    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit'   => 'onGetControllerPathFrontend',
            'Enlight_Controller_Action_Frontend_Account_SavePayment'                    => 'onSavePayment',
            'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment'           => 'onSaveShippingPayment',
            'Enlight_Controller_Action_PostDispatch_Frontend_Account'                   => 'onFrontendAccountPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'            => 'onFrontendCheckoutPostDispatch',
            'Shopware_Modules_Order_SaveOrder_FilterParams'                             => 'setEasycreditOrderStatus',
            'Enlight_Controller_Action_PostDispatch_Frontend'                           => 'addEasyCreditModelWidget'
        );
    }

    public function onGetControllerPathFrontend() {
        $this->_registerTemplateDir();
        return $this->Path() . 'Controllers/Frontend/PaymentEasycredit.php';
    }

    public function addEasyCreditModelWidget(\Enlight_Event_EventArgs $arguments) {
        /** @var $action \Enlight_Controller_Action */
        $action = $arguments->getSubject();
        $request = $action->Request();
        $response = $action->Response();
        $view = $action->View();
        $config = $this->config;
        $widgetActive = $config->get('easycreditModelWidget');

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
            || !$view->hasTemplate()
            || !$widgetActive
            || $request->getControllerName() !== 'detail'
        ) {
            return;
        }

        $this->_registerTemplateDir();

        $view->assign('EasyCreditApiKey', $config->get('easycreditApiKey'));
    }

    public function setEasycreditOrderStatus(\Enlight_Event_EventArgs $arguments) {
        $orderParams = $arguments->getReturn();
        $newOrderState = $this->config->get('easycreditOrderStatus');

        // use default shopware order state
        if ($newOrderState === null || $newOrderState === -1 || !is_numeric($newOrderState)) {
            return $orderParams;
        }

        $paymentID = $orderParams['paymentID'];

        /** @var $payment \Shopware\Models\Payment\Payment */
        $payment = Shopware()->Models()->find('Shopware\Models\Payment\Payment', $paymentID);

        if ($payment) {
            $paymentName = $payment->getName();

            if ($paymentName === \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap::PAYMENT_NAME) {
                $orderParams['status'] = $newOrderState;
            }
        }

        return $orderParams;
    }


    /**
     * Handle redirect to payment terminal, Emotion Template only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onSavePayment(\Enlight_Event_EventArgs $arguments) {
        if ($this->getPlugin()->isResponsive()) {
            return;
        } 

        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();
        if (!$request->isPost()) {
            return;
        }

        $values = $request->getPost('register');
        $paymentId = $values['payment'];

        if (!$this->getPlugin()->isSelected($paymentId)) {
            $this->getPlugin()->clear();
            return;
        }

        $agreementChecked = $request->getParam('sEasycreditAgreement');
        if (empty($agreementChecked)) {
            return;
        }

        if ($request->getParam('sTarget') !== 'checkout') {
            return;
        }
        return $this->_handleRedirect($paymentId, $action);
    }

    /**
     * Handle redirect to payment terminal, Responsive only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onSaveShippingPayment(\Enlight_Event_EventArgs $arguments)
    {
        if (!$this->getPlugin()->isResponsive()) {
            return;
        }

        $action = $arguments->getSubject();
        $request = $action->Request();

        $paymentId = (int)$request->getPost('payment');

        if (!$this->getPlugin()->isSelected($paymentId)) {
            $this->getPlugin()->clear();
            return;
        }

        if (!$request->isPost()
            || $request->getParam('isXHR')
        ) {
            return;
        }
        return $this->_handleRedirect($paymentId, $action);
    }

    /**
     * Add legal text, agreement & amount logic to payment selection, Emotion only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onFrontendAccountPostDispatch(\Enlight_Event_EventArgs $arguments) {
        if ($this->getPlugin()->isResponsive()) {
            return;
        }

        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->_registerTemplateDir();

        switch ($request->getActionName()) {
            case 'payment':

                if ($error = $this->_displayError($action)) {
                    $view->sErrorFlag = 1;
                    $view->sErrorMessages = $error;
                }
                $this->_extendPaymentTemplate($action, $view);
                break;
        }
    }

    /**
     * Checkout related modifications, both templates
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onFrontendCheckoutPostDispatch(\Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->_registerTemplateDir();

        switch ($request->getActionName()) {
            case 'confirm':
                if (!$this->getPlugin()->isSelected()) {
                    return;
                }

                $this->_onCheckoutConfirm($view, $action);
                $this->_extendConfirmTemplate($view);
                break;

            case 'shippingPayment':
                if (!$this->getPlugin()->isResponsive()) {
                    return;
                }

                if ($error = $this->_displayError($action)) {
                    $view->sBasketInfo = $error;
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'finish':
                $this->getPlugin()->unsetStorage();
        }
    }

    protected function _redirectToEasycredit($action) {
        try {
            $checkout = $this->getPlugin()->get('easyCreditCheckout')
               ->setCancelUrl($this->_getUrl('cancel'))
               ->setReturnUrl($this->_getUrl('return'))
               ->setRejectUrl($this->_getUrl('reject'))
               ->start(new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote());
            $this->getPlugin()->saveAddress();
 
            if ($url = $checkout->getRedirectUrl()) {
                header('Location: '.$url);
                exit;
            }
        } catch (\Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'confirm'
            ));
            return;
        }
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
            ->assign('EasyCreditAgreement', $checkout->getAgreement());
    }

    protected function _onCheckoutConfirm($view, $action) {
        if (!is_null(Shopware()->Session()->EasyCredit["apiError"])) {
            return $this->_redirToPaymentSelection($action);
        }

        $checkout = $this->getPlugin()->get('easyCreditCheckout');

        if (!$checkout->isInitialized()) {
            return $this->_redirToPaymentSelection($action);
        }

        if (!$this->getPlugin()->isValid()) {
            Shopware()->Session()->EasyCredit["apiError"] = self::INTEREST_REMOVED_ERROR;
            return $this->_redirToPaymentSelection($action);
        }
        
        try {
            $approved = $checkout->isApproved();
            if (!$approved) {
                throw new \Exception($this->getPlugin()->getLabel().' wurde nicht genehmigt.');
            }

            $checkout->loadFinancingInformation();
        } catch (\Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            return $this->_redirToPaymentSelection($action);
        }
    }

    protected function _redirToPaymentSelection($action) {
        if ($this->getPlugin()->isResponsive()) {
            $action->redirect(array(
                'controller' => 'checkout',
                'action' => 'shippingPayment'
            ));
        } else {
            $action->redirect(array(
                'controller' => 'account',
                'action' => 'payment',
                'sTarget'=>'checkout'
            ));
        }
    }

    protected function _extendConfirmTemplate($view) {
        if (!$this->getPlugin()->isResponsive()) {
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
    
    public function getPlugin() {
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit();
    }

    protected function _handleRedirect($selectedPaymentId, $action) {

        if ($this->getPlugin()->isInterestInBasket() !== false) {
            $this->getPlugin()->clear();
        }
        if (!$this->getPlugin()->isSelected($selectedPaymentId)) {
            return;
        }
        if ($this->getPlugin()->isValid()) {
            return;
        }
        $this->_redirectToEasycredit($action);
    }

    protected function _registerTemplateDir() {

        $template = $this->getPlugin()->get('template');
        $template->addTemplateDir($this->Path() . 'Views/');
        $template->addTemplateDir($this->Path() . 'Views/common/');

        if ($this->getPlugin()->isResponsive()) {
            $template->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $template->addTemplateDir($this->Path() . 'Views/emotion/');
        }
    }

    protected function _displayError($action) {
        $error = false;
        if (!empty(Shopware()->Session()->EasyCredit["apiError"]) && !$action->Response()->isRedirect()) {
            $apiError = Shopware()->Session()->EasyCredit["apiError"];    
            $apiError = trim($apiError);
            if (!in_array(substr($apiError, -1),array('.','!','?'))) {
                $apiError.='.';
            }

            $error = Shopware()->Snippets()->getSnippet()->get(
                'CheckoutSelectPremiumVariant',
                $apiError.' Bitte wählen Sie erneut <strong>'.$this->getPlugin()->getLabel().'</strong> in der Zahlartenauswahl.',
                true
            );
            Shopware()->Session()->EasyCredit["apiError"] = null;
            $this->getPlugin()->clear();
        }
        return $error;
    }

    protected function _getAmount(){
        $quote = new \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote();
        return $quote->getGrandTotal();
    }

    protected function _getUrl($action) {
        return Shopware()->Front()->Router()->assemble(array(
            'controller' => 'payment_easycredit',
            'action'    => $action,
        ));
    }
    
    public function Path() {
        return $this->getPlugin()->Path();
    }
}
