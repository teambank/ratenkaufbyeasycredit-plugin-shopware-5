<?php
namespace Teambank\RatenkaufByEasyCreditApiV3\Integration;

use Teambank\RatenkaufByEasyCreditApiV3\Service\TransactionApi;
use Teambank\RatenkaufByEasyCreditApiV3\Service\WebshopApi;
use Teambank\RatenkaufByEasyCreditApiV3\Service\InstallmentplanApi;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\TransactionInitRequestWrapper;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Storage;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\CheckoutInterface;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\AddressValidator;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\InitializationException;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\TokenException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSummaryResponse;
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformationResponse;
use Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\Article;
use Psr\Log\LoggerInterface;

class Checkout implements CheckoutInterface {

    public function __construct(
        WebshopApi $webshopApi,
        TransactionApi $transactionApi,
        InstallmentplanApi $installmentplanApi,
        StorageInterface $storage,
        AddressValidator $addressValidator,
        PrefixConverter $prefixConverter,
        LoggerInterface $logger
    ) {
        $this->webshopApi = $webshopApi;
        $this->transactionApi = $transactionApi;
        $this->installmentplanApi = $installmentplanApi;
        $this->storage = $storage;
        $this->addressValidator = $addressValidator;
        $this->prefixConverter = $prefixConverter;
        $this->logger = $logger;
    }

    public function getRedirectUrl() {
        return $this->storage->get('redirect_url');
    }

    public function start(
        TransactionInitRequestWrapper $wrappedRequest
    ) {
        $request = $wrappedRequest->getTransactionInitRequest();

        $this->storage
            ->set('uniqid', uniqid());

        $result = $this->transactionApi->apiPaymentV3TransactionPost($request);

        $this->storage
            ->set('token', $result->getTechnicalTransactionId())
            ->set('transaction_id', $result->getTransactionId())
            ->set('authorized_amount', $request->getOrderDetails()->getOrderValue())
            ->set('address_hash', $this->addressValidator->hashAddress($request->getOrderDetails()->getShippingAddress()))
            ->set('redirect_url', $result->getRedirectUrl());

        return $this;
    }

    public function getConfig() {
        return $this->_api->getConfig();
    }

    public function isInitialized() {
        try {
            $this->_getToken();
            return true;
        } catch (TokenException $e) {
            return false;
        }
    }

    protected function _getToken() {
        $token = $this->storage->get('token');

        if (empty($token)) {
            throw new InitializationException('ratenkauf by easyCredit payment was not initialized');
        }
        return $token;
    }
    
    /*public function isAccepted() {
        try {
            list($response, $statusCode) = $this->transactionApi->apiPaymentV3TransactionTechnicalTransactionIdPreAuthorizationPostWithHttpInfo(
                $this->_getToken()
            );

            return ($statusCode === 202);
        } catch (ApiException $e) {
            return false;
        }
    }*/

    public function loadTransaction() {

        $result = $this->transactionApi->apiPaymentV3TransactionTechnicalTransactionIdGet(
            $this->_getToken()
        );

        if ($result->getDecision()->getDecisionOutcome() === TransactionSummaryResponse::DECISION_OUTCOME_POSITIVE) {
            $this->storage->set(
                'interest_amount',
                (float) $result->getDecision()->getInterest()
            )->set(
                'summary',
                json_encode($result->getDecision()->jsonSerialize())
            );
            return $result;
        }

        if ($result->getStatus() == TransactionInformationResponse::STATUS_OPEN) {
            throw new InitializationException('ratenkauf by easyCredit payment terminal was not successfully finished');
        }
    }

    public function isApproved() {
        $summary = json_decode($this->storage->get('summary'));
        if ($summary &&
            isset($summary->decisionOutcome) && 
            TransactionSummaryResponse::DECISION_OUTCOME_POSITIVE == $summary->decisionOutcome
        ) {
            return true;
        }
        return false;
    }

