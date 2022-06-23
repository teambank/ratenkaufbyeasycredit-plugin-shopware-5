(function () {
    var webshopId = $('meta[name=easycredit-api-key]').attr('content');

    var onHydrated = function (selector, cb) {
        if (!document.querySelector(selector)) {
            return
        }
        
        window.setTimeout(function() {
            if (!document.querySelector(selector).classList.contains('hydrated')) {
                return onHydrated(selector, cb);
            }
            cb();
        }, 50)
    }

    var watchForSelector = function (selector, cb) {
        var observer = new MutationObserver(function(mutations) { 
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {  
                    if (node.nodeType !== 1) { 
                        return; 
                    }
                    if (el = node.querySelector(selector)) {
                        cb();
                    }
                });
            });
        });
        observer.observe(document, { subtree: true, childList: true });        
    }

    var getPriceAsFloat = function (price) {
        var separatorComa,
                separatorDot;

        price = price.replace(/[^\d.,-]/g,'');

        separatorComa = price.indexOf(',');
        separatorDot = price.indexOf('.');

        if (separatorComa > -1 && separatorDot > -1) {
            if (separatorComa > separatorDot) {
                price = price.replace(/\./g, '');
                price = price.replace(',', '.');
            } else {
                price = price.replace(/,/g, '');
            }
        }  else if (separatorComa > -1) {
            price = price.replace(',', '.');
        }

        return parseFloat(price);
    };

    var addPpToDetailPage = function() {
        var priceMeta = $("meta[itemprop=price]");
        var price = null;

        if (priceMeta.length === 1) {
            price = parseFloat($(priceMeta[0]).attr('content'));
        } else {
            priceMeta = $('meta[property="product:price"]');

            if (priceMeta.length === 1) {
                price = getPriceAsFloat($(priceMeta[0]).attr('content'));
            }
        }

        var productPrice = $(".product--buybox div.price--default"),
                target = null;

        if (productPrice === null || productPrice.length === 0) {
            return;
        }

        var productPriceSiblings = $(productPrice).siblings();

        $.each(productPriceSiblings, function( key, value ) {
            if (target !== null) {
                return;
            }

            var classes = $(value).attr('class');

            if (classes !== undefined && classes.match(/product--tax/i)) {
                target = value;
            }
        });

        if (target === null) {
            target = productPrice;
        }

        var amount = function () {
            // if price is identified by meta tag
            if (price !== undefined && price !== null && !isNaN(price)) {
                return price;
            }

            price = $(productPrice).text();

            if (price === undefined) {
                return NaN;
            }

            return getPriceAsFloat(price);
        }();

        if (isNaN(amount)) {
            return;
        }

        $(target).parent().find("[id^=easycredit-pp-plugin-placeholder]").remove();
        $(target).after('<easycredit-widget amount="'+amount+'" webshop-id="'+webshopId+'" />');
    };

    var handleShippingPaymentConfirm = function () {
        onHydrated('easycredit-checkout', function() {
            $('easycredit-checkout', 'form[name=shippingPaymentForm]').submit(function(e){
                $('#shippingPaymentForm')
                    .append('<input type="hidden" name="easycredit[submit]" value="1" />')
                    .append('<input type="hidden" name="easycredit[number-of-installments]" value="'+ e.detail.numberOfInstallments +'" />')
                    .submit();
                

                return false;
            });
            $('form[name=shippingPaymentForm]').submit(function() {
                if (!$('easycredit-checkout').prop('isActive')
                    || $('easycredit-checkout').prop('paymentPlan') !== ''
                    || $('easycredit-checkout').prop('alert') !== ''
                ) {
                    return true;
                }
                if ($(this).find('input[name="easycredit[submit]"]').length > 0) {
                    return true;
                }
                
                $('easycredit-checkout')
                    .get(0)
                    .dispatchEvent(new Event('openModal'));
                return false;
            }); 
        });
    }


    var addSubscriber =  function(){
        if (webshopId) {
            $.subscribe('plugin/swAjaxProductNavigation/onInit', addPpToDetailPage);
            $.subscribe('plugin/swLoadingIndicator/onCloseFinished', addPpToDetailPage);
        }

        $.subscribe('plugin/swShippingPayment/onInit', function() {
            watchForSelector('easycredit-checkout', handleShippingPaymentConfirm);        
            onHydrated('easycredit-checkout', handleShippingPaymentConfirm);  
        });
        $.subscribe('plugin/swPanelAutoResizer/onAfterInit', function(ev, plugin){
            onHydrated('easycredit-checkout', function(){
                plugin.update();
            });
        });
    }
    if (typeof document.asyncReady !== 'undefined' && typeof $('#main-script').attr('async') != 'undefined') {
        document.asyncReady(addSubscriber);
    } else {
        addSubscriber();
    }
}());
