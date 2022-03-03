import { Component, Prop, State, h } from '@stencil/core';
import { formatAmount, fetchInstallmentPlans } from '../../utils/utils';
export class EasycreditWidget {
  getLinkText() {
    return 'Mehr Infos';
  }
  getInstallmentText() {
    if (!this.installments) {
      return '';
    }
    if (this.amount < this.installments.minFinancingAmount) {
      return h("span", null,
        "Finanzieren ab\u00A0",
        h("em", null,
          this.installments.minFinancingAmount.toLocaleString('de-DE', { maximumFractionDigits: 0 }),
          " \u20AC Bestellwert"));
    }
    if (this.amount > this.installments.maxFinancingAmount) {
      return h("span", null,
        "Finanzieren bis\u00A0",
        h("em", null,
          this.installments.maxFinancingAmount.toLocaleString('de-DE', { maximumFractionDigits: 0 }),
          " \u20AC Bestellwert"));
    }
    return h("span", null,
      "Finanzieren ab\u00A0",
      h("em", null,
        formatAmount(this.getMinimumInstallment().installment),
        " \u20AC / Monat"));
  }
  componentWillLoad() {
    this.isValid = false;
    fetchInstallmentPlans(this.webshopId, this.amount).then((data) => {
      this.isValid = true;
      this.installments = data;
    }).catch(e => {
      console.error(e);
    });
  }
  getInstallmentPlan() {
    if (!this.installments) {
      return null;
    }
    return this.installments.installmentPlans
      .find(() => true);
  }
  getMinimumInstallment() {
    return this.getInstallmentPlan().plans
      .sort((a, b) => b.numberOfInstallments - a.numberOfInstallments)
      .find(() => true);
  }
  getRatenkaufIcon() {
    return h("svg", { width: "46px", height: "46px", viewBox: "0 0 46 46", version: "1.1", xmlns: "http://www.w3.org/2000/svg" },
      h("defs", null),
      h("g", { id: "ratenkauf-icon", stroke: "none", "stroke-width": "1", fill: "none", "fill-rule": "evenodd" },
        h("g", null,
          h("path", { d: "M46,23.0003853 C46,35.7027693 35.7025967,46 23,46 C10.2966326,46 0,35.7027693 0,23.0003853 C0,10.2972307 10.2966326,0 23,0 C35.7025967,0 46,10.2972307 46,23.0003853", id: "blue", fill: "#005DA9" }),
          h("polygon", { id: "orange", fill: "#EC6608", points: "19.1197164 22.579685 12 16 12 37 19.1197164 37 19.3713154 37 34 37" }),
          h("path", { d: "M25.7341311,8 L19.2650884,8 C15.2520812,8 12,11.2829473 12,15.3330708 L12,30 C12,25.9498765 15.2520812,22.6669292 19.2650884,22.6669292 L25.7341311,22.6653539 C29.7471384,22.6653539 33,19.3824066 33,15.3330708 C33,11.2829473 29.7471384,8 25.7341311,8", id: "white", fill: "#FFFFFF" }))));
  }
  render() {
    var _a;
    return ([
      this.isValid &&
        h("div", { class: "ec-widget" },
          this.getInstallmentText(),
          h("br", null),
          "mit ratenkauf by easyCredit.",
          h("a", { class: "ec-widget__link", onClick: () => this.modal.open() }, this.getLinkText()),
          this.getRatenkaufIcon()),
      h("easycredit-modal", { ref: (el) => this.modal = el },
        h("span", { slot: "content" },
          h("iframe", { "data-src": (_a = this.getInstallmentPlan()) === null || _a === void 0 ? void 0 : _a.url })))
    ]);
  }
  static get is() { return "easycredit-widget"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-widget.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-widget.css"]
  }; }
  static get properties() { return {
    "webshopId": {
      "type": "string",
      "mutable": false,
      "complexType": {
        "original": "string",
        "resolved": "string",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": "Webshop Id"
      },
      "attribute": "webshop-id",
      "reflect": false
    },
    "amount": {
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
        "text": "Financing Amount"
      },
      "attribute": "amount",
      "reflect": false
    }
  }; }
  static get states() { return {
    "installments": {},
    "isValid": {}
  }; }
}
