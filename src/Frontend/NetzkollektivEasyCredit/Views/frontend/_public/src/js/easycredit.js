(function () {
    var webshopId = $('meta[name=easycredit-api-key]').attr('content');
    var widgetActive = $('meta[name=easycredit-widget-active]').attr('content') === 'true';
    var disableFlexprice = $('meta[name=easycredit-disable-flexprice]').attr('content') === 'true';
    var paymentTypes = $('meta[name=easycredit-payment-types]').attr('content');

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

        const amount = (() => {
            // If price is identified by a meta tag
            if (price !== undefined && price !== null && !isNaN(price)) {
                return price;
            }

            const priceText = $(productPrice).text();

            return priceText === undefined ? NaN : getPriceAsFloat(priceText);
        })();

        if (isNaN(amount)) {
            return;
        }

        if (!$(target).siblings('easycredit-widget').length) {
            const opts = {
                amount,
                'webshop-id': webshopId,
                'payment-types': paymentTypes,
                ...(disableFlexprice && { 'disable-flexprice': 'true' })
            };

            const attributes = Object.entries(opts)
                .map(([key, value]) => `${key}="${value}"`)
                .join(' ');

            $(target).after(`<easycredit-widget ${attributes}/>`);
            return;
        }
        $(target).siblings('easycredit-widget').get(0).setAttribute('amount', amount);
    };

    var handleShippingPaymentConfirm = function () {
        onHydrated('easycredit-checkout', function() {
            $('easycredit-checkout', 'form[name=shippingPaymentForm]').submit(function(e){
                var form = $('#shippingPaymentForm')
                form.append('<input type="hidden" name="easycredit[submit]" value="1" />')
                if (e.detail && e.detail.numberOfInstallments) {
                    form.append('<input type="hidden" name="easycredit[number-of-installments]" value="'+ e.detail.numberOfInstallments +'" />')
                }
                form.submit();

                return false;
            });
            $('form[name=shippingPaymentForm]').submit(function() {
                if (!$('easycredit-checkout').closest('.payment--method.block.method').find('input[type=radio]').is(':checked')) {
                    return true;
                }
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
        if (webshopId && widgetActive) {
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
