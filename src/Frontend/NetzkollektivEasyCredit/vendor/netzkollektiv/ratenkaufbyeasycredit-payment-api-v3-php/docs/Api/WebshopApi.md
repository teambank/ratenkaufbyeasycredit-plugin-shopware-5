# Teambank\RatenkaufByEasyCreditApiV3\WebshopApi

All URIs are relative to https://partner.easycredit-ratenkauf.de.

Method | HTTP request | Description
------------- | ------------- | -------------
[**apiPaymentV3WebshopGet()**](WebshopApi.md#apiPaymentV3WebshopGet) | **GET** /api/payment/v3/webshop | Get the necessary information about the webshop
[**apiPaymentV3WebshopIntegrationcheckPost()**](WebshopApi.md#apiPaymentV3WebshopIntegrationcheckPost) | **POST** /api/payment/v3/webshop/integrationcheck | Verifies the correctness of the merchant&#39;s authentication credentials and, if enabled, the body signature
[**apiPaymentV3WebshopWebshopIdGet()**](WebshopApi.md#apiPaymentV3WebshopWebshopIdGet) | **GET** /api/payment/v3/webshop/{webshopId} | Get the necessary information about the webshop


## `apiPaymentV3WebshopGet()`

```php
apiPaymentV3WebshopGet(): \Teambank\RatenkaufByEasyCreditApiV3\Model\WebshopResponse
```

Get the necessary information about the webshop

' This API returns the necessary information of the corresponding webshop. This includes max and min financing amount, interest rate, test mode, current availability information and privacy approval form '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\WebshopApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->apiPaymentV3WebshopGet();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebshopApi->apiPaymentV3WebshopGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\WebshopResponse**](../Model/WebshopResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentV3WebshopIntegrationcheckPost()`

```php
apiPaymentV3WebshopIntegrationcheckPost($integrationCheckRequest): \Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckResponse
```

Verifies the correctness of the merchant's authentication credentials and, if enabled, the body signature

' After integrating the authentication credentials (basic auth: webshop id/ratenkauf token) and possibly enabling the body signature feature, the merchant can check its correctness. '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
              ->setHost('https://ratenkauf.easycredit.de')
              ->setUsername('1.de.1234.1') // use your "Webshop-ID"
              ->setPassword('YOUR_API_KEY'); // use your "API-Kennwort"


$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\WebshopApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$integrationCheckRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckRequest(); // \Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckRequest | integration check request

try {
    $result = $apiInstance->apiPaymentV3WebshopIntegrationcheckPost($integrationCheckRequest);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebshopApi->apiPaymentV3WebshopIntegrationcheckPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **integrationCheckRequest** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckRequest**](../Model/IntegrationCheckRequest.md)| integration check request | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\IntegrationCheckResponse**](../Model/IntegrationCheckResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiPaymentV3WebshopWebshopIdGet()`

```php
apiPaymentV3WebshopWebshopIdGet($webshopId): \Teambank\RatenkaufByEasyCreditApiV3\Model\WebshopResponse
```

Get the necessary information about the webshop

' This API returns the necessary information of the corresponding webshop. This includes max and min financing amount, interest rate, test mode, current availability information and privacy approval form '

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new Teambank\RatenkaufByEasyCreditApiV3\Api\WebshopApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$webshopId = 'webshopId_example'; // string | Identifier of a webshop

try {
    $result = $apiInstance->apiPaymentV3WebshopWebshopIdGet($webshopId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebshopApi->apiPaymentV3WebshopWebshopIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **webshopId** | **string**| Identifier of a webshop |

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\WebshopResponse**](../Model/WebshopResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
