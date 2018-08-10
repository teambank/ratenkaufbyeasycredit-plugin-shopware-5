<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Shopware\Plugins\NetzkollektivEasyCredit\Api;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
            'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment'           => 'onSaveShippingPayment',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'            => 'onFrontendCheckoutPostDispatch',
            'Shopware_Modules_Order_SaveOrder_FilterParams'                             => 'setEasycreditOrderStatus',
            'Enlight_Controller_Action_PostDispatch_Frontend'                           => 'addEasyCreditModelWidget',
            'Theme_Compiler_Collect_Plugin_Javascript'                                  => 'addJsFiles',
            'Theme_Compiler_Collect_Plugin_Css'                                         => 'addCssFiles',
            'sBasket::sInsertSurchargePercent::replace'                                 => 'sInsertSurchargePercent'
        );
    }


    public function sInsertSurchargePercent(\Enlight_Hook_HookArgs $arguments) {

        // remove interest from basket, so that the surcharge is not calculated based on amount including interest

        $this->getPlugin()->removeInterest(false);

        $arguments->setReturn(
            $arguments->getSubject()->executeParent(
                $arguments->getMethod(),
                $arguments->getArgs()
            )
        );

        // readd the interest, so that it is shown to the user

        $this->getPlugin()->addInterest(false);
        
    }

    public function addJsFiles() {
        $jsDir = $this->Path() . '/Views/frontend/_public/src/js/';
        return new ArrayCollection(array(
            $jsDir . 'easycredit-widget.js',
            $jsDir . 'easycredit.js'
        ));
    }

    public function addCssFiles(\Enlight_Event_EventArgs $args) {
        return new ArrayCollection(array(
            $this->Path() . '/Views/frontend/_public/src/css/easycredit-widget.css',
            $this->Path() . '/Views/frontend/_public/src/css/easycredit.css'
        ));
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

        $this->extendIndexTemplate($view, $config);
    }

    public function extendIndexTemplate($view, $config) {
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
     * Handle redirect to payment terminal, Responsive only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onSaveShippingPayment(\Enlight_Event_EventArgs $arguments)
    {
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
                if ($error = $this->_displayError($action)) {
                    $view->sBasketInfo = $error;
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'finish':
                $this->getPlugin()->clear();
        }
    }

    protected function _redirectToEasycredit($action) {
        try {
            $checkout = $this->getPlugin()->get('easyCreditCheckout')->start(
                new Api\Quote(),
                $this->_getUrl('cancel'),
                $this->_getUrl('return'),
                $this->_getUrl('reject')
            );
 
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


    protected function _getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return Shopware()->Modules()->Admin()->sGetUserData();
        }
    }

    protected function _extendPaymentTemplate($action, $view) {
        $checkout = $action->get('easyCreditCheckout');

        $error = false;

        try {
            $checkout->isAvailable(new Api\Quote());
        } catch(\Exception $e) {
            $error = $e->getMessage();
        }

        $agreement = '';
        if (!$error) {
            try {
                $agreement = $checkout->getAgreement();
            } catch (\Exception $e) { }
        }

        if ($error && $error === 'Der Webshop existiert nicht.') {
            $error = 'ratenkauf by easyCredit zur Zeit nicht verfügbar.';
        }

        $view->assign('EasyCreditError', $error)
            ->assign('EasyCreditAgreement', $agreement);
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
        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'shippingPayment'
        ));
    }

    protected function _extendConfirmTemplate($view) {
        $view->assign('EasyCreditPaymentShowRedemption', true)
            ->assign('EasyCreditPaymentRedemptionPlan', Shopware()->Session()->EasyCredit["redemption_plan"])
            ->assign(
                'EasyCreditPaymentPreContractInformationUrl',
                Shopware()->Session()->EasyCredit["pre_contract_information_url"]
            )->assign('EasyCreditPaymentPlan',Shopware()->Session()->EasyCredit["payment_plan"]);
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
        $quote = new Api\Quote();
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
