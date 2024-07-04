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

            const addFormField = (form, key, value) => {
                form.append($('<input>').attr('name', key).attr('value', value));
            }

            const handleExpressButton = function(event) {
                event.preventDefault();

                var form = $('<form>')
                    .attr('action', me.opts.url)
                    .hide();

                if (event.detail) {
                    for (const [key, value] of Object.entries(event.detail)) {
                        addFormField(form, `easycredit[${key}]`, value)
                    }
                }

                var addToBasketForm = $(this).closest('form[name=sAddToBasket]');
                if (addToBasketForm.length > 0) {
                    form.attr('method','post');
                    var formData = new FormData(addToBasketForm.get(0));

                    for (var key of formData.keys()) {
                        addFormField(form, key, formData.get(key));
                    }
                }

                $('body').append(form);
                form.submit();
            }

            var expressButton = me.$el.find('easycredit-express-button').get(0);
            onHydrated(expressButton, function() {
                me._on(expressButton, 'submit', handleExpressButton);
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
