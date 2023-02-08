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
                        .attr('method','post')
                        .hide();

                    if ($('form[name=sAddToBasket]').length > 0) {
                        var formData = new FormData($('form[name=sAddToBasket]').get(0));

                        for (var key of formData.keys()) {
                            form.append($('<input>').attr('name',key).attr('value', formData.get(key)));
                        }
                    }

                    $('body').append(form);
                    form.submit();
                });

                /* if ($(expressButton).closest('.container--ajax-cart').length > 0) {
                    me.fixOffCanvasModal(expressButton);
                } */
            });
        },

        /* fixOffCanvasModal (expressButton) {
            var button = expressButton.shadowRoot.querySelector('easycredit-modal');

            var callback = (mutationList, observer) => {
            for (const mutation of mutationList) {
                if (mutation.type === 'attributes' &&
                    mutation.target.classList.contains('ec-modal')
                ) {
                if (mutation.target.classList.contains('show')) {
                    document.querySelector('.container--ajax-cart').style.transform = 'none';
                    document.querySelector('.container--ajax-cart').style.transformStyle = 'initial';
                } else {
                    document.querySelector('.container--ajax-cart').style.transform = '';
                    document.querySelector('.container--ajax-cart').style.transformStyle = '';
                }
                }
            }
            };
            var observer = new MutationObserver(callback);
            observer.observe(button, { attributes: true, subtree: true });
        }, */
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
    $.subscribe('plugin/swCollapseCart/onLoadCartFinished', function() {
        window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');
    });

    window.StateManager.addPlugin('.easycredit-express-button-container', 'easycreditExpressButton');

})(jQuery, window);
