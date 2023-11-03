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

{block name='frontend_checkout_confirm_left_payment_method'}

    {if !$sRegisterFinished && $sPayment.name === 'easycredit'}
        <form name="" method="POST" action="{url controller=account action=savePayment sTarget='checkout'}" class="payment">
            <div id="easycredt-skip-link" class="payment-display row-padding content-border">
                    <h2>{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm_left"}{/s}</h2>
                    <div class="row">
                            <easycredit-checkout-label></easycredit-checkout-label>
                            <p class="easycredit-info-description">
                                <easycredit-checkout
                                    webshop-id="{$EasyCreditApiKey}"
                                    amount="500"
                                    {if $EasyCreditPaymentPlan}payment-plan="{$EasyCreditPaymentPlan}"{/if}
                                ></easycredit-checkout>
                            </p>
                    </div>

                    {* Action buttons *}
                    <div class="actions">
                            <a href="{url controller=account action=payment sTarget=checkout}" class="button button-fullwidth">
                                    {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                    </div>
            </div>
        </form>

        {if !$EasyCreditPaymentPlan}
        <script>
        $('.basket-form').submit(function(e) {
            e.preventDefault();

            var url = location.href;
            location.href = '#easycredt-skip-link';
            history.replaceState(null,null,url);

            {* open privacy acceptance modal *}
            $('easycredit-checkout').get(0).dispatchEvent(new Event('openModal'));
            return false;
        });
        </script>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
