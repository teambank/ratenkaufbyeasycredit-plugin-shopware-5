<?php

abstract class Shopware_Controllers_Backend_PaymentEasycredit_Abstract extends Shopware_Controllers_Backend_ExtJs {
    public function verifyCredentialsAction() {
        /**
         * @var \Netzkollektiv\EasyCredit\Checkout $checkout
         */
        $checkout = $this->get('easyCreditCheckout');

        $apiKey = $this->Request()->getParam('easycreditApiKey', null);
        $apiToken = $this->Request()->getParam('easycreditApiToken', null);

        if (
            $apiKey === null ||
            strlen($apiKey) === 0 ||
            strlen($apiToken) === 0 ||
            $apiToken === null
        ) {
            $this->View()->assign(array('status' => false, 'errorMessage' => "Please provide apiKey and apiToken!"));
            return;
        }

        $valid = $checkout->verifyCredentials($apiKey, $apiToken);

        $this->View()->assign(array('status' => true, 'valid' => $valid, 'errorMessage' => ''));
    }

    public function testAction() {
        /**@var $repository \Shopware\Models\Order\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $filters = array(array('property' => 'status.id', 'expression' => '!=', 'value' => '-1'));
        $orderStatus = $repository->getOrderStatusQuery($filters)->getArrayResult();
        $this->View()->assign(array('success' => true, 'data' => $orderStatus));
    }
}

if (interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    class Shopware_Controllers_Backend_PaymentEasycredit extends Shopware_Controllers_Backend_PaymentEasycredit_Abstract implements \Shopware\Components\CSRFWhitelistAware {

        /**
         * Returns a list with actions which should not be validated for CSRF protection
         *
         * @return string[]
         */
        public function getWhitelistedCSRFActions()
        {
            return array(
                'verifyCredentials',
                'test'
            );
        }
    }
} else {
    class Shopware_Controllers_Backend_PaymentEasycredit extends Shopware_Controllers_Backend_PaymentEasycredit_Abstract {

    }
}

