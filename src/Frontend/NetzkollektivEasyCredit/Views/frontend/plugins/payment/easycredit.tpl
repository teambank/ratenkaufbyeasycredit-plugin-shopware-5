{if $EasyCreditError}
    <div id="easycredit-error"> 
        {$EasyCreditError}
    </div>

    {if $EasyCreditThemeVersion == 2}
    <script>
    jQuery(function($){
        {include file="frontend/common/payment_error_js.tpl"}
        checkEasycreditAvailable();
    });
    </script>
    {/if}
{else}

    <div id="easycredit-description">

        <p>Datenübermittlung Ratenkauf by easyCredit</p>
        <p>
            <label for="easycredit-agreement" class="easycredit-agreement {if $error_flags.sEasycreditAgreement}instyle_error{/if}">
                    <input type="checkbox" name="sEasycreditAgreement" class="required-entry" {if $payment_mean.id == $form_data.payment}required="required"{/if} id="easycredit-agreement" /> 
                    <span>Ja, ich möchte per Ratenkauf zahlen und willige ein, dass die {$EasyCreditPaymentCompanyName} der TeamBank AG (Partner der Genossenschaftlichen FinanzGruppe Volksbanken Raiffeisenbanken), Beuthener Str. 25, 90471 Nürnberg zur Identitäts- und Bonitätsprüfung sowie Betrugsprävention Anrede und Name, Geburtsdatum und -ort, Kontaktdaten (Adresse, Telefon, E-Mail) sowie Angaben zur aktuellen und zu früheren Bestellungen übermittelt und das Prüfungsergebnis zu diesem Zweck erhält.</span>
            </label>
        </p>

    </div>

    {if $EasyCreditThemeVersion == 2}
    <script>
        jQuery(function($){
            var ecDesc = $('#easycredit-description');
            var ecVal = ecDesc.closest('.method, .method_last').find('input.radio').val();
            var toggleEasycreditDescription = function(){
                if ($('input[name="register[payment]"]:checked').val() == ecVal) {
                    ecDesc.show().attr('required','required');
                } else {
                    ecDesc.hide().find('input').attr('required',null);
                }
            };

            $('input.radio[name="register[payment]"]')
                .each(toggleEasycreditDescription)
                .change(toggleEasycreditDescription);
        });
    </script>
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
