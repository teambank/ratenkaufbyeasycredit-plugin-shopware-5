<?php
namespace Teambank\RatenkaufByEasyCreditApiV3\Integration;

interface CheckoutInterface {

    public function getRedirectUrl();
    public function start(TransactionInitRequestWrapper $wrappedRequest);
    public function getConfig();
    public function isInitialized();
    public function loadTransaction();
    public function authorize($orderId = null);

    // public function getInstallmentValues($amount);
    // public function getAgreement();
    // public function verifyCredentials($apiKey, $apiToken);
    // public function isCustomerSameAsBilling(TransactionInitRequestWrapper $wrappedRequest);

    public function verifyAddress(TransactionInitRequestWrapper $wrappedRequest, $preCheck);
    public function isAmountValid(TransactionInitRequestWrapper $wrappedRequest);
    public function clear();
}
