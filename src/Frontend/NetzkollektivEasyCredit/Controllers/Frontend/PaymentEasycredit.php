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

    private $order;

    private $em;

    protected $helper;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->container = Shopware()->Container();
        $this->helper = new EasyCredit_Helper();
        $this->plugin = $this->container->get('plugins')->Frontend()->NetzkollektivEasyCredit();
        $this->session = $this->container->get('session');
        $this->order = Shopware()->Modules()->Order();
        $this->em = Shopware()->Container()->get('models');
    }

    protected function getModule($moduleName) {
        return $this->container->get('modules')->getModule($moduleName);
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

    public function getOrderId($transactionId, $paymentUniqueId) {
        $sql = '
            SELECT id FROM s_order
            WHERE transactionID=? AND temporaryID=?
            AND status!=-1
        ';
        return (int) Shopware()->Db()->fetchOne($sql, [
            $transactionId,
            $paymentUniqueId,
        ]);
    }

    public function getTransactionInfoBySecuredTransaction($transactionId, $secToken) {
        $sql = '
            SELECT temporaryID, easycredit_token FROM s_order o INNER JOIN s_order_attributes oa ON o.id = oa.orderID
            WHERE o.transactionID=? AND oa.easycredit_sectoken = ?
            AND o.status!=-1
        ';
        return Shopware()->Db()->fetchRow($sql, [
            $transactionId,
            $secToken,
        ]);
    }

    public function indexAction()
    {
        $transactionId = $this->session->EasyCredit["transaction_id"];
        $paymentUniqueId = $this->createPaymentUniqueId();

        $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId, null, false);
        $orderId = $this->getOrderId($transactionId, $paymentUniqueId);

        $this->saveOrderAttributes($orderId);

        try {
            $checkout = $this->container->get('easyCreditCheckout');
            if (!$checkout->authorize($orderNumber)) {
                throw new \Exception('The transaction could not be authorized.');
            }

            $this->redirect(array(
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paymentUniqueId
            ));

        } catch (\Exception $e) {
            $orderStatusId = $this->plugin->Config()->get('easycreditOrderErrorStatus');
            $this->order->setOrderStatus($orderId, $orderStatusId, true, $e->getMessage());

            $this->container->get('pluginlogger')->error($e->getMessage());
            $this->plugin->getStorage()->set('apiError', 'Die Zahlung mit <strong>easyCredit-Ratenkauf</strong> konnte auf Grund eines Fehlers nicht abgeschlossen werden. Bitte probieren Sie es erneut oder kontaktieren Sie den Händler.');
            $this->plugin->getStorage()->set('apiErrorSkipSuffix', true);
            $this->redirect(array(
                'controller' => 'checkout',
                'action' => 'cart'
            ));
            return;
        }
    }

    public function returnAction()
    {
        $checkout = $this->container->get('easyCreditCheckout');
        try {
            $transaction = $checkout->loadTransaction();
            $approved = $checkout->isApproved();
        } catch (Exception $e) {
            $this->plugin->getStorage()->set('apiError', $e->getMessage());
            $this->_redirToPaymentSelection();
            return;
        }

        if (!$approved) {
            $this->plugin->getStorage()->set('apiError', 'easyCredit-Ratenkauf wurde nicht genehmigt.');
            $this->_redirToPaymentSelection();
            return;
        }

        if ($this->plugin->getStorage()->get('express')) {
            $customerService = new EasyCredit_CustomerService();
            $customer = $customerService->createCustomer($transaction);
            $customerService->loginCustomer($customer);
        }

        $this->plugin->addInterest();
        $this->redirectCheckoutConfirm();
    }

    public function createOrder($transaction) {


        $customerService = new EasyCredit_CustomerService();
        $customerService->createCustomer($transaction);

    }

    public function cancelAction() {
        $this->_redirToPaymentSelection();
    }

    public function rejectAction() {
        $this->_redirToPaymentSelection();
    }

    public function authorizeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $secToken = $this->Request()->getParam('secToken', null);
        $transactionId = $this->Request()->getParam('transactionId', null);

        $transactionInfo = $this->getTransactionInfoBySecuredTransaction($transactionId, $secToken);
        if (!$transactionInfo) {
            return $this->respondWithStatus('transaction could not be found', 404);
        }

        $checkout = $this->container->get('easyCreditCheckout');
        $tx = $checkout->loadTransaction($transactionInfo['easycredit_token']);

        if ($tx->getStatus() !== \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation::STATUS_AUTHORIZED) {
            return $this->respondWithStatus('payment status of transaction not updated as transaction status is not AUTHORIZED', 409);
        }

        $paymentStatusId = $this->plugin->Config()->get('easycreditPaymentStatus');

        $this->savePaymentStatus($transactionId, $transactionInfo['temporaryID'], $paymentStatusId, false);
        $this->setPaymentClearedDate($transactionId);

        return $this->respondWithStatus('payment status successfully set');
    }

    public function expressAction() {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $basket = $this->getModule('basket');

        if ($productNumber = $this->Request()->getParam('sAdd')) {
            $basket->sDeleteBasket();
            $basket->sAddArticle($productNumber, (int) $this->Request()->getParam('sQuantity', 1));
        }

        $this->session->offsetSet('sPaymentID', $this->plugin->getPayment()->getId());

        $checkoutController = $this->getCheckoutController();
        $checkoutController->getSelectedCountry();
        $checkoutController->getSelectedDispatch();

        /** @var sAdmin $admin */
        $admin = $this->getModule('admin');
        $countries = $admin->sGetCountryList();
        $shipping = $admin->sGetPremiumShippingcosts(\reset($countries));

        $basket->sGetBasket();

        $this->plugin->getStorage()->set('express', 1);
    }

    public function respondWithStatus($content, $code = 200) {
        http_response_code($code);
        echo $content;
        exit;
    }

    /**
     * @return Shopware_Controllers_Frontend_Checkout
     */
    private function getCheckoutController()
    {
        /** @var Shopware_Controllers_Frontend_Checkout $checkoutController */
        $checkoutController = Enlight_Class::Instance(Shopware_Controllers_Frontend_Checkout::class, [$this->request, $this->response]);
        $checkoutController->init();
        $checkoutController->setView($this->View());
        $checkoutController->setContainer($this->container);
        $checkoutController->setFront($this->front);

        return $checkoutController;
    }

    protected function setPaymentClearedDate($transactionId) {
        /** @phpstan-ignore-next-line */
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
        $this->container->get('db')->query($sql, array($transactionId));
    }

    public function addInterestSurcharge() {
        if (!isset($this->session->EasyCredit["interest_amount"])
            || empty($this->session->EasyCredit["interest_amount"])
           ) {
            return;
        }

        $interest_order_name = 'sw-payment-ec-interest';

        $interest_amount = round($this->session->EasyCredit["interest_amount"], 2);

        $this->container->get('db')->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $this->session->get('sessionId'),
                'ordernumber = ?' => $interest_order_name
            )
        );

        $this->container->get('db')->insert(
            's_order_basket',
            array(
                'sessionID' => $this->session->offsetGet('sessionId'),
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

    public function saveOrderAttributes($orderId) {
        $orderAttributeModel = $this->em->getRepository('Shopware\Models\Attribute\Order')->findOneBy(
            array('orderId' => $orderId)
        );
        /** @phpstan-ignore-next-line */
        if ($orderAttributeModel instanceof \Shopware\Models\Attribute\Order) {
            $orderAttributeModel->setEasycreditSectoken($this->session->EasyCredit["sec_token"]);
            $orderAttributeModel->setEasycreditToken($this->session->EasyCredit["token"]);
            $this->em->persist($orderAttributeModel);
            $this->em->flush();
        }
    }
 
    public function redirectCheckoutConfirm() {
        $this->redirect(
            array(
                'controller' => 'checkout',
                'action' => 'confirm',
            )
        );
    }

    protected function _redirToPaymentSelection() {
        $this->redirect(array(
            'controller'=>'checkout',
            'action'=>'shippingPayment'
        ));
    }
}
