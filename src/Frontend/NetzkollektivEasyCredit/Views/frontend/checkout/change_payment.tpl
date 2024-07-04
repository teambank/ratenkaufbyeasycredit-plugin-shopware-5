{extends file="parent:frontend/checkout/change_payment.tpl"}

{block name="frontend_checkout_payment_fieldset_input_label"}
    {if $payment_mean.name|strpos:"easycredit" !== false}
        {include file="frontend/plugins/payment/easycredit/label.tpl"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
