// {block name="backend/easycredit_merchant/store/order"}
Ext.define('Shopware.apps.EasycreditMerchant.store.Order', {
    /**
     * extends from the standard ExtJs store class
     * @type { String }
     */
    extend: 'Shopware.store.Listing',

    /**
     * the model which belongs to the store
     * @type { String }
     */
    model: 'Shopware.apps.EasycreditMerchant.model.ShopwareOrder',

    /**
     * @return { Object }
     */
    configure: function () {
        return {
            controller: 'EasycreditMerchant'
        };
    }
});
// {/block}