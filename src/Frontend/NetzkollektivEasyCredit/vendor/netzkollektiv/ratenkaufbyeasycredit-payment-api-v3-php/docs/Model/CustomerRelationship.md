# # CustomerRelationship

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**customerStatus** | **string** | NEW_CUSTOMER &#x3D; NEUKUNDE, EXISTING_CUSTOMER &#x3D; BESTANDSKUNDE, PREMIUM_CUSTOMER &#x3D; PREMIUMKUNDE | [optional]
**customerSince** | **\DateTime** |  | [optional]
**orderDoneWithLogin** | **bool** | true if the order was placed via customer login | [optional]
**numberOfOrders** | **int** |  | [optional]
**negativePaymentInformation** | **string** | Indicates whether the customer has already been in late payment or has not made the payment -&gt; NO_PAYMENT_DISRUPTION &#x3D; KEINE_ZAHLUNGSSTOERUNGEN, PAYMENT_DELAY &#x3D; ZAHLUNGSVERZOEGERUNG, PAYMENT_NOT_DONE &#x3D; ZAHLUNGSAUSFALL, NO_INFORMATION &#x3D; KEINE_INFORMATION | [optional]
**riskyItemsInShoppingCart** | **bool** | risikoartikelImWarenkorb | [optional]
**logisticsServiceProvider** | **string** | Logistics service provider for the delivery of the order | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
