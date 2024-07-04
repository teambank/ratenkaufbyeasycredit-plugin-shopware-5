{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_left_payment_method'}
    {$smarty.block.parent}

    {if $EasyCreditPaymentPlan}
    <p class="easycredit-info-description">
        <easycredit-checkout 
            {if $sPayment.name == 'easycredit_ratenkauf'}
            payment-type="INSTALLMENT"
            {else if $sPayment.name == 'easycredit_rechnung'}
            payment-type="BILL"
            {/if}    
            payment-plan="{$EasyCreditPaymentPlan}"
        ></easycredit-checkout>
   </p>
   {/if}
{/block}

{block name='frontend_checkout_confirm_information_addresses_equal_panel_shipping_select_address'}
    {if !$EasyCreditDisableAddressChange}
        {$smarty.block.parent}
    {/if}
{/block}
