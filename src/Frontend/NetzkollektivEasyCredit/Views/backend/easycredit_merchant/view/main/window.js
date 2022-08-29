// {namespace name=backend/easycredit_merchant/view/main/window}

// {block name="backend/easycredit_merchant/view/main/window"}
Ext.define('Shopware.apps.EasycreditMerchant.view.main.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.easycredit-main-window',

    width: 1200,
    height: 500,

    /**
     *
     * @returns { Object }
     */
    configure: function () {
        var me = this;
        me.title = '{s name="window/title"}easyCredit-Ratenkauf: Payments{/s}';

        return {
            listingGrid: 'Shopware.apps.EasycreditMerchant.view.main.Grid',
            listingStore: 'Shopware.apps.EasycreditMerchant.store.Order'
        };
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = me.callParent();

        me.sidebar = Ext.create('Shopware.apps.EasycreditMerchant.view.main.Sidebar');
        items.push(me.sidebar);

        return items;
    }
});
// {/block}
