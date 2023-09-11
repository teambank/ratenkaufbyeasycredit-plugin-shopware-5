{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_left_payment_method'}
    {$smarty.block.parent}

    
    <p class="easycredit-info-description">
        <easycredit-checkout 
            webshop-id="{$EasyCreditApiKey}"
            amount="500"
            {if $EasyCreditPaymentPlan}payment-plan="{$EasyCreditPaymentPlan}"{/if} 
        ></easycredit-checkout>
   </p>
   
{/block}

{block name='frontend_checkout_confirm_information_addresses_equal_panel_shipping_select_address'}
    {if !$EasyCreditDisableAddressChange}
        {$smarty.block.parent}
    {/if}
{/block}
