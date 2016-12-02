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
            var me = $('#easycredit-pp-plugin-box');

            if (me === null) {
                return;
            }

            var elementId = 'easycredit-pp-plugin-placeholder-' + guid(),
                    amount = function () {
                        var price = $(me).parent().find('.price--default').text();

                        if (price === undefined) {
                            return NaN;
                        }

                        return parseFloat(price.replace(/[^\d.,-]/g, '').replace(',', '.'));
                    }();

            if (isNaN(amount)) {
                return;
            }

            me.html('<div id="' + elementId + '"></div>');

            window.rkPlugin.anzeige(elementId, {
                webshopId: '{$EasyCreditApiKey}',
                finanzierungsbetrag: amount,
                textVariante: 'KURZ'
            });
        };

        $.subscribe('plugin/swAjaxProductNavigation/onProductNavigationFinished', addPpToDetailPage);
        $.subscribe('plugin/swLoadingIndicator/onCloseFinished', addPpToDetailPage);

    }());

</script>
