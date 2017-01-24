<script src="https://ratenkauf.easycredit.de/ratenkauf/js/paymentPagePlugin.js" type="text/javascript"></script>
<script>
    (function () {

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

        var guid = function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                        .toString(16)
                        .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                    s4() + '-' + s4() + s4() + s4();
        };


        var addPpToDetailPage = function () {
            var buybox = $("#buybox"),
                    priceElements = [
                        '.article_details_price',
                        '.article_details_price2 strong'
                    ],
                    productPrice = null,
                    target = null;


            for (var p in priceElements) {
                var find = buybox.find(priceElements[p]);
                if (find.length > 0) {
                    productPrice = find[0];
                    break;
                }
            }

            if (productPrice === null) {
                return;
            }

            var productPriceSiblings = $(productPrice).siblings();

            $.each(productPriceSiblings, function (key, value) {
                if (target !== null) {
                    return;
                }

                var classes = $(value).attr('class');

                if (classes !== undefined && classes.match(/tax_attention/i)) {
                    target = value;
                }
            });

            if (target === null) {
                target = productPrice;
            }

            var elementId = 'easycredit-pp-plugin-placeholder-' + guid(),
                    amount = function () {
                        var price = $(productPrice).text();

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

        $(function () {
            addPpToDetailPage();
        });

    }());
    // {$EasyCreditLocale}
</script>