<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

class FrontendEmotion extends Frontend
{
    /**
     * Frontend constructor.
     *
     * @param \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap $bootstrap
     */
    public function __construct(\Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->config = $bootstrap->Config();
        $this->db = Shopware()->Db();
        $this->container = Shopware()->Container();
        $this->helper = new \EasyCredit_Helper();
    }

    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Action_Frontend_Account_SavePayment'                    => 'onSavePayment',
            'Enlight_Controller_Action_PostDispatch_Frontend_Account'                   => 'onAccountPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'            => 'onCheckoutPostDispatch',
        );
    }

    /**
     * Handle redirect to payment terminal
     * Emotion only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onSavePayment($arguments) {
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

        $ecParam = $request->getParam('easycredit');
        if (!isset($ecParam['submit'])) {
            return;
        }

        if ($request->getParam('sTarget') !== 'checkout') {
            return;
        }
        return $this->_handleRedirect($action);
    }

    /**
     * Add legal text, agreement & amount logic to payment selection
     * Emotion only
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onAccountPostDispatch($arguments) {
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
     * Checkout related modifications
     * Emotion
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onCheckoutPostDispatch($arguments) {
        $action = $arguments->getSubject();
        $request = $action->Request();
        $view = $action->View();

        $this->_registerTemplateDir();

        switch ($request->getActionName()) {
            case 'confirm':
                if (!$this->helper->getPlugin()->isSelected()) {
                    return;
                }

                $this->_extendPaymentTemplate($action, $view);
                $this->onCheckoutConfirm($action, $view);
                $this->_extendConfirmTemplate($view);
                break;

            case 'shippingPayment':
                if ($error = $this->_displayError($action)) {
                    $view->assign('sBasketInfo', $error);
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'cart':
                if ($error = $this->_displayError($action)) {
                    $view->assign('sBasketInfo', $error);
                }
                break;

            case 'finish':
                $this->helper->getPlugin()->clear();
        }
    }

    protected function onCheckoutConfirm($action, $view) {
        if (!is_null($this->helper->getPluginSession()["apiError"])
            || !is_null($this->helper->getPluginSession()["addressError"])
        ) {
            $error = $view->getAssign('sBasketInfo');
            if ($this->helper->getPluginSession()["apiError"]) {
                $error = $this->helper->getPluginSession()["apiError"];
            }
            if ($this->helper->getPluginSession()["addressError"]) {
                $error = $this->helper->getPluginSession()["addressError"];
            }
            $view->assign('sBasketInfo', $error);
            $this->helper->getPlugin()->clear();
            return;
        }

        $checkout = $this->container->get('easyCreditCheckout');

        if (!$checkout->isInitialized()) {
            $this->helper->getPlugin()->getStorage()->set('apiError', $this->helper->getPlugin()->getLabel(). ' wurde nicht initialisiert.');
            return $this->_redirToPaymentSelection($action);
        }

        $ecQuote = $this->helper->getPlugin()->getQuote();
        if (!$checkout->isAmountValid($ecQuote)) {
            try {
                $checkout->update($ecQuote);
            } catch (\Exception $e) {}
        }

        if (!$this->helper->getPlugin()->isValid()) {
            $this->helper->getPlugin()->getStorage()->set('apiError', self::INTEREST_REMOVED_ERROR);
            return $this->_redirToPaymentSelection($action);
        }

        try { 
            $approved = $checkout->isApproved();
            if (!$approved) {
                throw new \Exception($this->helper->getPlugin()->getLabel().' wurde nicht genehmigt.');
            }

            $checkout->loadTransaction();
        } catch (\Exception $e) {
            $this->helper->getPlugin()->getStorage()->set('apiError', $e->getMessage());
            return $this->_redirToPaymentSelection($action);
        }
    }
}