{if $EasyCreditError}
    <div id="easycredit-error"> 
        {$EasyCreditError}
    </div>
{else}

    <div id="easycredit-description">

        <p>Datenübermittlung Ratenkauf by easyCredit</p>
        <p>
            <label for="easycredit-agreement" class="easycredit-agreement">
                    <input type="checkbox" class="required-entry" id="easycredit-agreement" /> 
                    <span>Ja, ich möchte per Ratenkauf zahlen und willige ein, dass die {$EasyCreditPaymentCompanyName} der TeamBank AG (Partner der Genossenschaftlichen FinanzGruppe Volksbanken Raiffeisenbanken), Beuthener Str. 25, 90471 Nürnberg zur Identitäts- und Bonitätsprüfung sowie Betrugsprävention Anrede und Name, Geburtsdatum und -ort, Kontaktdaten (Adresse, Telefon, E-Mail) sowie Angaben zur aktuellen und zu früheren Bestellungen übermittelt und das Prüfungsergebnis zu diesem Zweck erhält.</span>
            </label>
        </p>

    </div>

    {if $EasyCreditThemeVersion == 2}
    {block name="frontend_index_header_javascript_jquery_lib" append}
    <script>
        jQuery(function($){

            var easycreditVal = $('#easycredit-description').closest('.method').find('input.radio').val();
            var toggleEasycreditDescription = function(){
                var desc = $('#easycredit-description').hide();
                desc.find('input').attr('required',null);
                if ($(this).val()==easycreditVal) {
                    desc.show().attr('required','required');
                }
            };

            $('input.radio[name="register[payment]"]')
                .each(toggleEasycreditDescription)
                .change(toggleEasycreditDescription);
        });
    </script>
    {/block}
    {/if}

{/if}

<style>
#easycredit-description>img {
 width: 46px;
 height: 35px;
}
#easycredit-description label {
 display:table;
 width: auto;
 font-weight: normal;
}
#easycredit-description label>input, #easycredit-description label>span {
 display:table-cell;
}
#easycredit-description label>input {
 margin-right:15px;
}
img.easycredit-disabled {
 -webkit-filter:grayscale(100%); 
 filter:grayscale(100%);
}
</style>
