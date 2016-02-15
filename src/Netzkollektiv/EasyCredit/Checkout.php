<?php
namespace Netzkollektiv\EasyCredit;

class Checkout {

    public function __construct(EasyCredit\Api $client, EasyCredit\StorageInterface $storage) {
        $this->_client = $client;
        $this->_storage = $storage;
    }

    protected function _getApi() {
        return $this->_client;
    }

    protected function _getStorage() {
        return $this->_storage;
    }

    public function setCancelUrl($url) {
        $this->_cancelUrl = $url;
        return $this;
    }
    public function getCancelUrl() {
        return $this->_cancelUrl;
    }
    public function setReturnUrl($url) {
        $this->_returnUrl = $url;
        return $this;
    }
    public function getReturnUrl() {
        return $this->_returnUrl;

    }
    public function setRejectUrl($url) {
        $this->_rejectUrl = $url;
        return $this;
    }
    public function getRejectUrl() {
        return $this->_rejectUrl;
    }

    public function getRedirectUrl() {
        return $this->_getApi()
            ->getRedirectUrl($this->_getToken());
    }

    public function start(EasyCredit\QuoteInterface $quote) {

        $result = $this->_getApi()
            ->callProcessInit(
                $quote,
                $this->getCancelUrl(),
                $this->getReturnUrl(),
                $this->getRejectUrl()
            );

        $storage = $this->_getStorage();
        $storage->setData('token',$result->tbVorgangskennung);
        $storage->setData('transaction_id',$result->fachlicheVorgangskennung);
        $storage->setData('authorized_amount',$quote->getGrandTotal());

        //$quote->collectTotals()->save();
        return $this;
    }

    public function isInitialized() {
        try {
            $this->_getToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _getToken() {
        $token = $this->_getStorage()->getData('token');

        if (empty($token)) {
            throw new Exception('EasyCredit payment not initialized');
        }
        return $token;
    }

    public function getPayment() {
        return Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getPayment();
    }

    public function isApproved() {
        $token = $this->_getToken();
        $result = $this->_getApi()->callDecision($token);

        if (!isset($result->entscheidung->entscheidungsergebnis)
            || $result->entscheidung->entscheidungsergebnis != 'GRUEN'
        ) {
            return false;
        }
        return true;
    }

    public function loadFinancingInformation() {
        $token = $this->_getToken();

        /* get transaction status from api */
        $result = $this->_getApi()->callStatus($token);
       
        $this->_getStorage()->setData( 
            'pre_contract_information_url',
            (string)$result->allgemeineVorgangsdaten->urlVorvertraglicheInformationen
        );
        $this->_getStorage()->setData( 
            'redemption_plan',
            (string)$result->tilgungsplanText
        );

        /* get financing info from api */
        $result = $this->_getApi()->callFinancing($token);

        $this->_getStorage()->setData( 
            'interest_amount',
            (float)$result->ratenplan->zinsen->anfallendeZinsen
        );
    }

    public function capture($token = null) {
        if (is_null($token)) {
            $token = $this->_getToken();
        }

        return $this->getApi()
            ->callConfirm($token);
    }
}
