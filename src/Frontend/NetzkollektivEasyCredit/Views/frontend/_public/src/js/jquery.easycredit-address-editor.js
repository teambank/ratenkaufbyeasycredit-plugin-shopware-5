;(function($, window) {
    'use strict';

    $.plugin('swEasycreditAddressEditor',$.extend(true, {}, PluginsCollection.swAddressEditor,{
        init: function () {
            var me = this;

            me.applyDataAttributes(true);

            if (typeof me.opts.id == 'undefined') {
                return;
            }

            me.onAddressFailure(me.opts.id);
            $.subscribe('plugin/swPreloaderButton/onShowPreloader', function(event, _me) {
                var selector = 'form[name="'+_me.$el.data('submit-form')+'"]';
                if ($(selector)) {
                    $(selector).submit();
                }
            })
            $.publish('plugin/swEasycreditAddressEditor/onRegisterEvents', [ me ]);
        },
        onAddressFailure: function() {
            var me = this;

            $.publish('plugin/swEasycreditAddressEditor/onBeforeClick', [ me, me.opts.id ]);

            if (me.opts.id) {
                me.open(me.opts.id);
            } else {
                me.open();
            }

            $.publish('plugin/swEasycreditAddressEditor/onAfterClick', [ me, me.opts.id ]);
        },
        onSave: function($modal, response) {
            var me = this;

            $.publish('plugin/swEasycreditAddressEditor/onAfterSave', [ me, $modal, response ]);

            if (response.success === true) {
                if (me.opts.showSelectionOnClose) {
                    $.addressSelection.openPrevious();
                } else {
                    $('#shippingPaymentForm').submit();
                }
            } else {
                me._highlightErrors($modal, response.errors);
                me._enableSubmitButtons($modal);
            }
        }
    }));

    window.StateManager.addPlugin(
        '*[data-easycredit-address-editor="true"]', 
        'swEasycreditAddressEditor'
    );

})(jQuery, window);
