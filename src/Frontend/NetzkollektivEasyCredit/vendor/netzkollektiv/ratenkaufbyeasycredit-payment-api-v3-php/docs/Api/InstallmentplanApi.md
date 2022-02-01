# Teambank\RatenkaufByEasyCreditApiV3\InstallmentplanApi

All URIs are relative to https://partner.easycredit-ratenkauf.de.

Method | HTTP request | Description
------------- | ------------- | -------------
[**apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost()**](InstallmentplanApi.md#apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost) | **POST** /api/ratenrechner/v3/webshop/{shopIdentifier}/installmentplans | Calculates the installmentplan


## `apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost()`

```php
apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost($shopIdentifier, $installmentPlanRequest): \Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanResponse
```

Calculates the installmentplan

' Calculates the installmentplan for every article. '

### Example

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

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **shopIdentifier** | **string**| Shop Identifier |
 **installmentPlanRequest** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanRequest**](../Model/InstallmentPlanRequest.md)| integration check request | [optional]

### Return type

[**\Teambank\RatenkaufByEasyCreditApiV3\Model\InstallmentPlanResponse**](../Model/InstallmentPlanResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/problem+json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
