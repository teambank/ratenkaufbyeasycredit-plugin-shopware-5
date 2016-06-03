{extends file='parent:frontend/checkout/shipping_payment.tpl'}

{block name="frontend_index_header_javascript_jquery_lib" append}

<script>
jQuery(function($){
    {include file="frontend/common/payment_error_js.tpl"}

    checkEasycreditAvailable();
    $.subscribe(
        'plugin/swShippingPayment/onInputChanged', 
        checkEasycreditAvailable
    );
});
</script>
{/block}

{block name="frontend_index_content" prepend}
    {if $sBasketInfo != ''}
    <div style="margin-top:10px;">
        {include file="frontend/_includes/messages.tpl" type="error" content=$sBasketInfo}
    </div>
    {/if}
{/block}
