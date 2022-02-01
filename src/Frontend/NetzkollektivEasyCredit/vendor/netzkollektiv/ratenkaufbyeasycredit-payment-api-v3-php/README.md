# ratenkaufbyeasycredit-payment-api-v3-php

Transaction-V3 API for Merchant Portal


## Installation & Usage

### Requirements

PHP 7.2 and later.

### Composer

To install the bindings via [Composer](https://getcomposer.org/), add the following to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/netzkollektiv/ratenkaufbyeasycredit-payment-api-v3-php.git"
    }
  ],
  "require": {
    "netzkollektiv/ratenkaufbyeasycredit-payment-api-v3-php": "*@dev"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
<?php
require_once('/path/to/ratenkaufbyeasycredit-payment-api-v3-php/vendor/autoload.php');
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\InstallmentplanApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$shopIdentifier = 'shopIdentifier_example'; // string | Shop Identifier
$installmentPlanRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanRequest(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanRequest | integration check request

try {
    $result = $apiInstance->apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost($shopIdentifier, $installmentPlanRequest);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InstallmentplanApi->apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost: ', $e->getMessage(), PHP_EOL;
}

```

## API Endpoints

All URIs are relative to *https://partner.easycredit-ratenkauf.de*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*InstallmentplanApi* | [**apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost**](docs/Api/InstallmentplanApi.md#apiratenrechnerv3webshopshopidentifierinstallmentplanspost) | **POST** /api/ratenrechner/v3/webshop/{shopIdentifier}/installmentplans | Calculates the installmentplan
*ServiceintegrationApi* | [**apiPaymentSiV3IdentityPost**](docs/Api/ServiceintegrationApi.md#apipaymentsiv3identitypost) | **POST** /api/payment/si/v3/identity | create an identity token (device ident ID) which must be used in the payment process
*ServiceintegrationApi* | [**apiPaymentSiV3TransactionPreAuthorizationPost**](docs/Api/ServiceintegrationApi.md#apipaymentsiv3transactionpreauthorizationpost) | **POST** /api/payment/si/v3/transaction/preAuthorization | preAuthorizes a transaction
*ServiceintegrationApi* | [**apiPaymentSiV3TransactionTechnicalTransactionIdGet**](docs/Api/ServiceintegrationApi.md#apipaymentsiv3transactiontechnicaltransactionidget) | **GET** /api/payment/si/v3/transaction/{technicalTransactionId} | Get the necessary information about the transaction
*ServiceintegrationApi* | [**apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost**](docs/Api/ServiceintegrationApi.md#apipaymentsiv3transactiontechnicaltransactionidmtanpost) | **POST** /api/payment/si/v3/transaction/{technicalTransactionId}/mtan | transmit the customers mtan
*ServiceintegrationApi* | [**apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost**](docs/Api/ServiceintegrationApi.md#apipaymentsiv3transactiontechnicaltransactionidmtanresendpost) | **POST** /api/payment/si/v3/transaction/{technicalTransactionId}/mtan/resend | send new mtan to customer
*TransactionApi* | [**apiMerchantV3TransactionGet**](docs/Api/TransactionApi.md#apimerchantv3transactionget) | **GET** /api/merchant/v3/transaction | Find transactions of a merchant according to some search parameters.
*TransactionApi* | [**apiMerchantV3TransactionTransactionIdCapturePost**](docs/Api/TransactionApi.md#apimerchantv3transactiontransactionidcapturepost) | **POST** /api/merchant/v3/transaction/{transactionId}/capture | Report a capture for a transaction according to its unique functional identifier
*TransactionApi* | [**apiMerchantV3TransactionTransactionIdGet**](docs/Api/TransactionApi.md#apimerchantv3transactiontransactionidget) | **GET** /api/merchant/v3/transaction/{transactionId} | Retrieve a transaction of a merchant according to a unique functional identifier
*TransactionApi* | [**apiMerchantV3TransactionTransactionIdRefundPost**](docs/Api/TransactionApi.md#apimerchantv3transactiontransactionidrefundpost) | **POST** /api/merchant/v3/transaction/{transactionId}/refund | Report a refund for a transaction according to its unique functional identifier
*TransactionApi* | [**apiPaymentV3TransactionPost**](docs/Api/TransactionApi.md#apipaymentv3transactionpost) | **POST** /api/payment/v3/transaction | Initiates a transaction based on the given request
*TransactionApi* | [**apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost**](docs/Api/TransactionApi.md#apipaymentv3transactiontechnicaltransactionidauthorizationpost) | **POST** /api/payment/v3/transaction/{technicalTransactionId}/authorization | Authorizes a transaction after finishing the process in a webshop
*TransactionApi* | [**apiPaymentV3TransactionTechnicalTransactionIdGet**](docs/Api/TransactionApi.md#apipaymentv3transactiontechnicaltransactionidget) | **GET** /api/payment/v3/transaction/{technicalTransactionId} | Get the necessary information about the transaction
*TransactionApi* | [**apiPaymentV3TransactionTechnicalTransactionIdPatch**](docs/Api/TransactionApi.md#apipaymentv3transactiontechnicaltransactionidpatch) | **PATCH** /api/payment/v3/transaction/{technicalTransactionId} | Updates a transaction based on the given request
*WebshopApi* | [**apiPaymentV3WebshopGet**](docs/Api/WebshopApi.md#apipaymentv3webshopget) | **GET** /api/payment/v3/webshop | Get the necessary information about the webshop
*WebshopApi* | [**apiPaymentV3WebshopIntegrationcheckPost**](docs/Api/WebshopApi.md#apipaymentv3webshopintegrationcheckpost) | **POST** /api/payment/v3/webshop/integrationcheck | Verifies the correctness of the merchant&#39;s authentication credentials and, if enabled, the body signature
*WebshopApi* | [**apiPaymentV3WebshopWebshopIdGet**](docs/Api/WebshopApi.md#apipaymentv3webshopwebshopidget) | **GET** /api/payment/v3/webshop/{webshopId} | Get the necessary information about the webshop

## Models

- [Address](docs/Model/Address.md)
- [Article](docs/Model/Article.md)
- [ArticleNumberItem](docs/Model/ArticleNumberItem.md)
- [AuthenticationError](docs/Model/AuthenticationError.md)
- [AuthorizationRequest](docs/Model/AuthorizationRequest.md)
- [AuthorizationStatusResponse](docs/Model/AuthorizationStatusResponse.md)
- [Bank](docs/Model/Bank.md)
- [BankAccountCheck](docs/Model/BankAccountCheck.md)
- [Booking](docs/Model/Booking.md)
- [CaptureRequest](docs/Model/CaptureRequest.md)
- [Consent](docs/Model/Consent.md)
- [ConstraintViolation](docs/Model/ConstraintViolation.md)
- [ConstraintViolationViolations](docs/Model/ConstraintViolationViolations.md)
- [Contact](docs/Model/Contact.md)
- [Customer](docs/Model/Customer.md)
- [CustomerRelationship](docs/Model/CustomerRelationship.md)
- [DeviceIdentToken](docs/Model/DeviceIdentToken.md)
- [Employment](docs/Model/Employment.md)
- [InstallmentPlan](docs/Model/InstallmentPlan.md)
- [InstallmentPlanRequest](docs/Model/InstallmentPlanRequest.md)
- [InstallmentPlanResponse](docs/Model/InstallmentPlanResponse.md)
- [IntegrationCheckRequest](docs/Model/IntegrationCheckRequest.md)
- [IntegrationCheckResponse](docs/Model/IntegrationCheckResponse.md)
- [InvoiceAddress](docs/Model/InvoiceAddress.md)
- [InvoiceAddressAllOf](docs/Model/InvoiceAddressAllOf.md)
- [MTan](docs/Model/MTan.md)
- [OrderDetails](docs/Model/OrderDetails.md)
- [PaginationInfo](docs/Model/PaginationInfo.md)
- [Plan](docs/Model/Plan.md)
- [RedirectLinks](docs/Model/RedirectLinks.md)
- [Refund](docs/Model/Refund.md)
- [RefundRequest](docs/Model/RefundRequest.md)
- [ServerError](docs/Model/ServerError.md)
- [ShippingAddress](docs/Model/ShippingAddress.md)
- [ShippingAddressAllOf](docs/Model/ShippingAddressAllOf.md)
- [ShoppingCartInformationItem](docs/Model/ShoppingCartInformationItem.md)
- [Shopsystem](docs/Model/Shopsystem.md)
- [Transaction](docs/Model/Transaction.md)
- [TransactionCustomer](docs/Model/TransactionCustomer.md)
- [TransactionInformation](docs/Model/TransactionInformation.md)
- [TransactionInitResponse](docs/Model/TransactionInitResponse.md)
- [TransactionListInfo](docs/Model/TransactionListInfo.md)
- [TransactionOrderDetails](docs/Model/TransactionOrderDetails.md)
- [TransactionResponse](docs/Model/TransactionResponse.md)
- [TransactionSI](docs/Model/TransactionSI.md)
- [TransactionSIAllOf](docs/Model/TransactionSIAllOf.md)
- [TransactionSummary](docs/Model/TransactionSummary.md)
- [TransactionUpdate](docs/Model/TransactionUpdate.md)
- [TransmitMtan](docs/Model/TransmitMtan.md)
- [WebshopResponse](docs/Model/WebshopResponse.md)

## Authorization

### basicAuth

- **Type**: HTTP basic authentication

## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```

## Author



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `V3.84.0`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`
