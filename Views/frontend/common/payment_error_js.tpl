<script>
jQuery(function($){
    var checkEasycreditAvailable = function() {
        var error = $('#easycredit-error');
        if (error.length > 0) {
            var method = $('#easycredit-error').closest('.method');
            var radio = method.find('input.radio').get(0);
            radio.disabled = true;
            radio.checked = false;
            method.find('img').addClass('easycredit-disabled');
            method.find('.is--hidden').removeClass('is--hidden');
        }
    }
    checkEasycreditAvailable();
    $.subscribe('plugin/swShippingPayment/onInputChanged', checkEasycreditAvailable);
});
</script>
