;(function($, window) {
    'use strict';

    var onHydrated = function (el, cb) {
        window.setTimeout(function() {
            if (!el || !el.classList.contains('hydrated')) {
                return onHydrated(el, cb);
            }
            cb();
        }, 50)
    };

    $.plugin('easycreditExpressButton', {

        defaults: {
            url: null
        },

        init: function() {
            var me = this;

            me.applyDataAttributes();

            var expressButton = me.$el.find('easycredit-express-button').get(0);
            onHydrated(expressButton, function() {
                me._on(expressButton, 'submit', function(event) {
                    event.preventDefault();

                    var form = $('<form>')
                        .attr('action', me.opts.url)
                        .hide();

                    var addToBasketForm = $(this).closest('form[name=sAddToBasket]');
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
        },
    })

    /**
     *  After the loading another product variant, we lose the
     *  plugin instance, therefore, we have to re-initialize it here.
     */
    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });

    $.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });

    $.subscribe('plugin/swInfiniteScrolling/onLoadPreviousFinished', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });
    $.subscribe('plugin/swResponsive/onCartRefreshSuccess', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });
    $.subscribe('plugin/swCollapseCart/onLoadCartFinished', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });

    window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');

})(jQuery, window);
