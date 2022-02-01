# Teambank\RatenkaufByEasyCreditApiV3\ServiceintegrationApi

All URIs are relative to https://partner.easycredit-ratenkauf.de.

Method | HTTP request | Description
------------- | ------------- | -------------
[**apiPaymentSiV3IdentityPost()**](ServiceintegrationApi.md#apiPaymentSiV3IdentityPost) | **POST** /api/payment/si/v3/identity | create an identity token (device ident ID) which must be used in the payment process
[**apiPaymentSiV3TransactionPreAuthorizationPost()**](ServiceintegrationApi.md#apiPaymentSiV3TransactionPreAuthorizationPost) | **POST** /api/payment/si/v3/transaction/preAuthorization | preAuthorizes a transaction
[**apiPaymentSiV3TransactionTechnicalTransactionIdGet()**](ServiceintegrationApi.md#apiPaymentSiV3TransactionTechnicalTransactionIdGet) | **GET** /api/payment/si/v3/transaction/{technicalTransactionId} | Get the necessary information about the transaction
[**apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost()**](ServiceintegrationApi.md#apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost) | **POST** /api/payment/si/v3/transaction/{technicalTransactionId}/mtan | transmit the customers mtan
[**apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost()**](ServiceintegrationApi.md#apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost) | **POST** /api/payment/si/v3/transaction/{technicalTransactionId}/mtan/resend | send new mtan to customer


## `apiPaymentSiV3IdentityPost()`

```php
apiPaymentSiV3IdentityPost(): \Teambank\RatenkaufByEasyCreditApiV3\Model\DeviceIdentToken
```

create an identity token (device ident ID) which must be used in the payment process

' '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\ServiceintegrationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->apiPaymentSiV3IdentityPost();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServiceintegrationApi->apiPaymentSiV3IdentityPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\DeviceIdentToken**](../Model/DeviceIdentToken.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/hal+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentSiV3TransactionPreAuthorizationPost()`

```php
apiPaymentSiV3TransactionPreAuthorizationPost($transactionSI): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInitResponse
```

preAuthorizes a transaction

' The preAuthorization of a transaction will trigger the initialization and the decision process. It additionally requires a device-ident token. It returns the decision object with the information if the mobile tan process is required. '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\ServiceintegrationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$transactionSI = new \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSI(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSI | preAuthorization request

try {
    $result = $apiInstance->apiPaymentSiV3TransactionPreAuthorizationPost($transactionSI);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServiceintegrationApi->apiPaymentSiV3TransactionPreAuthorizationPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **transactionSI** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSI**](../Model/TransactionSI.md)| preAuthorization request | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInitResponse**](../Model/TransactionInitResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/hal+json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentSiV3TransactionTechnicalTransactionIdGet()`

```php
apiPaymentSiV3TransactionTechnicalTransactionIdGet($technicalTransactionId): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation
```

Get the necessary information about the transaction

Based on the unique TeamBank identifier, transaction's specific data is returned. Supports body signature.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\ServiceintegrationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier

try {
    $result = $apiInstance->apiPaymentSiV3TransactionTechnicalTransactionIdGet($technicalTransactionId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServiceintegrationApi->apiPaymentSiV3TransactionTechnicalTransactionIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **technicalTransactionId** | **string**| Unique TeamBank transaction identifier |

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation**](../Model/TransactionInformation.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/hal+json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost()`

```php
apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost($technicalTransactionId, $transmitMtan): \Teambank\RatenkaufByEasyCreditApiV3\Model\MTan
```

transmit the customers mtan

' 200: finished 400: mtan is not correct 409: no remaining attempts '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\ServiceintegrationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier
$transmitMtan = new \Teambank\RatenkaufByEasyCreditApiV3\Model\TransmitMtan(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\TransmitMtan | mtan request

try {
    $result = $apiInstance->apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost($technicalTransactionId, $transmitMtan);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServiceintegrationApi->apiPaymentSiV3TransactionTechnicalTransactionIdMtanPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **technicalTransactionId** | **string**| Unique TeamBank transaction identifier |
 **transmitMtan** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransmitMtan**](../Model/TransmitMtan.md)| mtan request | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\MTan**](../Model/MTan.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost()`

```php
apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost($technicalTransactionId)
```

send new mtan to customer

' '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\ServiceintegrationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier

try {
    $apiInstance->apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost($technicalTransactionId);
} catch (Exception $e) {
    echo 'Exception when calling ServiceintegrationApi->apiPaymentSiV3TransactionTechnicalTransactionIdMtanResendPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **technicalTransactionId** | **string**| Unique TeamBank transaction identifier |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
