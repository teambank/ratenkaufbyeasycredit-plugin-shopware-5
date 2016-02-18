{extends file="parent:frontend/checkout/change_payment.tpl"}

{block name='frontend_checkout_payment_fieldset_input_radio'}
    {if $EasyCreditPaymentDisabled && $payment_mean.name == 'easycredit'}
        <div class="method--input">
            <input disabled="disabled" type="radio" name="payment" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" />
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_payment_fieldset_description'}
    {if $EasyCreditPaymentDisabled && $payment_mean.name == 'easycredit'}
        <div class="method--description is--last">
            {include file="string:{$EasyCreditDescriptionDisabled}"}
        </div>
    {elseif $EasyCreditPaymentSelected && $payment_mean.name == 'easycredit'}
        <div class="method--description is--last">
            {include file="string:{$EasyCreditDescriptionSelected}"}
            <br />
            <input required="required" aria-required="true" type="checkbox" class="required-entry" id="easycredit-agreement" />
            <label for="easycredit-agreement">
                 Ja, ich möchte per Ratenkauf zahlen und willige ein, dass die {$sShopname} der TeamBank AG (Partner der Genossenschaftlichen FinanzGruppe Volksbanken Raiffeisenbanken), Beuthener Str. 25, 90471 Nürnberg zur Identitäts- und Bonitätsprüfung sowie Betrugsprävention Anrede und Name, Geburtsdatum und -ort, Kontaktdaten (Adresse, Telefon, E-Mail) sowie Angaben zur aktuellen und zu früheren Bestellungen übermittelt und das Prüfungsergebnis zu diesem Zweck erhält.
            </label>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}