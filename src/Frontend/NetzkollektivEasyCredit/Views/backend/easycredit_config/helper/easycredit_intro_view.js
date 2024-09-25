//{block name="backend/easycredit/clickandcollect"}

Ext.define('Netzkollektiv.EasyCredit.ClickAndCollect', {
  extend: 'Ext.panel.Panel',
  cls: 'base-element-easycreditintro',
  alias: 'widget.base-element-easycreditintro',
  initComponent: function () {
      this.callParent(arguments);
      this.update(this.helpText);
  },
  listeners: {
    afterrender: function () {
      if (this.elementName === 'easycreditMarketing') {
        this.initTabs();
      }

      if (
        this.elementName === 'easyCreditIntro' ||
        this.elementName === 'easyCreditActivation'
      ) {
        this.checkStatusAction()
        .then((data) => {

          if ( this.el.query('.easycredit-intro.intro-2024.bill').length > 0 ) {
            const apiKey = this.el.dom.closest('table').querySelector('input[type=text]').value
            this.fetchWebshopInfo(apiKey)
            .then((data) => {
              var elBill = this.el.query('.easycredit-intro.intro-2024.bill');

              if ( data.billPaymentActive === true ) {
                var elRemove = this.el.query('#easycredit-activate-invoice-button-inactive');
              } else {
                var elRemove = this.el.query('#easycredit-activate-invoice-button-active');
                elBill.length > 0 ? elBill[0].classList.add('easycredit_rechnung-not-allowed') : null;
              }

              elRemove.length > 0 ? elRemove[0].remove() : null;
            })
            .catch(error => {
              var elBill = this.el.query('.easycredit-intro.intro-2024.bill');
              elBill.length > 0 ? elBill[0].classList.add('easycredit_rechnung-not-allowed') : null;

              var elRemove = this.el.query('#easycredit-activate-invoice-button-active');
              elRemove.length > 0 ? elRemove[0].remove() : null;
            });
          }

          if ( !data.methods.easycredit_ratenkauf ) {
            var elActivation = this.el.query('.easycredit-intro.intro-2024.activation');
            elActivation.length > 0 ? elActivation[0].classList.add('easycredit_ratenkauf-not-active') : null;
          }
          if ( !data.methods.easycredit_rechnung ) {
            var elBill = this.el.query('.easycredit-intro.intro-2024.bill');
            elBill.length > 0 ? elBill[0].classList.add('easycredit_rechnung-not-active') : null;

            var elActivation = this.el.query('.easycredit-intro.intro-2024.activation');
            elActivation.length > 0 ? elActivation[0].classList.add('easycredit_rechnung-not-active') : null;
          }

        })
        .catch(error => {});
      }
    },
  },

  getTabs: function() {
    var tabs = this.el.query('.easycredit-intro__tabs .easycredit-intro__tab');

    return tabs;
  },

  getTabContents: function() {
    var tabContents = this.el.query('.easycredit-intro__tab-content');

    return tabContents;
  },

  selectTab: function(target) {
    tabs = this.getTabs();
    tabContents = this.getTabContents();

    tabs.forEach(tab => {
      if ( tab.dataset.target === target ) {
        tab.classList.add('active');
      } else {
        tab.classList.remove('active');
      }
    });

    tabContents.forEach(content => {
      if ( content.dataset.tab === target ) {
        content.classList.add('active');
      } else {
        content.classList.remove('active');
      }
    });
  },

  initTabs: function() {
    var me = this;
    tabs = this.getTabs();

    if ( tabs.length > 0 ) {
        this.el.dom.classList.add('has-tabs');
    }

    tabs.forEach(tab => {
      var el = Ext.get(tab);

      el.on('click', function (e, t) {
        target = t.dataset.target;
        me.selectTab(target);
      });
    });
  },

  fetchWebshopInfo: function(apiKey) {
    return new Promise((resolve, reject) => {
      const url = 'https://ratenkauf.easycredit.de/api/payment/v3/webshop/' + apiKey;

      return fetch(url).then((response) => {
          if (!response.ok) { 
              return Promise.reject(response); 
          }
          return response.json();
      }).then((data) => {
          resolve(data);
      }).catch((error) => {
          console.error('Error fetching webshop info:', error);
          reject(error);
      });
    });
  },

  checkStatusAction: function() {
    return new Promise((resolve, reject) => {

      Ext.Ajax.request({
        url: '{url controller=PaymentEasycredit action=getStatus}',
        success: function(response) {
          var data = Ext.decode(response.responseText);
            if (data.success) {
                resolve(data);
            }
        },
        failure: function() {
            Ext.Msg.alert('Error', 'Request failed');
            reject('Request failed');
        }
      });

    });
  }
});

//{/block}