//{block name="backend/plugin_manager/view/detail/container" append}

Ext.define('Netzkollektiv.EasyCredit.ClickAndCollect',{
  extend: 'Ext.panel.Panel',
  cls: 'easycredit-click-and-collect-intro-panel',
  alias: 'widget.base-element-easycreditintro',
  initComponent: function () {
      this.callParent(arguments);
      this.update(this.helpText);
  }
});

//{/block}