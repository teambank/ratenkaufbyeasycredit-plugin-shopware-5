# Teambank\RatenkaufByEasyCreditApiV3\TransactionApi

All URIs are relative to https://partner.easycredit-ratenkauf.de.

Method | HTTP request | Description
------------- | ------------- | -------------
[**apiMerchantV3TransactionGet()**](TransactionApi.md#apiMerchantV3TransactionGet) | **GET** /api/merchant/v3/transaction | Find transactions of a merchant according to some search parameters.
[**apiMerchantV3TransactionTransactionIdCapturePost()**](TransactionApi.md#apiMerchantV3TransactionTransactionIdCapturePost) | **POST** /api/merchant/v3/transaction/{transactionId}/capture | Report a capture for a transaction according to its unique functional identifier
[**apiMerchantV3TransactionTransactionIdGet()**](TransactionApi.md#apiMerchantV3TransactionTransactionIdGet) | **GET** /api/merchant/v3/transaction/{transactionId} | Retrieve a transaction of a merchant according to a unique functional identifier
[**apiMerchantV3TransactionTransactionIdRefundPost()**](TransactionApi.md#apiMerchantV3TransactionTransactionIdRefundPost) | **POST** /api/merchant/v3/transaction/{transactionId}/refund | Report a refund for a transaction according to its unique functional identifier
[**apiPaymentV3TransactionPost()**](TransactionApi.md#apiPaymentV3TransactionPost) | **POST** /api/payment/v3/transaction | Initiates a transaction based on the given request
[**apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost()**](TransactionApi.md#apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost) | **POST** /api/payment/v3/transaction/{technicalTransactionId}/authorization | Authorizes a transaction after finishing the process in a webshop
[**apiPaymentV3TransactionTechnicalTransactionIdGet()**](TransactionApi.md#apiPaymentV3TransactionTechnicalTransactionIdGet) | **GET** /api/payment/v3/transaction/{technicalTransactionId} | Get the necessary information about the transaction
[**apiPaymentV3TransactionTechnicalTransactionIdPatch()**](TransactionApi.md#apiPaymentV3TransactionTechnicalTransactionIdPatch) | **PATCH** /api/payment/v3/transaction/{technicalTransactionId} | Updates a transaction based on the given request


## `apiMerchantV3TransactionGet()`

```php
apiMerchantV3TransactionGet($firstname, $lastname, $orderId, $pageSize, $page, $status, $minOrderValue, $maxOrderValue, $tId): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionListInfo
```

Find transactions of a merchant according to some search parameters.

' Find transactions of a merchant according to the following search parameters: firstname, lastname, orderId, status (REPORT_CAPTURE, REPORT_CAPTURE_EXPIRING, IN_BILLING, BILLED, EXPIRED), minOrderValue, maxOrderValue. The search filter combines the search tags as follows: call /transaction?firstname=Ralf&minOrderValue=5000 -> find transactions with customers firstname Ralf and a minimum order value of 5000€ '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$firstname = Ralf; // string
$lastname = Ratenkauf; // string
$orderId = ABCDE; // string
$pageSize = 50; // int
$page = 0; // int
$status = array('status_example'); // string[]
$minOrderValue = 200; // float
$maxOrderValue = 9999.99; // float
$tId = EWZEN7; // string | Unique functional transaction identifier (consists of 6 characters) provided through the query

try {
    $result = $apiInstance->apiMerchantV3TransactionGet($firstname, $lastname, $orderId, $pageSize, $page, $status, $minOrderValue, $maxOrderValue, $tId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiMerchantV3TransactionGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **firstname** | **string**|  | [optional]
 **lastname** | **string**|  | [optional]
 **orderId** | **string**|  | [optional]
 **pageSize** | **int**|  | [optional] [default to 100]
 **page** | **int**|  | [optional]
 **status** | [**string[]**](../Model/string.md)|  | [optional]
 **minOrderValue** | **float**|  | [optional]
 **maxOrderValue** | **float**|  | [optional]
 **tId** | **string**| Unique functional transaction identifier (consists of 6 characters) provided through the query | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionListInfo**](../Model/TransactionListInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/hal+json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiMerchantV3TransactionTransactionIdCapturePost()`

```php
apiMerchantV3TransactionTransactionIdCapturePost($transactionId, $captureRequest)
```

Report a capture for a transaction according to its unique functional identifier

Reports a capture for the specified merchant's transaction. Only one capture can be reported per transaction.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$transactionId = EWZEN7; // string | Unique functional transaction identifier (consists of 6 characters)
$captureRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest | Capture Request Object

try {
    $apiInstance->apiMerchantV3TransactionTransactionIdCapturePost($transactionId, $captureRequest);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiMerchantV3TransactionTransactionIdCapturePost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **transactionId** | **string**| Unique functional transaction identifier (consists of 6 characters) |
 **captureRequest** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest**](../Model/CaptureRequest.md)| Capture Request Object | [optional]

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiMerchantV3TransactionTransactionIdGet()`

```php
apiMerchantV3TransactionTransactionIdGet($transactionId): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionResponse
```

Retrieve a transaction of a merchant according to a unique functional identifier

A single transaction is loaded according to its unique functional identifier.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$transactionId = EWZEN7; // string | Unique functional transaction identifier (consists of 6 characters)

try {
    $result = $apiInstance->apiMerchantV3TransactionTransactionIdGet($transactionId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiMerchantV3TransactionTransactionIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **transactionId** | **string**| Unique functional transaction identifier (consists of 6 characters) |

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionResponse**](../Model/TransactionResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/hal+json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiMerchantV3TransactionTransactionIdRefundPost()`

```php
apiMerchantV3TransactionTransactionIdRefundPost($transactionId, $refundRequest)
```

Report a refund for a transaction according to its unique functional identifier

Reports a refund for the specified merchant's transaction. Currently there can only be one active refund.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$transactionId = EWZEN7; // string | Unique functional transaction identifier (consists of 6 characters)
$refundRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest | Refund Request Object

try {
    $apiInstance->apiMerchantV3TransactionTransactionIdRefundPost($transactionId, $refundRequest);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiMerchantV3TransactionTransactionIdRefundPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **transactionId** | **string**| Unique functional transaction identifier (consists of 6 characters) |
 **refundRequest** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest**](../Model/RefundRequest.md)| Refund Request Object | [optional]

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentV3TransactionPost()`

```php
apiPaymentV3TransactionPost($transaction): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInitResponse
```

Initiates a transaction based on the given request

' A transaction is created with unique identifiers (a TeamBank identifier <technicalTransactionId> and a functional identifier <transactionId>). The data in the request is validated and normalised and, if necessary, corresponding error messages are returned. Supports body signature. '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$transaction = new \Teambank\RatenkaufByEasyCreditApiV3\Model\Transaction(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\Transaction | init request

try {
    $result = $apiInstance->apiPaymentV3TransactionPost($transaction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiPaymentV3TransactionPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **transaction** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\Transaction**](../Model/Transaction.md)| init request | [optional]

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

## `apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost()`

```php
apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost($technicalTransactionId, $authorizationRequest)
```

Authorizes a transaction after finishing the process in a webshop

' The authorization of a transaction will be triggered asynchronous. After processing all necessary checks there will be an optional callback to the url that was provided in initialization and you can get the updated status information. '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier
$authorizationRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Model\AuthorizationRequest(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\AuthorizationRequest | authorization request

try {
    $apiInstance->apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost($technicalTransactionId, $authorizationRequest);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiPaymentV3TransactionTechnicalTransactionIdAuthorizationPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **technicalTransactionId** | **string**| Unique TeamBank transaction identifier |
 **authorizationRequest** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\AuthorizationRequest**](../Model/AuthorizationRequest.md)| authorization request | [optional]

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentV3TransactionTechnicalTransactionIdGet()`

```php
apiPaymentV3TransactionTechnicalTransactionIdGet($technicalTransactionId): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation
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


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier

try {
    $result = $apiInstance->apiPaymentV3TransactionTechnicalTransactionIdGet($technicalTransactionId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiPaymentV3TransactionTechnicalTransactionIdGet: ', $e->getMessage(), PHP_EOL;
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

## `apiPaymentV3TransactionTechnicalTransactionIdPatch()`

```php
apiPaymentV3TransactionTechnicalTransactionIdPatch($technicalTransactionId, $transactionUpdate): \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSummary
```

Updates a transaction based on the given request

Based on the unique TeamBank identifier, transaction's specific data is modified. Supports body signature.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\TransactionApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$technicalTransactionId = 'technicalTransactionId_example'; // string | Unique TeamBank transaction identifier
$transactionUpdate = new \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionUpdate(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionUpdate | update request

try {
    $result = $apiInstance->apiPaymentV3TransactionTechnicalTransactionIdPatch($technicalTransactionId, $transactionUpdate);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TransactionApi->apiPaymentV3TransactionTechnicalTransactionIdPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **technicalTransactionId** | **string**| Unique TeamBank transaction identifier |
 **transactionUpdate** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionUpdate**](../Model/TransactionUpdate.md)| update request | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionSummary**](../Model/TransactionSummary.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/hal+json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
