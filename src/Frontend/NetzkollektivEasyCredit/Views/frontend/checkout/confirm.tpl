{extends file='parent:frontend/checkout/confirm.tpl'}
{block name='frontend_checkout_confirm_tos_panel'}
    {$smarty.block.parent}

    {include file="frontend/common/redemption.tpl"}
{/block}

{block name='frontend_checkout_confirm_left_payment_method'}
    {$smarty.block.parent}

    {if $EasyCreditPaymentPlan}
    <p class="easycredit-info-description">
        <span class="easycredit-info-logo">ratenkauf by easyCredit</span><br />
        <strong>Einfach. Fair. In Raten zahlen.</strong><br />
        {$EasyCreditPaymentPlan}
   </p>
   {/if}
{/block}

{block name='frontend_checkout_confirm_information_addresses_equal_panel_shipping_select_address'}
    {if !$EasyCreditDisableAddressChange}
        {$smarty.block.parent}
    {/if}
{/block}
