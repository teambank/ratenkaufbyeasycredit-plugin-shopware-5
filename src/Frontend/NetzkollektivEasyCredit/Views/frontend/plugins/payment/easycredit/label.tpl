<div class="method--label is--first easycredit-method">
    <label class="method--name is--strong" for="payment_mean{$payment_mean.id}">
        <easycredit-checkout-label
            label="{$payment_mean.description}"
            {if $payment_mean.name == 'easycredit_ratenkauf'}
            payment-type="INSTALLMENT"
            {else if $payment_mean.name == 'easycredit_rechnung'}
            payment-type="BILL"
            {/if}
        ></easycredit-checkout-label>
    </label>
</div>
