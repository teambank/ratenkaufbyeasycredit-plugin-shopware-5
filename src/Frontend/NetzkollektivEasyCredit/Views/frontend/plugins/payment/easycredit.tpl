{if $Controller == "checkout"}
    {if $EasyCreditAddressError}
        <div data-easycredit-address-editor="true" data-sessionKey="checkoutShippingAddressId" data-id="{$EasyCreditActiveBillingAddressId}"></div>
    {/if}
    <easycredit-checkout webshop-id="{$EasyCreditApiKey}" amount="250" is-active="true" alert="{$EasyCreditError}" />
{/if}
