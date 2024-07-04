{if $Controller == "checkout"}
    {if $EasyCreditAddressError}
        <div data-easycredit-address-editor="true" data-sessionKey="checkoutShippingAddressId" data-id="{$EasyCreditActiveBillingAddressId}"></div>
    {/if}
    {if $EasyCreditSelectedPaymentId == $payment_mean.id}
    <easycredit-checkout 
        webshop-id="{$EasyCreditApiKey}" 
        amount="{$EasyCreditAmount}" 
        {if $payment_mean.name == 'easycredit_ratenkauf'}
        payment-type="INSTALLMENT"
        {else if $payment_mean.name == 'easycredit_rechnung'}
        payment-type="BILL"
        {/if}
        is-active="true" 
        alert="{$EasyCreditError}"
        payment-plan="{$EasyCreditPaymentPlan}"
        {if $EasyCreditDisableFlexprice}disable-flexprice="true"{/if}
    />
    {/if}
{/if}
