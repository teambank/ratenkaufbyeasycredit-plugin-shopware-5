jQuery(function($) {
    var onHydrated = function (selector, cb) {
        if (!document.querySelector(selector)) {
            return
        }
        
        window.setTimeout(function() {
            var el = document.querySelector(selector);
            if (!el.classList.contains('hydrated')) {
                return onHydrated(selector, cb);
            }
            cb(el);
        }, 50)
    }

    var container = $('.easycredit-express-button-container');
    onHydrated('.easycredit-express-button-container easycredit-express-button', function (expressButton) {
        container.on('submit', expressButton, function(event) {
            event.preventDefault();

            var form = $('<form>')
                .attr('action', container.data('url'))
                .hide();

            var addToBasketForm = $(this).closest('#buybox').find('form[name=sAddToBasket]');
            if (addToBasketForm.length > 0) {
                form.attr('method','post');
                var formData = new FormData(addToBasketForm.get(0));

                for (var key of formData.keys()) {
                    form.append($('<input>').attr('name',key).attr('value', formData.get(key)));
                }
            }

            $('body').append(form);
            form.submit();
        });
    });

    var handleShippingPaymentConfirm = function () {
        onHydrated('easycredit-checkout', function (easyCreditCheckout) {
            $(easyCreditCheckout, "form.payment[action*='savePayment']").submit(function(e){
                var form = $("form.payment[action*='savePayment']")
                form.append('<input type="hidden" name="easycredit[submit]" value="1" />')
                if (!e.detail) {
                    e.detail = e.originalEvent.detail;
                }
                if (e.detail && e.detail.numberOfInstallments) {
                    form.append('<input type="hidden" name="easycredit[number-of-installments]" value="'+ e.detail.numberOfInstallments +'" />')
                }
                form.submit();

                return false;
            });
            $('form[action$="checkout/payment"]').submit(function() {
                if (!$(easyCreditCheckout).closest('div.method').find('input[type=radio][name="register[payment]"]').is(':checked')) {
                    return true;
                }
                if ($(easyCreditCheckout).prop('paymentPlan')
                    || $(easyCreditCheckout).prop('alert')
                ) {
                    return true;
                }
                if ($(this).find('input[name="easycredit[submit]"]').length > 0) {
                    return true;
                }
                
                easyCreditCheckout
                    .dispatchEvent(new Event('openModal'));
                return false;
            }); 
        });
    }
    handleShippingPaymentConfirm();
});
