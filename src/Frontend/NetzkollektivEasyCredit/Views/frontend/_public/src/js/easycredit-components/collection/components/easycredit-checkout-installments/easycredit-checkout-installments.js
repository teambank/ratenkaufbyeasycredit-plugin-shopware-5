import { Component, Prop, State, Listen, Watch, Event, h } from '@stencil/core';
import { formatCurrency } from '../../utils/utils';
export class EasycreditCheckoutInstallments {
  constructor() {
    this.showMoreButtonText = 'Weitere Raten anzeigen +';
    this.rows = 5;
    this.collapsed = true;
    this.collapsing = false;
  }
  selectedInstallmentHandler(e) {
    this.selectedInstallmentValue = e.detail;
  }
  parseInstallmentsProp(newValue) {
    if (newValue)
      this._installments = JSON.parse(newValue);
  }
  async componentWillLoad() {
    this.parseInstallmentsProp(this.installments);
  }
  componentDidLoad() {
    this.selectFirstOption();
  }
  selectFirstOption() {
    let initialOption = this.installmentsBase.querySelector('input:first-child');
    initialOption.checked = true;
    this.selectedInstallment.emit(initialOption.value);
  }
  listBase() {
    return this._installments.slice(0, this.rows);
  }
  listExtended() {
    return this._installments.slice(this.rows);
  }
  listExtendedMaxHeight() {
    var maxHeight = ((this._installments.length - this.rows) * 42);
    return maxHeight + 'px';
  }
  listClasses(cls) {
    cls += this.collapsing ? ' collapsing' : '';
    cls += this.collapsed ? ' collapsed' : '';
    return cls;
  }
  toggleList() {
    this.collapsing = !this.collapsing;
    setTimeout(() => this.collapsing = !this.collapsing, 350);
    setTimeout(() => this.collapsed = !this.collapsed, 350);
    this.showMoreButtonText = !this.collapsed ? 'Weitere Raten anzeigen +' : 'Weniger Raten anzeigen -';
    if (this._installments.findIndex((item) => item.numberOfInstallments == this.selectedInstallmentValue) >= this.rows) {
      this.selectFirstOption();
    }
  }
  onInstallmentSelect(e) {
    let t = e.target;
    this.selectedInstallment.emit(t.value);
  }
  getInstallmentFragment(installment) {
    return h("li", null,
      h("input", { id: `easycreditInstallment${installment.numberOfInstallments}`, type: "radio", name: "easycredit-duration", value: installment.numberOfInstallments, onInput: (e) => this.onInstallmentSelect(e) }),
      h("label", { htmlFor: `easycreditInstallment${installment.numberOfInstallments}` },
        h("span", null,
          installment.numberOfInstallments,
          " Monate"),
        " ",
        h("span", null,
          formatCurrency(installment.installment),
          " / Monat")));
  }
  getMoreListFragment() {
    if (this._installments.length > this.rows) {
      return h("ul", { class: "ec-checkout__instalments actions" },
        h("li", { class: this.listClasses('more'), onClick: () => this.toggleList() }, this.showMoreButtonText));
    }
  }
  render() {
    return h("div", null,
      h("ul", { class: { 'ec-checkout__instalments': true, 'base': true, 'last': this._installments.length <= this.rows }, ref: (el) => this.installmentsBase = el }, this.listBase().map(installment => this.getInstallmentFragment(installment))),
      h("ul", { class: this.listClasses('ec-checkout__instalments extended'), style: { maxHeight: this.listExtendedMaxHeight() } }, this.listExtended().map(installment => this.getInstallmentFragment(installment))),
      this.getMoreListFragment());
  }
  static get is() { return "easycredit-checkout-installments"; }
  static get properties() { return {
    "showMoreButtonText": {
      "type": "string",
      "mutable": true,
      "complexType": {
        "original": "string",
        "resolved": "string",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": ""
      },
      "attribute": "show-more-button-text",
      "reflect": false,
      "defaultValue": "'Weitere Raten anzeigen +'"
    },
    "installments": {
      "type": "any",
      "mutable": false,
      "complexType": {
        "original": "any",
        "resolved": "any",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": ""
      },
      "attribute": "installments",
      "reflect": false
    },
    "rows": {
      "type": "number",
      "mutable": false,
      "complexType": {
        "original": "number",
        "resolved": "number",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": ""
      },
      "attribute": "rows",
      "reflect": false,
      "defaultValue": "5"
    }
  }; }
  static get states() { return {
    "collapsed": {},
    "collapsing": {},
    "_installments": {},
    "selectedInstallmentValue": {}
  }; }
  static get events() { return [{
      "method": "selectedInstallment",
      "name": "selectedInstallment",
      "bubbles": true,
      "cancelable": true,
      "composed": true,
      "docs": {
        "tags": [],
        "text": ""
      },
      "complexType": {
        "original": "string",
        "resolved": "string",
        "references": {}
      }
    }]; }
  static get watchers() { return [{
      "propName": "installments",
      "methodName": "parseInstallmentsProp"
    }]; }
  static get listeners() { return [{
      "name": "selectedInstallment",
      "method": "selectedInstallmentHandler",
      "target": undefined,
      "capture": false,
      "passive": false
    }]; }
}
