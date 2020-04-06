// {block name="backend/easycredit_merchant/app"}
Ext.define('Shopware.apps.EasycreditMerchant', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.EasycreditMerchant',

    /**
     * Enable bulk loading
     *
     * @type { Boolean }
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @type { String }
     */
    loadPath: '{url action="load"}',

    /**
     * @type { Array }
     */
    controllers: [
        'Main'
    ],

    /**
     * @type { Array }
     */
    models: [
        'ShopwareOrder',
        'Order'
    ],

    /**
     * @type { Array }
     */
    stores: [
        'Order'
    ],

    /**
     * @type { Array }
     */
    views: [
        'main.Window',
        'main.Grid',
        'main.Sidebar'
    ],

    /**
     * @returns { Shopware.apps.EasycreditMerchant.view.overview.Window }
     */
    launch: function () {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
// {/block}
