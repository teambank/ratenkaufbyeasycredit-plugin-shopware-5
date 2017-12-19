{if $EasyCreditError}

    <div id="easycredit-error"> 
        {$EasyCreditError}
    </div>

{else}

    <div id="easycredit-description">
        <p>
            <label for="easycredit-agreement" class="easycredit-agreement {if $error_flags.sEasycreditAgreement}instyle_error{/if}">
                    <input type="checkbox" name="sEasycreditAgreement" class="required-entry" {if $payment_mean.id == $form_data.payment}required="required"{/if} id="easycredit-agreement" /> 
                    <span>{$EasyCreditAgreement}</span>
            </label>
        </p>

    </div>

{/if}
