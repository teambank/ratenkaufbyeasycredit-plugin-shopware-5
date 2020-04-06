// {namespace name=backend/easycredit_merchant/view/main/sidebar}

// {block name="backend/easycredit_merchant/view/main/sidebar"}
Ext.define('Shopware.apps.EasycreditMerchant.view.main.Sidebar', {
    extend: 'Ext.Panel',
    alias: 'widget.easycredit-main-sidebar',

    region: 'east',
    layout: 'anchor',
    disabled: true,
    autoScroll: true,
    flex: 0.4,
    height: '100%',
    bodyPadding: 5,
    title: '{s name="sidebar/transaction_status"}Transaction Status{/s}',

    /**
     * @type { Shopware.apps.EasycreditMerchant.view.sidebar.Toolbar }
     */
    toolbar: null
});
// {/block}
