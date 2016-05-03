{debug}
{if $EasyCreditPaymentShowRedemption}
    <div class="tos--panel panel has--border dispatch-methods">
        <div class="panel--title primary is--underline">
            {if $EasyCreditThemeVersion == 3}
            <h3 class="underline">easyCredit Tilgungsplan</h3>
            {else}
            easyCredit Tilgungsplan 
            {/if}
        </div>
        <div class="panel--body is--wide">
            {$EasyCreditPaymentRedemptionPlan}
            <br /><br />
            <a href="{$EasyCreditPaymentPreContractInformationUrl}" target="_blank">Vorvertragliche Informationen zum Ratenkauf hier abrufen</a>
        </div>
    </div>
{/if}
