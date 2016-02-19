{extends file="parent:frontend/checkout/confirm.tpl"}

{block name='frontend_checkout_confirm_tos_panel'}
    {$smarty.block.parent}
    {if $EasyCreditPaymentShowRedemption}
        <div class="tos--panel panel has--border">
            <div class="panel--title primary is--underline">
                easyCredit Tilgungsplan
            </div>
            <div class="panel--body is--wide">
                {$EasyCreditPaymentRedemptionPlan}
                <br /><br />
                <a href="{$EasyCreditPaymentPreContractInformationUrl}" target="_blank">Vorvertragliche Informationen zum Ratenkauf hier abrufen</a>
            </div>
        </div>
    {/if}
{/block}