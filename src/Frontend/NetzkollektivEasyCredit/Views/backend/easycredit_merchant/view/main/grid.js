// {namespace name=backend/easycredit_merchant/view/main/grid}

// {block name="backend/easycredit_merchant/view/main/grid"}
Ext.define('Shopware.apps.EasycreditMerchant.view.main.Grid', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.easycredit-main-grid',

    region: 'center',

    /**
     * @returns { Object }
     */
    configure: function () {
        var me = this;

        return {
            columns: me.getColumns(),
            rowEditing: false,
            deleteButton: false,
            deleteColumn: false,
            editButton: false,
            editColumn: false,
            addButton: false
        };
    },

    /**
     * @returns { Object }
     */
    getColumns: function () {
        var me = this;

        return {
            transactionId: {
                header: '{s name="column/transactionId"}Transaction Id{/s}',
                draggable: false
            },
            languageIso: {
                header: '{s name="column/shopName"}Shop name{/s}',
                renderer: me.shopColumnRenderer,
                draggable: false
            },
            orderTime: {
                header: '{s name="column/orderTime"}Order timestamp{/s}',
                renderer: me.dateColumnRenderer,
                draggable: false
            },
            number: {
                header: '{s name="column/orderNumber"}Order number{/s}',
                draggable: false
            },
            invoiceAmount: {
                header: '{s name="column/invoiceAmount"}Invoice amount{/s}',
                renderer: me.invoiceAmountColumnRenderer,
                draggable: false,
                align: 'left'
            },
            customerId: {
                header: '{s name="column/customerEmail"}Customer email{/s}',
                renderer: me.customerColumnRenderer,
                draggable: false
            },
            status: {
                header: '{s name="column/orderStatus"}Order status{/s}',
                renderer: me.orderStatusColumnRenderer,
                draggable: false
            },
            cleared: {
                header: '{s name="column/paymentStatus"}Payment status{/s}',
                renderer: me.paymentStatusColumnRenderer,
                draggable: false
            },
            merchantStatus: {
                header: '{s name="column/merchantStatus"}Merchant Status{/s}',
                draggable: false,
                sortable: false,
                renderer: me.merchantStatusRenderer,
                width: 150
            }
        };
    },

    /**
     * @returns { Array }
     */
    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments);

        // Customer details button
        items.push({
            iconCls: 'sprite-user',
            tooltip: '{s name="tooltip/customer"}Open customer details{/s}',

            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer',
                    action: 'detail',
                    params: {
                        customerId: record.get('customerId')
                    }
                });
            }
        });

        // Order details button
        items.push({
            iconCls: 'sprite-shopping-basket',
            tooltip: '{s name="tooltip/order"}Open order details{/s}',

            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Order',
                    action: 'detail',
                    params: {
                        orderId: record.get('id')
                    }
                });
            }
        });

        return items;
    },

    /**
     * @param { String } value
     * @returns { String }
     */
    dateColumnRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }

        return Ext.util.Format.date(value, 'd.m.Y H:i');
    },

    /**
     * @param { String } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @returns { string }
     */
    shopColumnRenderer: function (value, metaData, record) {
        var shop = record.getLanguageSubShop().first();

        if (shop instanceof Ext.data.Model) {
            return shop.get('name');
        }

        return value;
    },

    /**
     * @param { String } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @returns { String }
     */
    customerColumnRenderer: function (value, metaData, record) {
        var customer = record.getCustomer().first();

        if (customer instanceof Ext.data.Model) {
            return customer.get('email');
        }

        return value;
    },

    /**
     * @param { String } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @returns { String }
     */
    orderStatusColumnRenderer: function (value, metaData, record) {
        var status = record.getOrderStatus().first();

        if (status instanceof Ext.data.Model) {
            return status.get('description');
        }

        return value;
    },

    /**
     * @param { String } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @returns { String }
     */
    paymentStatusColumnRenderer: function (value, metaData, record) {
        var status = record.getPaymentStatus().first();

        if (status instanceof Ext.data.Model) {
            return status.get('description');
        }

        return value;
    },

    /**
     * @param { String } value
     * @returns { String }
     */
    invoiceAmountColumnRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }

        return Ext.util.Format.currency(value);
    },

    /**
     * @param { String } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @return { String }
     */
    merchantStatusRenderer: function (value, metaData, record) {

        const offset = record.data.orderTime.getTimezoneOffset();
        var date = new Date(record.data.orderTime.getTime() + (offset*60*1000));

        return '<easycredit-merchant-status-widget id="'+ value +'" date="'+ date.toISOString().split('T')[0] +'" />';
    }
});
// {/block}
