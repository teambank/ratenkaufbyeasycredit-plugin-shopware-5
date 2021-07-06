<?php
class Shopware_Controllers_Frontend_PaymentEasycredit extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap $plugin
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

    public function indexAction()
    {
        try {
            $transactionId = Shopware()->Session()->EasyCredit["transaction_id"];
            $paymentUniqueId = $this->createPaymentUniqueId();
            $paymentStatusId = $this->plugin->Config()->get('easycreditPaymentStatus');

            $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId, null, false);

            $checkout = $this->get('easyCreditCheckout');
            $captureResult = $checkout->capture(null, $orderNumber);

            $this->savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, false);
            $this->setPaymentClearedDate($transactionId);

            $this->redirect(array(
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paymentUniqueId
            ));

        } catch (\Exception $e) {
            $orderStatusId = $this->plugin->Config()->get('easycreditOrderErrorStatus');
            $this->saveOrderStatus($transactionId, $paymentUniqueId, $orderStatusId, true, $e->getMessage());

            Shopware()->Container()->get('pluginlogger')->error($e->getMessage());
            $this->getPlugin()->getStorage()->set('apiError', 'Die Zahlung mit <strong>ratenkauf by easyCredit</strong> konnte auf Grund eines Fehlers nicht abgeschlossen werden. Bitte probieren Sie es erneut oder kontaktieren Sie den Händler.');
            $this->getPlugin()->getStorage()->set('apiErrorSkipSuffix', true);
            $this->redirect(array(
                'controller' => 'checkout',
                'action' => 'cart'
            ));
            return;
        }
    }

    public function saveOrderStatus($transactionId, $paymentUniqueId, $orderStatusId, $sendStatusMail = false, $comment)
    {
        $sql = '
            SELECT id FROM s_order
            WHERE transactionID=? AND temporaryID=?
            AND status!=-1
        ';
        $orderId = (int) Shopware()->Db()->fetchOne($sql, [
                $transactionId,
                $paymentUniqueId,
            ]);
        $order = Shopware()->Modules()->Order();
        $order->setOrderStatus($orderId, $orderStatusId, $sendStatusMail, $comment);
    }

    protected function setPaymentClearedDate($transactionId) {
        if (defined('\Shopware::VERSION') && version_compare(\Shopware::VERSION,'5.1.0') == -1) {
            return;
        }

        $sql = "
            UPDATE s_order as o
            INNER JOIN s_core_states AS s
                   ON s.id = o.cleared
            SET o.cleareddate = NOW()
            WHERE o.cleareddate IS NULL
            AND s.name = 'completely_paid'
            AND o.transactionID = ?;
        ";
        $this->get('db')->query($sql, array($transactionId));
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
        $checkout = $this->get('easyCreditCheckout');

        try {
            $approved = $checkout->isApproved();
        } catch (Exception $e) {
            $this->getPlugin()->getStorage()->set('apiError', $e->getMessage());
            $this->_redirToPaymentSelection();
            return;
        }

        if (!$approved) {
            $this->getPlugin()->getStorage()->set('apiError', 'ratenkauf by easyCredit wurde nicht genehmigt.');
            $this->_redirToPaymentSelection();
            return;
        }

        try{
            $checkout->loadFinancingInformation();
        } catch (Exception $e) {
            $this->getPlugin()->getStorage()->set('apiError', $e->getMessage());
            $this->_redirToPaymentSelection();
            return;
        }

        $this->getPlugin()->addInterest();
        $this->redirectCheckoutConfirm();
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
        $this->redirect(array(
            'controller'=>'checkout',
            'action'=>'shippingPayment'
        ));
    }
}
