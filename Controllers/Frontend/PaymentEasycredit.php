<?php
class Shopware_Controllers_Frontend_PaymentEasycredit extends Shopware_Controllers_Frontend_Payment
{
    
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

    public function getCommunicationError() {
        return 'Kommunikationsproblem zum EasyCredit Server. Bitte versuchen sie es später nocheinmal.';
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
        if (
            isset(Shopware()->Session()->EasyCredit["externRedirect"])
            && Shopware()->Session()->EasyCredit["externRedirect"]
        ) {
            Shopware()->Session()->EasyCredit["externRedirect"] = false;
            $this->forward('gateway');
        } else {
            $checkout = $this->get('easyCreditCheckout');

            try{
                $captureResult = $checkout->capture();
            } catch (Exception $e) {
                Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
                $this->redirectCheckoutConfirm();
                return;
            }

            if (!isset($captureResult->uuid)) {
                Shopware()->Session()->EasyCredit["apiError"] = $this->getCommunicationError();
                $this->redirectCheckoutConfirm();
                return;
            }

            $transactionId = Shopware()->Session()->EasyCredit["transaction_id"];
            $transactionUuid= $captureResult->uuid;
            $paymentUniqueId = $this->createPaymentUniqueId();

            $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId);

            $this->saveTransactionUuidForOrderNumber($transactionUuid, $orderNumber);

            $this->redirect(array(
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paymentUniqueId
            ));
        }


    }

    /**
     * Gateway payment action method.
     *
     * Collects the payment information and transmit it to the payment provider.
     */
    public function gatewayAction()
    {
        $router = $this->Front()->Router();
        $config = $this->plugin->Config();

        try {
            $checkout = $this->get('easyCreditCheckout')
               ->setCancelUrl($this->_getUrl('cancel'))
               ->setReturnUrl($this->_getUrl('return'))
               ->setRejectUrl($this->_getUrl('reject'))
               ->start(new Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Quote($this));

            if ($url = $checkout->getRedirectUrl()) {
                $this->redirect($url);

                return;
            }
        } catch (Exception $e) {
echo $e->getMessage(); exit;
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            Shopware()->Session()->EasyCredit["externRedirect"] = false;
            $this->redirectCheckoutConfirm();
            return;
        }
    }

    protected function _getUrl($action) {
        return $this->Front()->Router()->assemble(array(
            'action'    => $action,
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
        return;
    }

    public function returnAction()
    {

        Shopware()->Session()->EasyCredit["externRedirect"] = false;
        $checkout = $this->get('easyCreditCheckout');

        try {
            $approved = $checkout->isApproved();
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->redirectCheckoutConfirm();
            return;
        }

        if (!$approved) {
            Shopware()->Session()->EasyCredit["apiError"] = 'EasyCredit Ratenkauf wurde nicht genehmigt.';
            $this->redirectCheckoutConfirm();
            return;
        }

        try{
            $checkout->loadFinancingInformation();
        } catch (Exception $e) {
            Shopware()->Session()->EasyCredit["apiError"] = $e->getMessage();
            $this->redirectCheckoutConfirm();
            return;
        }

        $this->addInterestSurcharge();

        $this->redirectCheckoutConfirm();
    }

    /**
     * Cancel action method
     */
    public function cancelAction()
    {
        // storage->clear

        Shopware()->Session()->EasyCredit["externRedirect"] = false;

        $this->redirect(array(
            'controller'=>'checkout',
            'action'=>'shippingPayment'
        ));
    }

    public function rejectAction() {
        // storage->clear

        Shopware()->Session()->EasyCredit["externRedirect"] = false;

        $this->redirect(array(
            'controller'=>'checkout',
            'action'=>'shippingPayment'
        ));
    }
}
