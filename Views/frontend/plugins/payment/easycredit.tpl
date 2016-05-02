<div id="easycredit-description">
    {if $EasyCreditError}

    {$EasyCreditError}
    <script>
    var method = $('#easycredit-description').closest('.method');
    var radio = method.find('input.radio').get(0);
    radio.disabled = true;
    radio.checked = false;
    method.find('img').addClass('easycredit-disabled');
    </script>

    {else}

    <input required="required" aria-required="true" type="checkbox" class="required-entry" id="easycredit-agreement" />
    <label for="easycredit-agreement">
         Ja, ich möchte per Ratenkauf zahlen und willige ein, dass die {$EasyCreditPaymentCompanyName} der TeamBank AG (Partner der Genossenschaftlichen FinanzGruppe Volksbanken Raiffeisenbanken),
Beuthener Str. 25, 90471 Nürnberg zur Identitäts- und Bonitätsprüfung sowie Betrugsprävention Anrede und Name, Geburtsdatum und -ort, Kontaktdaten (Adresse, Telefon, E-Mail) sowie Angaben zur aktuellen
und zu früheren Bestellungen übermittelt und das Prüfungsergebnis zu diesem Zweck erhält.
    </label>

    {/if}
</div>
<style>
#easycredit-description label {
  width: auto !important;
  height: auto !important;
  display:block !important;
  font-weight: normal !important;
}
#easycredit-description>img {
 width: 46px;
 height: 35px;
}
img.easycredit-disabled {
 -webkit-filter:grayscale(100%); 
 filter:grayscale(100%);
}
</style>