    public function authorize($orderId = null) {
        try {
            list($response, $statusCode) = $this->transactionApi->apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost(
                $this->_getToken(),
                new \Teambank\RatenkaufByEasyCreditApiV3\Model\AuthorizationRequest()
            );

            if ($statusCode === 202) {
                $this->storage->set(
                    'is_authorized', 1
                );              
                return true;
            }
        } catch (ApiException $e) {
            $this->logger->warning($e);
        }
        return false;
    }

    public function getInstallmentValues($amount) {
        return $this->installmentplanApi->apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost(
            $this->webshopApi->getConfig()->getUsername(),
            new InstallmentPlanRequest([
                'articles'=> [
                    new Article(['identifier' => 'single', 'price' => $amount ])
                ]
            ])
        );
    }

    public function getWebshopDetails() {
        return $this->webshopApi->apiPaymentV3WebshopGet();
    }

    public function verifyCredentials($apiKey, $apiToken, $apiSignature = null) {
        $this->webshopApi->getConfig()
            ->setUsername($apiKey)
            ->setPassword($apiToken)
            ->setAccessToken($apiSignature);

        try {
            $this->webshopApi->apiPaymentV3WebshopIntegrationcheckPost(
                new IntegrationCheckRequest(['message'=>''])
            );
        } catch (ApiException $e) {
            if ($e->getCode() === 401) {
                throw new ApiCredentialsInvalidException();
            }
            if ($e->getCode() === 403) {
                throw new ApiCredentialsNotActiveException();
            }
            throw $e;
        }
    }

    public function isAvailable(TransactionInitRequestWrapper $wrappedRequest) {

        $this->addressValidator->validate($wrappedRequest);

        $request = $wrappedRequest->getTransactionInitRequest();
        try {
            $this->getInstallmentValues($request->getOrderDetails()->getOrderValue());
        } catch (ApiException $e) {
            if ($e->getCode() === 400) {
                throw new AmountOutOfRange('Der Betrag befindet sich außerhalb ');
            }
            throw $e;
        }

        return true;
    }

    public function verifyAddress(TransactionInitRequestWrapper $wrappedRequest, $preCheck = false) {
        $request = $wrappedRequest->getTransactionInitRequest();

        $initialHash = $this->storage->get('address_hash');

        $billingHash = null;
        if ($request->getOrderDetails()->getInvoiceAddress()) {
            $billingHash = $this->addressValidator->hashAddress(
                $request->getOrderDetails()->getInvoiceAddress()
            );
        }

        $shippingHash = $this->addressValidator->hashAddress(
            $request->getOrderDetails()->getShippingAddress()
        );

        return (
            ($preCheck || $initialHash === $shippingHash) &&
            ($billingHash === $shippingHash || $billingHash === null)
        );
    }

    public function isAmountValid(TransactionInitRequestWrapper $wrappedRequest) {
        $request = $wrappedRequest->getTransactionInitRequest();

        $amount = (float) $request->getOrderDetails()->getOrderValue();

        $authorizedAmount = (float) $this->storage->get('authorized_amount');
        $interestAmount = (float) $this->storage->get('interest_amount');

        if (
            $authorizedAmount > 0
            && $interestAmount > 0
            && round($amount, 2) != round($authorizedAmount + $interestAmount, 2)
        ) {
            $this->logger->debug('amount not valid: '.$amount.' (amount) !== '.$authorizedAmount.' (authorized) + '.$interestAmount.' (interest)');
            return false;
        }
        return true;
    }

    public function isValid(TransactionInitRequestWrapper $wrappedRequest) {
        return $this->isAmountValid($wrappedRequest) &&
            $this->verifyAddress($wrappedRequest);
    }

    public function isPrefixValid($prefix) {
        return $this->prefixConverter->convert($prefix) !== null;
    }

    public function clear() {
        return $this->storage->clear();
    }
}
