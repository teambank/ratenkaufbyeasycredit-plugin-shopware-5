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
      this.initTabs();
      this.initSmoothScrolling();
    }
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

  getSmoothLinks: function() {
    var links = this.el.query('a[href^="#"]');

    return links;
  },

  initSmoothScrolling: function() {
    // Enable smooth scrolling for anchor links (handle links with @href started with '#' only)
    links = this.getSmoothLinks();

    links.forEach(a => {
      var el = Ext.get(a);

      el.on( 'click', function (e, t) {
        // target element id
        var id = t.getAttribute('href');

        // target element
        var elements = Ext.dom.Query.select(id);
        if (elements.length === 0) {
            return;
        }

        // prevent standard hash navigation (avoid blinking in IE)
        e.preventDefault();

        // animated top scrolling
        elements[0].scrollIntoView({
          behavior: 'smooth'
        });
      }, false );
    });
  }
});

//{/block}