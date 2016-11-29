<?php
class Shopware_Controllers_Backend_PaymentEasycredit extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware {

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

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'verifyCredentials'
        );
    }
}
