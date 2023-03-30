<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Shopware\Plugins\NetzkollektivEasyCredit\Api;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\ConstraintViolation;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\AddressValidationException;

use GuzzleHttp\Exception\ConnectException;
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

    protected $db;

    protected $container;

    protected $helper;

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
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEasycredit'   => 'onGetControllerPathFrontend',
            'Enlight_Controller_Action_Frontend_Checkout_SaveShippingPayment'           => 'onSaveShippingPayment',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_PaymentEasycredit'   => 'onExpressCheckoutStart',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'            => 'onFrontendCheckoutPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Address'             => 'onFrontendAddressPostDispatch',
            'Shopware_Modules_Order_SaveOrder_FilterParams'                             => 'setEasycreditOrderStatus',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend'                     => 'addEasyCreditModelWidget',
            'sBasket::sInsertSurchargePercent::replace'                                 => 'sInsertSurchargePercent',
            'Shopware_Modules_Order_SendMail_BeforeSend'                                => 'onOrderSave'
        );
    }

    public function sInsertSurchargePercent(\Enlight_Hook_HookArgs $arguments) {

        // remove interest from basket, so that the surcharge is not calculated based on amount including interest

        $this->helper->getPlugin()->removeInterest(false);

        $arguments->setReturn(
            $arguments->getSubject()->executeParent(
                $arguments->getMethod(),
                $arguments->getArgs()
            )
        );

        // readd the interest, so that it is shown to the user

        $this->helper->getPlugin()->addInterest(false);
        
    }

    public function onOrderSave(\Enlight_Event_EventArgs $args) {

        $orderVariables = $args->get('variables');
        if (!isset($orderVariables['additional']['payment']['name'])
            || $orderVariables['additional']['payment']['name'] != 'easycredit'
        ) {
            return;
        }

        $context = $args->get('context');
        $orderNumber = $context['sOrderNumber'];
        if (empty($orderNumber)) {
            return;
        }

        $this->updateInterestModus($orderNumber);
        $this->removeInterestFromOrder($orderNumber);
    }

    protected function updateInterestModus($orderNumber) {

        // update interest modus to "premium article" for correct tax handling (otherwise shopware overwrites tax)
        $this->db->query("UPDATE s_order_details od
            INNER JOIN s_order o ON od.orderID = o.id
            Set od.modus = 1
            WHERE o.ordernumber = ?
            AND od.articleordernumber = ?;
        ", [$orderNumber, self::INTEREST_ORDERNUM]);
    }

    protected function removeInterestFromOrder($orderNumber) {
        if (!$this->config->get('easycreditRemoveInterestFromOrder')) {
            return;
        }

        try {
            $this->db->beginTransaction();

            // subtract interest from total amount
            $this->db->query("UPDATE s_order o
                INNER JOIN s_order_details od ON od.orderID = o.id AND articleordernumber = ?
                SET
                    o.invoice_amount = ROUND(o.invoice_amount - od.price,2),
                    o.invoice_amount_net = ROUND(o.invoice_amount_net - od.price,2)
                WHERE o.ordernumber = ?", [self::INTEREST_ORDERNUM, $orderNumber]
            );

            // remove interest position
            $this->db->query("DELETE s_order_details
                FROM s_order_details
                INNER JOIN s_order o ON s_order_details.orderID = o.id
                WHERE o.ordernumber = ?
                AND s_order_details.articleordernumber = ?;
            ", [$orderNumber, self::INTEREST_ORDERNUM]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Enlight_Exception("Removal of interest failed:" . $e->getMessage(), 0, $e);
        }

    }

    public function onGetControllerPathFrontend() {
        $this->_registerTemplateDir();
        return $this->helper->getPlugin()->Path() . 'Controllers/Frontend/PaymentEasycredit.php';
    }

    public function addEasyCreditModelWidget(\Enlight_Event_EventArgs $arguments) {
        $action = $arguments->getSubject();

        $request = $action->Request();
        $response = $action->Response();
        $view = $action->View();

        $this->_registerTemplateDir();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $sAdmin = $this->helper->getModule('Admin');
        $sBasket = $this->helper->getModule('Basket');

        $paymentId = $this->helper->getPayment()->getId();

        $apiKey = $this->config->get('easycreditApiKey');
        if ($apiKey) {
            $view->assign('EasyCreditApiKey', $apiKey);
        }

        $isEasyCreditAllowed = !$sAdmin->sManageRisks($paymentId, $sBasket->sGetBasket(), $sAdmin->sGetUserData() ?: []);

        if ($apiKey && $isEasyCreditAllowed) {
            $modalIsOpen = 'true';
            if ( $this->config->get('easyCreditMarketingModalSettingsDelay') ) {
                if ( $this->config->get('easyCreditMarketingModalSettingsDelay') > 0 ) {
                    $modalIsOpen = 'false';
                }
            }

            // Express Checkout
            $view->assign('EasyCreditExpressProduct', $this->config->get('easyCreditExpressProduct'));
            $view->assign('EasyCreditExpressCart', $this->config->get('easyCreditExpressCart'));
            // Widget
            $view->assign('EasyCreditWidget', $this->config->get('easycreditModelWidget'));
            // Marketing - Modal
            $view->assign('EasyCreditMarketingModal', $this->config->get('easyCreditMarketingModal'));
            $view->assign('EasyCreditMarketingModalIsOpen', $modalIsOpen);
            $view->assign('EasyCreditMarketingModalSettingsSnoozeFor', $this->config->get('easyCreditMarketingModalSettingsSnoozeFor'));
            $view->assign('EasyCreditMarketingModalSettingsDelay', $this->config->get('easyCreditMarketingModalSettingsDelay') * 1000);
            $view->assign('EasyCreditMarketingModalSettingsMedia', $this->config->get('easyCreditMarketingModalSettingsMedia'));
            // Marketing - Card
            $view->assign('EasyCreditMarketingCard', $this->config->get('easyCreditMarketingCard'));
            $view->assign('EasyCreditMarketingCardSearch', $this->config->get('easyCreditMarketingCardSearch'));
            $view->assign('EasyCreditMarketingCardSettingsMedia', $this->config->get('easyCreditMarketingCardSettingsMedia'));
            $view->assign('EasyCreditMarketingCardSettingsPosition', $this->config->get('easyCreditMarketingCardSettingsPosition'));
            // Marketing - Flashbox
            $view->assign('EasyCreditMarketingFlashbox', $this->config->get('easyCreditMarketingFlashbox'));
            $view->assign('EasyCreditMarketingFlashboxSettingsMedia', $this->config->get('easyCreditMarketingFlashboxSettingsMedia'));
            // Marketing - Bar
            $view->assign('EasyCreditMarketingBar', $this->config->get('easyCreditMarketingBar'));
        }
    }

    public function setEasycreditOrderStatus(\Enlight_Event_EventArgs $arguments) {
        $orderParams = $arguments->getReturn();
        $newOrderState = $this->config->get('easycreditOrderStatus');

        // use default shopware order state
        if ($newOrderState === null || $newOrderState === -1 || !is_numeric($newOrderState)) {
            return $orderParams;
        }

        $paymentID = $orderParams['paymentID'];

        if (empty($paymentID)) {
            return $orderParams;
        }

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
     */
    public function onSaveShippingPayment(\Enlight_Event_EventArgs $arguments)
    {
        $action = $arguments->getSubject();
        $request = $action->Request();

        $paymentId = (int)$request->getPost('payment');

        if (!$this->helper->getPlugin()->isSelected($paymentId)) {
            $this->helper->getPlugin()->clear();
            return;
        }

        if (!$params = $request->getParam('easycredit')) {
            return;
        }

        if (!$request->isPost()
            || $request->getParam('isXHR')
        ) {
            return;
        }

        if (isset($params['number-of-installments']) && $params['number-of-installments'] > 0) {
            $this->helper->getPlugin()
                ->getStorage()
                ->set('duration', $params['number-of-installments']);
        }
        $this->helper->getPlugin()->getStorage()->set('express', false);

        return $this->_handleRedirect($action);
    }

    public function onExpressCheckoutStart(\Enlight_Event_EventArgs $arguments) {
        $action = $arguments->getSubject();
        $request = $action->Request();

        if ($request->getActionName() !== 'express') {
            return;
        }

        if ($this->helper->getPlugin()->isInterestInBasket() !== false) {
            $this->helper->getPlugin()->clear();
        }

        if ($url = $this->getRedirectUrl()) {
            return $action->redirect($url);
        }
        return $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'cart'
        ));
    }

    protected function _handleRedirect($action) {
        if ($this->helper->getPlugin()->isInterestInBasket() !== false) {
            $this->helper->getPlugin()->clear();
        }
        if ($this->helper->getPlugin()->isValid()) {
            return;
        }

        try {
            $checkout = $this->container->get('easyCreditCheckout');
            $checkout->isAvailable($this->helper->getPlugin()->getQuote());
        } catch (AddressValidationException $e) {
            $this->helper->getPlugin()->getStorage()->set('addressError', $e->getMessage());
            return;
        } catch (\Exception $e) {
            $this->helper->getPlugin()->getStorage()->set('apiError', $e->getMessage());
            return;
        }

        if ($url = $this->getRedirectUrl()) {
            header('Location: '.$url);
            exit;
        }

        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'confirm'
        ));
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

        switch ($request->getActionName()) {
            case 'confirm':
                if (!$this->helper->getPlugin()->isSelected()) {
                    return;
                }

                $this->_onCheckoutConfirm($view, $action);
                $this->_extendConfirmTemplate($view);
                break;

            case 'cart':
                if ($error = $this->_displayError($action)) {
                    $view->assign('sBasketInfo', $error);
                }
                break;

            case 'shippingPayment':
                if ($error = $this->_displayError($action)) {
                    $view->assign('sBasketInfo', $error);
                }

                $this->_extendPaymentTemplate($action, $view);
                break;

            case 'finish':
                $this->helper->getPlugin()->clear();
        }
    }

    protected function getRedirectUrl() {
        try {
            try {
                $checkout = $this->container->get('easyCreditCheckout')->start(
                    $this->helper->getPlugin()->getQuote(),
                    $this->_getUrl('cancel'),
                    $this->_getUrl('return'),
                    $this->_getUrl('reject')
                );

                return $checkout->getRedirectUrl();
            } catch (ConnectException $e) {
                $this->container->get('pluginlogger')->error($e->getMessage());
                $this->helper->getPlugin()->getStorage()
                    ->set('apiError', 'easyCredit-Ratenkauf ist im Moment nicht verfügbar. Bitte probieren Sie es erneut oder wählen Sie eine andere Zahlungsart.')
                    ->set('apiErrorSkipSuffix', true);

                return false;
            } catch (ApiException $e) {
                if ($e->getResponseObject() instanceof ConstraintViolation) {
                    $errors = [];
                    foreach ($e->getResponseObject()->getViolations() as $violation) {
                        $errors[$violation['field']] = $violation['message'];
                    }
                    if (in_array('orderDetails.invoiceAddress', array_keys($errors)) ||
                        in_array('orderDetails.shippingAddress', array_keys($errors))
                    ) {
                        throw new AddressValidationException(implode(' ', $errors));
                    }

                    $this->helper->getPlugin()->getStorage()->set('apiError', implode(' ', $errors));
                    return false;
                }
                throw $e;
            }
        }  catch (AddressValidationException $e) {
            $this->helper->getPlugin()->getStorage()->set('addressError', $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->helper->getPlugin()->getStorage()->set('apiError', $e->getMessage());
            return false;
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

    protected function _getActiveBillingAddressId() {
        $activeBillingAddressId = Shopware()->Session()->offsetGet('checkoutBillingAddressId');
        if (empty($activeBillingAddressId)) {
            $user = $this->_getUser();
            $activeBillingAddressId = (is_array($user) && isset($user['additional']['user']['default_billing_address_id'])) ? $user['additional']['user']['default_billing_address_id'] : '';
        }
        return $activeBillingAddressId; 
    }

    protected function _extendPaymentTemplate($action, $view) {
        $checkout = $this->container->get('easyCreditCheckout');
        $error = false;

        try {
            $this->helper->getPlugin()->getStorage()->set('express', false);
            $checkout->isAvailable(
                $this->helper->getPlugin()->getQuote()
            );
        } catch (AddressValidationException $e) {
            $this->helper->getPlugin()->getStorage()->set('addressError', $e->getMessage());
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $view->assign('EasyCreditApiKey', $this->config->get('easycreditApiKey'));
        $view->assign('EasyCreditError', $error);
        $view->assign('EasyCreditAmount', $this->helper->getPlugin()->getQuote()->getOrderDetails()->getOrderValue());

        $isSelected = $this->helper->getPlugin()->isSelected($view->sUserData['additional']['user']['paymentID']);
        $view->assign('EasyCreditIsSelected', ($isSelected) ? 'true' : 'false');
        $view->assign('EasyCreditPaymentPlan', $this->getPaymentPlan());

        if (!$error && $isSelected) {
            if (isset($this->helper->getPluginSession()["addressError"])
                && $this->helper->getPluginSession()["addressError"]
            ) {
                $view->assign('EasyCreditAddressError',$this->helper->getPluginSession()["addressError"]);
                $view->assign('EasyCreditActiveBillingAddressId', $this->_getActiveBillingAddressId());
            }
        }
    }

    public function onFrontendAddressPostDispatch(\Enlight_Event_EventArgs $args) {
        $action = $args->getSubject();
        $request = $action->Request();
        $view = $action->View();

        if (!$this->helper->getPlugin()->isSelected($view->getAssign('sUserData')['additional']['user']['paymentID'])) {
            return;
        }

        $actionPath = implode('/',array($request->getModuleName(),$request->getControllerName(),$request->getActionName())); 

        if ($actionPath == 'frontend/address/ajaxEditor'
            && isset($this->helper->getPluginSession()["addressError"])
            && !empty($this->helper->getPluginSession()["addressError"])
        ) {
            $this->_registerTemplateDir('ViewsAddressError');

            $errors = $view->getAssign('error_messages');
            $errors[] = $this->helper->getPluginSession()["addressError"]; 
            $view->assign('error_messages', $errors);
        }

        if ($actionPath == 'frontend/address/ajaxSave'
            && isset($this->helper->getPluginSession()["addressError"])
        ) {
            $response = json_decode($action->Response()->getBody());

            if (isset($response->success) && $response->success) {
                $data = $this->helper->getPluginSession();
                if (isset($data['addressError'])) {
                    unset($data['addressError']);
                }
                Shopware()->Session()->offsetSet('EasyCredit', $data);

                $data = Shopware()->Session()->get('sOrderVariables')['sUserData'];
                if (isset($data['sUserData'])) {
                    unset($data['sUserData']);
                }
                Shopware()->Session()->offsetSet('sOrderVariables', $data);
            } 
        }
    }

    protected function _onCheckoutConfirm($view, $action) {
        if (!is_null($this->helper->getPluginSession()["apiError"])
            || !is_null($this->helper->getPluginSession()["addressError"])
        ) {
            return $this->_redirToPaymentSelection($action);
        }

        $checkout = $this->container->get('easyCreditCheckout');

        if (!$checkout->isInitialized()) {
            return $this->_redirToPaymentSelection($action);
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

    protected function _redirToPaymentSelection($action) {
        $action->redirect(array(
            'controller' => 'checkout',
            'action' => 'shippingPayment'
        ));
    }

    protected function getPaymentPlan() {
        $summary = json_decode($this->helper->getPlugin()->getStorage()->get('summary'));
        if ($summary === false || $summary === null) {
            return null;
        }
        return htmlspecialchars(json_encode($summary));
    }

    protected function _extendConfirmTemplate($view) {
        $view
            ->assign('EasyCreditPaymentPlan', $this->getPaymentPlan())
            ->assign('EasyCreditDisableAddressChange', true);
    }
    
    protected function _registerTemplateDir($viewDir = 'Views') {
        $template = $this->container->get('template');
        $template->addTemplateDir($this->helper->getPlugin()->Path() . $viewDir.'/');
    }

    protected function _displayError($action) {
        $error = false;
        if (!empty($this->helper->getPluginSession()["apiError"]) && !$action->Response()->isRedirect()) {
            $apiError = $this->helper->getPluginSession()["apiError"];    
            $apiError = trim($apiError);
            if (!in_array(substr($apiError, -1),array('.','!','?'))) {
                $apiError.='.';
            }
            $error = $apiError;
            $this->helper->getPlugin()->getStorage()->set('apiError', null);

            if (!isset($this->helper->getPluginSession()["apiErrorSkipSuffix"])) {
                $error.= ' Bitte wählen Sie erneut <strong>'.$this->helper->getPlugin()->getLabel().'</strong> in der Zahlartenauswahl.';
            }
            $this->helper->getPlugin()->clear();
        }
        return $error;
    }

    protected function _getUrl($action) {
        return Shopware()->Front()->Router()->assemble(array(
            'controller' => 'payment_easycredit',
            'action'    => $action,
        ));
    }
}
