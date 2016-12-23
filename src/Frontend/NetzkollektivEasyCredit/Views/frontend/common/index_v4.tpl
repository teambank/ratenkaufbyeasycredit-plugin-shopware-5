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

        var addPpToDetailPage = function() {
            var productPrice = $("#buybox").find(".article_details_price"),
                    target = null;

            if (productPrice === null) {
                return;
            }

            var productPriceSiblings = $(productPrice).siblings();

            $.each(productPriceSiblings, function( key, value ) {
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

                        return parseFloat(price.replace(/[^\d.,-]/g, '').replace(',', '.'));
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

        $(function() {
            addPpToDetailPage();
        });

    }());
// {$EasyCreditLocale}
</script>