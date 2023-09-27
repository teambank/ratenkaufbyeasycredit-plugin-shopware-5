{block name="frontend_index_header_javascript_jquery"}
    {$smarty.block.parent}

    <script type="module" src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js"></script>
    <script type="text/javascript" src="{link file='frontend/_resources/js/easycredit.js'}"></script>

{/block}

{block name="frontend_detail_buy"}
    {$smarty.block.parent}

    {if $EasyCreditExpressProduct}
        {include file='frontend/plugins/payment/easycredit/express-button.tpl'}
    {/if}
{/block}

{block name='frontend_detail_data_price_info'}
    {$smarty.block.parent}

    {if isset($EasyCreditApiKey)}
    <easycredit-widget webshop-id="{$EasyCreditApiKey}" amount="{$sArticle.price}"></easycredit-widget>
    {/if}
{/block}

{block name="frontend_register_payment_fieldset_description"}
    {$smarty.block.parent}

    {if $payment_mean.name == 'easycredit'}
    <easycredit-logo></easycredit-logo>
    {/if}
{/block}

{block name="frontend_checkout_payment_fieldset_description"}
    {if $payment_mean.name == 'easycredit'}
    <div class="grid_10 last">
        <easycredit-checkout-label></easycredit-checkout-label>
    </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_cart_deliveryfree"}
    {if $EasyCreditExpressCart}
        {include file='frontend/plugins/payment/easycredit/express-button.tpl'}
    {/if}

    {$smarty.block.parent}
{/block}

{block name='frontend_index_body_inline'}
    {$smarty.block.parent}

    {block name='frontend_index_easycredit_marketing_bar'}
        {if $EasyCreditMarketingBar}
            <easycredit-box-top></easycredit-box-top>
        {/if}
    {/block}
{/block}

{block name='frontend_index_footer'}
    {$smarty.block.parent}

    {block name='frontend_index_easycredit_marketing_modal'}
        {if $EasyCreditMarketingModal}
            <easycredit-box-modal is-open="{$EasyCreditMarketingModalIsOpen}" snooze-for="{$EasyCreditMarketingModalSettingsSnoozeFor}" delay="{$EasyCreditMarketingModalSettingsDelay}" src="{$EasyCreditMarketingModalSettingsMedia}"></easycredit-box-modal>
        {/if}
    {/block}

    {block name='frontend_index_easycredit_marketing_flashbox'}
        {if $EasyCreditMarketingFlashbox}
            <easycredit-box-flash is-open="false" src="{$EasyCreditMarketingFlashboxSettingsMedia}"></easycredit-box-flash>
        {/if}
    {/block}
{/block}
