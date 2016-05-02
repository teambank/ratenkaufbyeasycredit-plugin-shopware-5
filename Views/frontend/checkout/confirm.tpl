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
    {if $EasyCreditError}
        <div class="alert is--error is--rounded">
            <div class="alert--icon">
                <i class="icon--element icon--cross"></i>
            </div>
            <div class="alert--content">
                {if $EasyCreditAPIError}
                    <p>{$EasyCreditAPIErrorMessage}</p>
                {/if}
                {if $EasyCreditNewRates}
                    <p>EasyCredit Raten müssen neu berechnet werden. Bitte wählen Sie erneut die Zahlungsart <strong>Ratenkauf by easyCredit</strong>.</p>
                {/if}
            </div>
        </div>
    {/if}
{/block}

{block name='frontend_checkout_confirm_left_payment_method}
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
    {if $EasyCreditError}
        <div class="alert is--error is--rounded">
            <div class="alert--icon">
                <i class="icon--element icon--cross"></i>
            </div>
            <div class="alert--content">
                {if $EasyCreditAPIError}
                    <p>{$EasyCreditAPIErrorMessage}</p>
                {/if}
                {if $EasyCreditNewRates}
                    <p>EasyCredit Raten müssen neu berechnet werden. Bitte wählen Sie erneut die Zahlungsart <strong>Ratenkauf by easyCredit</strong>.</p>
                {/if}
            </div>
        </div>
    {/if}
{/block}
