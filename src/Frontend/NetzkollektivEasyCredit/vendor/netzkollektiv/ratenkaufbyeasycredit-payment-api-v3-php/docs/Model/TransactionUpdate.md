# # TransactionUpdate

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**orderValue** | **float** | Amount in € | [optional]
**numberOfProductsInShoppingCart** | **int** |  | [optional]
**orderId** | **string** | Shop transaction identifier (allows the shop to store its own reference for the transaction) | [optional]
**shoppingCartInformation** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem[]**](ShoppingCartInformationItem.md) |  | [optional]
**financingTerm** | **int** | &#39; Duration in months, depending on individual shop conditions and order value (please check your ratenkauf widget). Will be set to default value if not available. &#39; | [optional]
**contact** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\Contact**](Contact.md) |  | [optional]
**bank** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\Bank**](Bank.md) |  | [optional]
**customerRelationship** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship**](CustomerRelationship.md) |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
