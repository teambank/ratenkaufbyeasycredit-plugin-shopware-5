// {namespace name="backend/easycredit_merchant/controller/main"}
// {block name="backend/easycredit_merchant/controller/main"}
Ext.define('Shopware.apps.EasycreditMerchant.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Array }
     */
    refs: [
        { ref: 'sidebar', selector: 'easycredit-main-sidebar' },
        { ref: 'grid', selector: 'easycredit-main-grid' }
    ],

    /**
     * @type { Shopware.apps.EasycreditMerchant.view.overview.Window }
     */
    window: null,

    /**
     * @type { Shopware.apps.EasycreditMerchant.view.refund.CaptureWindow|Shopware.apps.EasycreditMerchant.view.refund.SaleWindow }
     */
    refundWindow: null,

    /**
     * @type { Object }
     */
    details: null,

    /**
     * @type { Ext.data.Model }
     */
    record: null,

    init: function() {
        var me = this;

        me.createWindow();
        me.createComponentControl();
        me.callParent(arguments);
    },

    createComponentControl: function() {
        var me = this;

        me.control({
            'easycredit-main-grid': {
                select: me.onSelectGridRecord
            },
        });
    },

    createWindow: function() {
        var me = this;

        me.window = me.getView('main.Window').create().show();
    },

    /**
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     */
    onSelectGridRecord: function(element, record) {
        var me = this;

        me.loadDetails(record);
    },

    /**
     * @param { Ext.data.Model } record
     */
    loadDetails: function(record) {
        var me = this,
            orderDate = record.get('orderTime').toISOString().split('T')[0],
            transactionId = record.get('transactionId'),
            sidebar = me.getSidebar();

        sidebar.setTitle('{s name="main/transaction_status"}Transaction Status{/s}: ' + transactionId);
        sidebar.update('<easycredit-tx-manager id="' + transactionId + '" date="' + orderDate + '" />');
        sidebar.unmask();
    },

    getRecord: function() {
        return this.record;
    }
});
// {/block}
