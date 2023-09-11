{if $Controller == "checkout"}
    {if $EasyCreditAddressError}
        <div data-easycredit-address-editor="true" data-sessionKey="checkoutShippingAddressId" data-id="{$EasyCreditActiveBillingAddressId}"></div>
    {/if}
    <easycredit-checkout 
        {if $EasyCreditApiKey}webshop-id="{$EasyCreditApiKey}"{/if}
        {if $EasyCreditAmount}amount="{$EasyCreditAmount}"s{/if}
        {if $EasyCreditIsSelected}is-active="{$EasyCreditIsSelected}"{/if}
        {if $EasyCreditError}alert="{$EasyCreditError}"{/if}
        {if $EasyCreditPaymentPlan}payment-plan="{$EasyCreditPaymentPlan}"{/if}
        {if $EasyCreditDisableFlexprice}disable-flexprice="true"{/if}
    />
{/if}
