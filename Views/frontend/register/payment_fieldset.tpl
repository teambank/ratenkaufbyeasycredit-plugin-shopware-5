{* used only in Shopware 4.x.x *}
{debug}
{extends file="parent:frontend/register/payment_fieldset.tpl"}

{block name='frontend_register_payment_fieldset_input_radio'}
    {if $EasyCreditPaymentDisabled && $payment_mean.name == 'easycredit'}
        <div class="method--input">
            <input disabled="disabled" type="radio" name="payment" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" />
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_register_payment_fieldset_description'}
    {if $payment_mean.name == 'easycredit'}
        <div class="method--description is--last">
            {include file="string:{$EasyCreditDescription}"}

            {if $EasyCreditPaymentSelected}
            <br />
            <input required="required" aria-required="true" type="checkbox" class="required-entry" id="easycredit-agreement" />
            <label for="easycredit-agreement">
                 Ja, ich möchte per Ratenkauf zahlen und willige ein, dass die {$EasyCreditPaymentCompanyName} der TeamBank AG (Partner der Genossenschaftlichen FinanzGruppe Volksbanken Raiffeisenbanken),
Beuthener Str. 25, 90471 Nürnberg zur Identitäts- und Bonitätsprüfung sowie Betrugsprävention Anrede und Name, Geburtsdatum und -ort, Kontaktdaten (Adresse, Telefon, E-Mail) sowie Angaben zur aktuellen
 und zu früheren Bestellungen übermittelt und das Prüfungsergebnis zu diesem Zweck erhält.
            </label>
            {/if}

        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
