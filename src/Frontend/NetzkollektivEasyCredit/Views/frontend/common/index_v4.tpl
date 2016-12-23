<script src="https://ratenkauf.easycredit.de/ratenkauf/js/paymentPagePlugin.js" type="text/javascript"></script>
<script>
    (function () {

        var sepLanguages = {
            'gr': ',',
            'id': ',',
            'he': ',',
            'de': ',',
            'hu': ',',
            'lt': ',',
            'fi': ',',
            'da': ',',
            'ja': '.',
            'fa': ',',
            'sr': ',',
            'hr': ',',
            'es': ',',
            'vn': ',',
            'mk': ',',
            'sk': ',',
            'sl': ',',
            'fr': ',',
            'ca': ',',
            'th': ',',
            'pt': ',',
            'lv': ',',
            'tr': ',',
            'bg': ',',
            'af': ',',
            'pl': ',',
            'en': '.',
            'ru': ',',
            'ro': ',',
            'ko': '.',
            'it': ',',
            'nl': ',',
            'cs': ',',
            'ukr': ','
        };

        var sepCountries = {
            'el_GR': ',',
            'no_NB': ',',
            'pt_PT': ',',
            'zh_CN': '.',
            'fr_CA': ',',
            'pt_BR': '.',
            'sv_SE': ',',
            'no_NN': ',',
            'en_GB': '.',
            'sr_RS': ','
        };

        var parsePrice = function (price) {
            var locale = '{$Shop->getLocale()->toString()}',
                    s = locale.split('_'),
                    lang = s[0],
                    decimalSeparator,
                    thousandsSeparator;

            if (sepCountries.hasOwnProperty(locale)) {
                decimalSeparator = sepCountries[locale];
            } else if (sepLanguages.hasOwnProperty(lang)) {
                decimalSeparator = sepLanguages[locale];
            } else {
                decimalSeparator = '.';
            }

            thousandsSeparator = (decimalSeparator === '.') ? ',' : '.';

            var re = new RegExp(thousandsSeparator, "g");

            return parseFloat(price.replace(/[^\d.,-]/g, '').replace(re, '').replace(decimalSeparator, '.'));
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
            var productPrice = $("#buybox").find(".article_details_price"),
                    target = null;

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

                        return parsePrice(price);
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