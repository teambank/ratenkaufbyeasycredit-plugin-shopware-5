<?php
use Teambank\EasyCreditApiV3\Integration\Checkout;

abstract class Shopware_Controllers_Backend_PaymentEasycredit_Abstract extends Shopware_Controllers_Backend_ExtJs {
    public function verifyCredentialsAction() {
        /**
         * @var Checkout $checkout
         */
        $checkout = $this->get('easyCreditCheckout');

        $apiKey = $this->Request()->getParam('easycreditApiKey', null);
        $apiPassword = $this->Request()->getParam('easycreditApiPassword', null);
        $apiSignature = $this->Request()->getParam('easycreditApiSignature', null);

        if (
            $apiKey === null ||
            strlen($apiKey) === 0 ||
            strlen($apiPassword) === 0 ||
            $apiPassword === null
        ) {
            $this->View()->assign(array('status' => false, 'errorMessage' => "Please provide apiKey and apiToken!"));
            return;
        }

        try {
           $checkout->verifyCredentials($apiKey, $apiPassword, $apiSignature);

           $flexpriceService = new EasyCredit_FlexpriceService();
           $flexpriceService->updateConfiguration();

           $valid = true;
           $message = '';
        } catch (\Exception $e) {
            $valid = false;
            $message = $e->getMessage();
        }
        $this->View()->assign(array('status' => true, 'valid' => $valid, 'errorMessage' => $message));
    }

    public function getStatusAction()
    {
        $paymentMethods = (new \EasyCredit_Helper())->getPlugin()->getPaymentMethods();

        $data = [
            'success' => true,
        ];
        foreach ($paymentMethods as $method) {
            $data['methods'][$method->getName()] = $method->getActive();
        }

        $this->View()->assign($data);
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
                'getStatus'
            );
        }
    }
} else {
    class Shopware_Controllers_Backend_PaymentEasycredit extends Shopware_Controllers_Backend_PaymentEasycredit_Abstract {

    }
}
