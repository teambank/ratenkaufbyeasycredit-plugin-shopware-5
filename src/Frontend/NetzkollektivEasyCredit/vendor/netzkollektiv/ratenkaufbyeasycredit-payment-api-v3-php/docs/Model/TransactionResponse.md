# # TransactionResponse

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**transactionId** | **string** | Unique functional transaction identifier (consists of 6 characters) | [optional]
**status** | **string** | Status structure &#x3D; &lt;Merchant-Transaction-Status&gt;_&lt;Booking-Status&gt; -&gt; Merchant-Transaction-Status are REPORT_CAPTURE (LIEFERUNG_MELDEN), REPORT_CAPTURE_EXPIRING (LIEFERUNG_MELDEN_AUSLAUFEND), IN_BILLING (IN_ABRECHNUNG), BILLED (ABGERECHNET), EXPIRED (ABGELAUFEN). Applicable Booking-Status for this scenario are FAILED, PENDING | [optional]
**message** | **string** | error message whenever a booking (e.g REFUND/CAPTURE) related to a transaction fails | [optional]
**bookings** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\Booking[]**](Booking.md) |  | [optional]
**customer** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionCustomer**](TransactionCustomer.md) |  | [optional]
**creditAccountNumber** | **string** | (&#x3D; kreditKontonummer) | [optional]
**orderDetails** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionOrderDetails**](TransactionOrderDetails.md) |  | [optional]
**refundDetails** | [**\Teambank\RatenkaufByEasyCreditApiV3\Model\Refund[]**](Refund.md) |  | [optional]
**refundsTotalValue** | **float** | Sum of all the refund amounts in € | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
