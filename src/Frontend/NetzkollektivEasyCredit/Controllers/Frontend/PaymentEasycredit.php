<?php
class Shopware_Controllers_Frontend_PaymentEasycredit extends Shopware_Controllers_Frontend_Payment
{
    const CONNECTION_ERROR =  'Kommunikationsproblem mit Zahlungsprovider. Bitte versuchen sie es später nochmal.';
    
    /**
     * @var Shopware_Plugins_Frontend_SwagPaymentPaypal_Bootstrap $plugin
     */
    private $plugin;

    /**
     * @var Enlight_Components_Session_Namespace $session
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->plugin = $this->get('plugins')->Frontend()->NetzkollektivEasyCredit();
        $this->session = $this->get('session');
    }


    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (version_compare(Shopware::VERSION, '4.2.0', '<') && Shopware::VERSION != '___VERSION___') {
            if ($name == 'pluginlogger') {
                $name = 'log';
            }
            $name = ucfirst($name);

            return Shopware()->Bootstrap()->getResource($name);
        }

        return Shopware()->Container()->get($name);
    }

    public function getPaymentShortName()
    {
        if (($user = $this->getUser()) !== null
            && !empty($user['additional']['payment']['name'])) {
            return $user['additional']['payment']['name'];
        } else {
            return null;
        }
    }


    public function createPaymentUniqueId()
    {
        if (class_exists('\Shopware\Components\Random')) {
            return \Shopware\Components\Random::getAlphanumericString(32);
        }
        return parent::createPaymentUniqueId();
    }

    /**
     * Index action method.
     *
     * Forwards to correct the action.
     */

    public function saveTransactionUuidForOrderNumber($transactionUuid, $orderNumber) {
        $orderId = Shopware()->Db()->fetchOne(
            'SELECT `id` FROM `s_order` WHERE `ordernumber`=?',
            array($orderNumber)
        );

        Shopware()->Db()->update(
            's_order_attributes',
            array(
                'easycredit_transaction_uuid' => $transactionUuid,
            ),
            'orderID=' . $orderId
        );
    }

    public function indexAction()
    {
        $checkout = $this->get('easyCreditCheckout');

        try{
            $captureResult = $checkout->capture();
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->redirectCheckoutConfirm();
            return;
        }

        if (!isset($captureResult->uuid)) {
            Shopware()->Session()->EasyCredit["apiError"] = self::CONNECTION_ERROR;
            $this->redirectCheckoutConfirm();
            return;
        }

        $transactionId = Shopware()->Session()->EasyCredit["transaction_id"];
        $transactionUuid = $captureResult->uuid;
        $paymentUniqueId = $this->createPaymentUniqueId();

        $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId);

        $this->saveTransactionUuidForOrderNumber($transactionUuid, $orderNumber);

        $this->redirect(array(
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $paymentUniqueId
        ));
    }

    public function addInterestSurcharge() {
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
                'sessionID = ?' => $this->session->get('sessionId'),
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
 
    public function redirectCheckoutConfirm() {
        $this->redirect(
            array(
                'controller' => 'checkout',
                'action' => 'confirm',
            )
        );
    }

    public function returnAction()
    {
        Shopware()->Session()->EasyCredit["externRedirect"] = false;
        $checkout = $this->get('easyCreditCheckout');

        try {
            $approved = $checkout->isApproved();
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->_redirToPaymentSelection();
            return;
        }

        if (!$approved) {
            Shopware()->Session()->EasyCredit["apiError"] = 'Ratenkauf by easyCredit wurde nicht genehmigt.';
            $this->_redirToPaymentSelection();
            return;
        }

        try{
            $checkout->loadFinancingInformation();
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->_redirToPaymentSelection();
            return;
        }

        $this->getPlugin()->addInterest();

        if ($this->getPlugin()->isResponsive()) {
            $this->redirectCheckoutConfirm();
        } else {
            // Fake Post Request to save payment (Emotion)
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['register']['payment'] = $this->getPlugin()->getPayment()->getId();
            $_GET['sTarget'] = 'checkout';
            $this->forward('savePayment','account');
        }
    }

    public function cancelAction() {
        $this->_redirToPaymentSelection();
    }

    public function rejectAction() {
        $this->_redirToPaymentSelection();
    }

    public function getPlugin() {
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit();
    }

    protected function _redirToPaymentSelection() {
        if ($this->getPlugin()->isResponsive()) {

            $this->redirect(array(
                'controller'=>'checkout',
                'action'=>'shippingPayment'
            ));

        } else {

            $this->redirect(array(
                'controller'=>'account',
                'action' => 'payment',
                'target' => 'checkout'
            ));

        }
    }
}