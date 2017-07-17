{if isset($EasyCreditApiKey)}
    <script src="https://ratenkauf.easycredit.de/ratenkauf/js/paymentPagePlugin.js" type="text/javascript"></script>
    <script>
        (function () {
            var guid = function () {
                function s4() {
                    return Math.floor((1 + Math.random()) * 0x10000)
                            .toString(16)
                            .substring(1);
                }

                return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                        s4() + '-' + s4() + s4() + s4();
            };

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

                var elementId = 'easycredit-pp-plugin-placeholder-' + guid(),
                        amount = function () {
                            // if price is identifed by meta tag
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

                $(target).after('<div id="' + elementId + '"></div>');

                window.rkPlugin.anzeige(elementId, {
                    webshopId: '{$EasyCreditApiKey}',
                    finanzierungsbetrag: amount,
                    textVariante: 'KURZ'
                });
            };

            {if isset($EasyCreditShopwareLt53) && $EasyCreditShopwareLt53}

            $.subscribe('plugin/swAjaxProductNavigation/onProductNavigationFinished', addPpToDetailPage);
            $.subscribe('plugin/swLoadingIndicator/onCloseFinished', addPpToDetailPage);

            {else}

            document.asyncReady(function() {
                $.subscribe('plugin/swAjaxProductNavigation/onProductNavigationFinished', addPpToDetailPage);
                $.subscribe('plugin/swLoadingIndicator/onCloseFinished', addPpToDetailPage);
            });

            {/if}

        }());

    </script>
{/if}