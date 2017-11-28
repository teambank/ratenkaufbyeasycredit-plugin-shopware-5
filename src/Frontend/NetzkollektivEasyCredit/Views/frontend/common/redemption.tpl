{if $EasyCreditPaymentShowRedemption}
    <div class="tos--panel panel has--border dispatch-methods">
        <div class="panel--title primary is--underline">
            {if $EasyCreditThemeVersion == 2}
            <h3 class="underline">ratenkauf by easyCredit Tilgungsplan</h3>
            {else}
            ratenkauf by easyCredit Tilgungsplan 
            {/if}
        </div>
        <div class="panel--body is--wide">
            {$EasyCreditPaymentRedemptionPlan}
            <br /><br />
            <a href="{$EasyCreditPaymentPreContractInformationUrl}" target="_blank" style="text-decoration:underline !important;">Vorvertragliche Informationen zum Ratenkauf hier abrufen</a>
        </div>
    </div>
{/if}
