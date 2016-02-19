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

    /**
     * {@inheritdoc}
     */
/*    public function preDispatch()
    {
        if (in_array($this->Request()->getActionName(), array('recurring', 'notify'))) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }
*/

    /**
     * Returns if the current user is logged in
     *
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return (isset($this->session->sUserId) && !empty($this->session->sUserId));
    }

    /**
     * Index action method.
     *
     * Forwards to correct the action.
     */
    public function indexAction()
    {

        $this->forward('gateway');
    }

    /**
     * Express payment action method.
     */
    public function expressAction()
    {
        unset($this->session->sOrderVariables);

        $payment = $this->plugin->getPayment();
        if ($payment !== null) {
            $this->session->sPaymentID = $payment->getId();
        }

        $this->forward('gateway');
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
        } catch (Mage_Core_Exception $e) {
            //$this->_getCheckoutSession()->addError($this->__('Unable to start easyCredit Payment:').' '.$e->getMessage());
            print_r($e->getMessage());
            echo "EXCEPTION";
            exit();
        } catch (Exception $e) {
            print_r($e->getMessage());
            echo "EXCEPTION";
            exit();
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
 

    public function returnAction() {
        try {
            //$this->_validateQuote();

            $checkout = $this->get('easyCreditCheckout');
            if (!$checkout->isApproved()) {
                throw new Exception('transaction not approved');
            }
            $checkout->loadFinancingInformation();
            Shopware()->Session()->EasyCredit["externRedirect"] = false;

            /*$quote = $this->_getQuote();
            $quote->getPayment()
                ->setMethod('easycredit');
            $quote->collectTotals()->save();
            */

            $this->addInterestSurcharge();

            $this->redirect(array('controller'=>'checkout'));
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            //$this->_getCheckoutSession()->addError($this->__('Unable to validate easyCredit Payment.'));
            //Mage::logException($e);
            // echo $e->getMessage(); exit;
        }
        $this->redirect(array('controller' => 'checkout'));
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

    /**
     * Notify action method
     */
    public function reviewAction()
    {
        $txnId = $this->Request()->get('parent_txn_id') ?: $this->Request()->get('txn_id');
        try {
            $client = $this->get('paypalClient');
            $details = $client->getTransactionDetails(array('TRANSACTIONID' => $txnId));
        } catch (Exception $e) {
            $message = sprintf(
                "PayPal-Notify: Exception %s",
                $e->getMessage()
            );
            $context = array('exception' => $e);
            $this->get('pluginlogger')->error($message, $context);
        }

        if (empty($details['PAYMENTSTATUS']) || empty($details['ACK']) || $details['ACK'] != 'Success') {
            $message = sprintf(
                "PayPal-Notify: Could not find TRANSACTIONID %s",
                $txnId
            );
            $context = array('details' => $details);
            $this->get('pluginlogger')->error($message, $context);

            return;
        }

        $this->plugin->setPaymentStatus($details['TRANSACTIONID'], $details['PAYMENTSTATUS']);
    }

    /**
     * Login action method
     */
    public function loginAction()
    {
        $request = $this->Request();
        $view = $this->View();
        /** @var \Shopware_Components_Paypal_RestClient $restClient */
        $restClient = $this->get('paypalRestClient');
        $config = $this->plugin->Config();

        $auth = $restClient->getOpenIdAuth(
            $request->getParam('code'),
            $request->getParam('redirect_uri')
        );

        if (!empty($auth) && !empty($auth['access_token'])) {
            $this->session->PaypalAuth = $auth;
        } else {
            $auth = $this->session->PaypalAuth;
        }

        $identity = $restClient->getOpenIdIdentity($auth);

        if (!empty($identity['email'])) {
            $this->createAccount(
                array(
                    'EMAIL' => $identity['email'],
                    'PAYERID' => $identity['user_id'],
                    'FIRSTNAME' => $identity['given_name'],
                    'LASTNAME' => $identity['family_name'],
                    'SHIPTOSTREET' => $identity['address']['street_address'],
                    'SHIPTOZIP' => $identity['address']['postal_code'],
                    'SHIPTOCITY' => $identity['address']['locality'],
                    'SHIPTOCOUNTRYCODE' => $identity['address']['country'],
                    'SHIPTOSTATE' => $identity['address']['region'],
                    'SHIPTONAME' => $identity['name'],
                    'SHIPTOPHONENUM' => $identity['phone_number'],
                ),
                !$config->get('paypalFinishRegister', true)
            );
        }

        $view->PaypalResponse = $identity;
        $view->PaypalIdentity = !empty($identity['email']);
        $view->PaypalUserLoggedIn = $this->isUserLoggedIn();
        $view->PaypalFinishRegister = !$config->get('paypalFinishRegister');
    }

    /**
     * @param $details
     * @return array
     */
    protected function finishCheckout($details)
    {
        $client = $client = $this->get('paypalClient');
        $config = $this->plugin->Config();

        $router = $this->Front()->Router();
        $notifyUrl = $router->assemble(array('action' => 'notify', 'forceSecure' => true, 'appendSession' => true));

        if (!empty($details['REFERENCEID'])) {
            $params = array(
                'REFERENCEID' => $details['REFERENCEID'],
                'IPADDRESS' => $this->Request()->getClientIp(false),
                'NOTIFYURL' => $notifyUrl,
                'CUSTOM' => $this->createPaymentUniqueId(),
                'BUTTONSOURCE' => 'Shopware_Cart_ECM'
            );
        } else {
            $params = array(
                'TOKEN' => $details['TOKEN'],
                'PAYERID' => $details['PAYERID'],
                'NOTIFYURL' => $notifyUrl,
                'CUSTOM' => $details['CUSTOM'],
                'BUTTONSOURCE' => 'Shopware_Cart_ECS'
            );
        }

        if (Shopware::VERSION == '___VERSION___' || version_compare(Shopware::VERSION, '4.4.0') >= 0) {
            $params['BUTTONSOURCE'] = 'Shopware_Cart_5';
        }

        if (empty($params['TOKEN'])) {
            $params['PAYMENTACTION'] = 'Authorization';
        } else {
            $params['PAYMENTACTION'] = $config->get('paypalPaymentAction', 'Sale');
        }

        $params = array_merge($params, $this->getBasketParameter());
        $params = array_merge($params, $this->getCustomerParameter());

        if ($config->get('paypalSendInvoiceId')) {
            $orderNumber = $this->saveOrder(
                isset($params['TOKEN']) ? $params['TOKEN'] : $params['REFERENCEID'],
                $params['CUSTOM']
            );
            $prefix = $config->get('paypalPrefixInvoiceId');
            if (!empty($prefix)) {
                // Set prefixed invoice id - Remove special chars and spaces
                $prefix = str_replace(' ', '', $prefix);
                $prefix = preg_replace('/[^A-Za-z0-9\-]/', '', $prefix);
                $params['INVNUM'] = $prefix . $orderNumber;
            } else {
                $params['INVNUM'] = $orderNumber;
            }
        }

        //$params['SOFTDESCRIPTOR'] = $orderNumber;

        if (!empty($params['REFERENCEID'])) {
            $result = $client->doReferenceTransaction($params);
        } else {
            $result = $client->doExpressCheckoutPayment($params);
        }
        $result['CUSTOM'] = $params['CUSTOM'];

        if ($result['ACK'] != 'Success') {
            return $result;
        }

        if (empty($orderNumber)) {
            $orderNumber = $this->saveOrder(
                $result['TRANSACTIONID'],
                $result['CUSTOM']
            );
        }

        // Sets billing agreement id
        if (!empty($result['BILLINGAGREEMENTID'])) {
            try {
                $sql = '
                    INSERT INTO s_order_attributes (orderID, swag_payal_billing_agreement_id)
                    SELECT id, ? FROM s_order WHERE ordernumber = ?
                    ON DUPLICATE KEY UPDATE
                       swag_payal_billing_agreement_id = VALUES(swag_payal_billing_agreement_id)
                ';
                $this->get('db')->query($sql, array($result['BILLINGAGREEMENTID'], $orderNumber));
            } catch (Exception $e) {
            }
        }

        // Sets express flag
        if (!empty($params['TOKEN'])) {
            try {
                $sql = '
                    INSERT INTO s_order_attributes (orderID, swag_payal_express)
                    SELECT id, 1 FROM s_order WHERE ordernumber = ?
                    ON DUPLICATE KEY UPDATE swag_payal_express = 1
                ';
                $this->get('db')->query($sql, array($orderNumber,));
            } catch (Exception $e) {
            }
        }

        // Stets transaction details
        $sql = '
            UPDATE `s_order`
            SET transactionID = ?, internalcomment = CONCAT(internalcomment, ?),
              customercomment = CONCAT(customercomment, ?)
            WHERE ordernumber = ?
        ';
        $this->get('db')->query(
            $sql,
            array(
                $result['TRANSACTIONID'],
                isset($details['EMAIL']) ? "{$details['EMAIL']} ({$details['PAYERSTATUS']})\r\n" : null,
                isset($details['NOTE']) ? $details['NOTE'] : '',
                $orderNumber
            )
        );

        // Sets payment status
        $paymentStatus = $result['PAYMENTSTATUS'];
        $ppAmount = floatval($result['AMT']);
        $swAmount = $this->getAmount();
        if (abs($swAmount - $ppAmount) >= 0.01) {
            $paymentStatus = 'AmountMissMatch'; //Überprüfung notwendig
        }
        $this->plugin->setPaymentStatus($result['TRANSACTIONID'], $paymentStatus);

        $result['INVNUM'] = $orderNumber;

        return $result;
    }

    /**
     * @param $details
     * @param bool $finish
     */
    protected function createAccount($details, $finish = true)
    {
        $module = $this->get('modules')->Admin();
        $session = $this->session;

        if (version_compare(Shopware::VERSION, '4.1.0', '>=') || Shopware::VERSION == '___VERSION___') {
            $encoderName = $this->get('passwordEncoder')->getDefaultPasswordEncoderName();
        }

        $data['auth']['email'] = $details['EMAIL'];
        $data['auth']['password'] = $details['PAYERID'];
        $data['auth']['accountmode'] = '1';

        $data['billing']['salutation'] = 'mr';
        $data['billing']['firstname'] = $details['FIRSTNAME'];
        $data['billing']['lastname'] = $details['LASTNAME'];

        if (version_compare(Shopware::VERSION, '4.4.0', '>=') || Shopware::VERSION == '___VERSION___') {
            $data['billing']['street'] = $details['SHIPTOSTREET'];
            if (!empty($details['SHIPTOSTREET2'])) {
                $data['billing']['additional_address_line1'] = $details['SHIPTOSTREET2'];
            }
        } else {
            $street = explode(' ', $details['SHIPTOSTREET']);
            $data['billing']['street'] = $street[0];
            $data['billing']['streetnumber'] = implode(' ', array_slice($street, 1));
            if (strlen($data['billing']['streetnumber']) > 4) {
                $data['billing']['street'] .= ' ' . $data['billing']['streetnumber'];
                $data['billing']['streetnumber'] = '';
            }
            if (empty($data['billing']['streetnumber'])) {
                $data['billing']['streetnumber'] = ' ';
            }
        }

        $data['billing']['zipcode'] = $details['SHIPTOZIP'];
        $data['billing']['city'] = $details['SHIPTOCITY'];
        $sql = 'SELECT id FROM s_core_countries WHERE countryiso=?';
        $countryId = $this->get('db')->fetchOne($sql, array($details['SHIPTOCOUNTRYCODE']));
        $data['billing']['country'] = $countryId;
        if (!empty($details['SHIPTOSTATE']) && $details['SHIPTOSTATE'] != 'Empty') {
            $sql = 'SELECT id FROM s_core_countries_states WHERE countryID=? AND shortcode=?';
            $stateId = $this->get('db')->fetchOne($sql, array($countryId, $details['SHIPTOSTATE']));
            $data['billing']['stateID'] = $stateId;
        }
        if (!empty($details['BUSINESS'])) {
            $data['billing']['customer_type'] = 'business';
            $data['billing']['company'] = $details['BUSINESS'];
        } else {
            $data['billing']['customer_type'] = 'private';
            $data['billing']['company'] = '';
        }
        $data['billing']['department'] = '';

        $data['shipping'] = $data['billing'];
        $name = explode(' ', $details['SHIPTONAME']);
        $data['shipping']['firstname'] = $name[0];
        $data['shipping']['lastname'] = implode(' ', array_slice($name, 1));
        if (!empty($details['SHIPTOPHONENUM'])) {
            $data['billing']['phone'] = $details['SHIPTOPHONENUM'];
        }

        $sql = 'SELECT id FROM s_core_paymentmeans WHERE name=?';
        $paymentId = $this->get('db')->fetchOne($sql, array('paypal'));
        $data['payment']['object'] = $module->sGetPaymentMeanById($paymentId);

        //if(!$finish) {
        $shop = $this->get('shop');
        $shop = $shop->getMain() ?: $shop;
        $sql = 'SELECT `password` FROM `s_user` WHERE `email` LIKE ? AND `active` = 1 ';
        if ($shop->getCustomerScope()) {
            $sql .= "AND `subshopID` = {$shop->getId()} ";
        }
        $sql .= 'ORDER BY `accountmode`';
        $data['auth']['passwordMD5'] = $this->get('db')->fetchOne($sql, array($data['auth']['email']));
        //}
        // First try login / Reuse paypal account
        $module->sSYSTEM->_POST = $data['auth'];
        $module->sLogin(true);

        // Check login status
        if ($module->sCheckUser()) {
            $module->sSYSTEM->_POST = $data['shipping'];
            $module->sUpdateShipping();
            $module->sSYSTEM->_POST = array('sPayment' => $paymentId);
            $module->sUpdatePayment();
        } else {
            if (isset($encoderName)) {
                $data["auth"]["encoderName"] = $encoderName;
                $data["auth"]["password"] = $this->get('passwordEncoder')
                    ->encodePassword($data["auth"]["password"], $encoderName);
            } else {
                $data['auth']['password'] = md5($data['auth']['password']);
            }
            if (!$finish) {
                unset($data['shipping']);
                if (!empty($data['billing']['stateID'])) {
                    $data['billing']['country_state_' . $data['billing']['country']] = $data['billing']['stateID'];
                }
            }
            $session->sRegisterFinished = false;
            if (version_compare(Shopware::VERSION, '4.3.0', '>=') || Shopware::VERSION == '___VERSION___') {
                $session->sRegister = $data;
            } else {
                $session->sRegister = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
            }
            if ($finish) {
                $module->sSaveRegister();
            }
        }
    }

    /**
     * Returns the article list parameter data.
     *
     * @return array
     */
    protected function getBasketParameter()
    {
        $params = array();
        $user = $this->getUser();

        $params['CURRENCYCODE'] = $this->getCurrencyShortName();

        if ($user !== null) {
            $basket = $this->getBasket();
            if (!empty($basket['sShippingcosts'])) {
                $params['SHIPPINGAMT'] = $this->getShipment();
            }
            $params['AMT'] = $this->getAmount();
        } else {
            $basket = $this->get('modules')->Basket()->sGetBasket();
            if (!empty($basket['sShippingcosts'])) {
                $params['SHIPPINGAMT'] = !empty($basket['sShippingcostsWithTax']) ? $basket['sShippingcostsWithTax'] : $basket['sShippingcosts'];
                $params['SHIPPINGAMT'] = str_replace(',', '.', $params['SHIPPINGAMT']);
            }
            if (!empty($user['additional']['charge_vat']) && !empty($item['AmountWithTaxNumeric'])) {
                $params['AMT'] = $basket['AmountWithTaxNumeric'];
            } else {
                $params['AMT'] = $basket['AmountNumeric'];
            }
            $params['AMT'] = $basket['AmountNumeric'];
        }
        $params['AMT'] = number_format($params['AMT'], 2, '.', '');
        $params['SHIPPINGAMT'] = number_format($params['SHIPPINGAMT'], 2, '.', '');
        $params['ITEMAMT'] = number_format($params['AMT'] - $params['SHIPPINGAMT'], 2, '.', '');
        $params['TAXAMT'] = number_format(0, 2, '.', '');

        $config = $this->plugin->Config();
        if ($config->get('paypalTransferCart') && $params['ITEMAMT'] != '0.00' && count($basket['content']) < 25) {
            foreach ($basket['content'] as $key => $item) {
                if (!empty($user['additional']['charge_vat']) && !empty($item['amountWithTax'])) {
                    $amount = round($item['amountWithTax'], 2);
                    $quantity = 1;
                } else {
                    $amount = str_replace(',', '.', $item['amount']);
                    $quantity = $item['quantity'];
                    $amount = $amount / $item['quantity'];
                }
                $amount = round($amount, 2);
                $article = array(
                    'L_NUMBER' . $key => $item['ordernumber'],
                    'L_NAME' . $key => $item['articlename'],
                    'L_AMT' . $key => $amount,
                    'L_QTY' . $key => $quantity
                );
                $params = array_merge($params, $article);
            }
        }

        if ($params['ITEMAMT'] == '0.00') {
            $params['ITEMAMT'] = $params['SHIPPINGAMT'];
            $params['SHIPPINGAMT'] = '0.00';
        }

        return $params;
    }

    /**
     * Returns the prepared customer parameter data.
     *
     * @return array
     */
    protected function getCustomerParameter()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return array(
                'LOCALECODE' => $this->plugin->getLocaleCode(true)
            );
        }
        $shipping = $user['shippingaddress'];
        $name = $shipping['firstname'] . ' ' . $shipping['lastname'];
        if (!empty($shipping['company'])) {
            $name = $shipping['company'] . ' - ' . $name;
        }
        if (!empty($shipping['streetnumber'])) {
            $shipping['street'] .= ' ' . $shipping['streetnumber'];
        }
        if (!empty($shipping['additional_address_line1'])) {
            $shipping['street2'] = $shipping['additional_address_line1'];
            if (!empty($shipping['additional_address_line2'])) {
                $shipping['street2'] .= ' ' . $shipping['additional_address_line2'];
            }
        } else {
            $shipping['street2'] = '';
        }
        $customer = array(
            'CUSTOMERSERVICENUMBER' => $user['billingaddress']['customernumber'],
            //'gender' => $shipping['salutation'] == 'ms' ? 'f' : 'm',
            'SHIPTONAME' => $name,
            'SHIPTOSTREET' => $shipping['street'],
            'SHIPTOSTREET2' => $shipping['street2'],
            'SHIPTOZIP' => $shipping['zipcode'],
            'SHIPTOCITY' => $shipping['city'],
            'SHIPTOCOUNTRY' => $user['additional']['countryShipping']['countryiso'],
            'EMAIL' => $user['additional']['user']['email'],
            'SHIPTOPHONENUM' => $user['billingaddress']['phone'],
            'LOCALECODE' => $this->plugin->getLocaleCode(true)
        );
        if (!empty($user['additional']['stateShipping']['shortcode'])) {
            $customer['SHIPTOSTATE'] = $user['additional']['stateShipping']['shortcode'];
        }

        return $customer;
    }
}
