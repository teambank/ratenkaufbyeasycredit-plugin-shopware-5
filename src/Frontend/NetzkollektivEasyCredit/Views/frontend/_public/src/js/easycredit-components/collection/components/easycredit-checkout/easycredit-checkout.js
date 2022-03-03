import { Component, Prop, State, Listen, Element, h } from '@stencil/core';
import { formatCurrency, fetchInstallmentPlans, fetchAgreement } from '../../utils/utils';
export class EasycreditCheckout {
  constructor() {
    this.isActive = true;
    this.askForPrefix = false;
    this.privacyCheckboxChecked = false;
    this.totals = {
      interest: 0,
      total: 0
    };
    this.selectedInstallment = {
      totalInterest: 0,
      totalValue: 0,
      numberOfInstallments: 0
    };
    this.submitDisabled = false;
  }
  selectedInstallmentHandler(e) {
    this.selectedInstallment = this.installments.find(i => i.numberOfInstallments == e.detail);
  }
  async componentWillLoad() {
    if (this.amount > 0 && !this.alert && !this.paymentPlan) {
      await fetchInstallmentPlans(this.webshopId, this.amount).then((data) => {
        const instalment = data.installmentPlans.find(() => true);
        this.installments = instalment.plans.reverse();
        this.example = instalment.example;
      }).catch(e => {
        console.error(e);
      });
      fetchAgreement(this.webshopId).then(data => {
        this.privacyApprovalForm = data.privacyApprovalForm;
        if (this.amount < data.minFinancingAmount
          || this.amount > data.maxFinancingAmount) {
          this.alert = `Der Finanzierungbetrag liegt außerhalb der zulässigen Beträge (${data.minFinancingAmount} - ${data.maxFinancingAmount})`;
        }
      }).catch(e => {
        console.error(e);
        this.alert = 'Es ist ein Fehler aufgetreten.';
      });
    }
  }
  onSubmit() {
    this.el.dispatchEvent(new CustomEvent('submit', {
      bubbles: true,
      cancelable: true,
      detail: {
        numberOfInstallments: this.selectedInstallment.numberOfInstallments,
        privacyCheckboxChecked: this.privacyCheckboxChecked
      }
    }));
  }
  getPaymentPlan() {
    if (this.alert) {
      return false;
    }
    try {
      if (this.paymentPlan) {
        return JSON.parse(this.paymentPlan);
      }
    }
    catch (e) {
      // continue regardless of error
    }
    return false;
  }
  getPaymentPlanFragment() {
    if (!this.getPaymentPlan()) {
      return null;
    }
    return h("div", { class: "ec-payment-plan" },
      h("strong", null, "Ihre Auswahl:"),
      h("ul", { class: "ec-checkout__instalments payment-plan" },
        h("li", { class: "is-selected" },
          h("label", null,
            h("span", null,
              this.getPaymentPlan().numberOfInstallments,
              " Raten"),
            h("span", null,
              "\u00E0 ",
              formatCurrency(this.getPaymentPlan().installment))))),
      h("p", { class: "ec-checkout__small-print" },
        h("small", null,
          "Die Raten im Detail:\u00A0",
          this.getPaymentPlan().numberOfInstallments - 1,
          " x ",
          formatCurrency(this.getPaymentPlan().installment),
          ", 1 x ",
          formatCurrency(this.getPaymentPlan().lastInstallment))));
  }
  getCheckoutFragment() {
    if (this.alert) {
      return h("div", { class: "ec-checkout__alert" }, this.alert);
    }
    return ([h("div", { class: "ec-checkout__body" /* :class="bodyClasses" */ },
        h("easycredit-checkout-installments", { installments: JSON.stringify(this.installments) }),
        h("ul", { class: "ec-checkout__totals" },
          h("li", null,
            h("span", null, "Zinsen"),
            h("span", null, formatCurrency(this.selectedInstallment.totalInterest))),
          h("li", { class: "total" },
            h("span", null, "Gesamtbetrag"),
            h("span", null, formatCurrency(this.selectedInstallment.totalValue)))),
        h("div", { class: "ec-checkout__actions form-submit" },
          h("button", { type: "button", class: "btn btn-primary", onClick: () => this.modal.open() }, "Weiter zum Ratenkauf")),
        h("p", { class: "ec-checkout__small-print" },
          h("small", { innerHTML: this.example })))
    ]);
  }
  getPrefixFragment() {
    if (true || !this.askForPrefix) {
      return;
    }
    /*
    return <div class="title">
      <p><strong>Für ratenkauf by easyCredit bitte eine Anrede auswählen:</strong></p>
      <div class="form-radio badges">
        <span v-for="(label, key) in modal.prefix.options" :key="key">
          <input
              :id="'modalPrefix' + key"
              v-model="modal.prefix.value"
              class="form-check-input"
              type="radio"
              name="easycredit-prefix"
              :value="key"
              @change.stop=""
          >
          <label class="form-check-label" :for="'modalPrefix' + key">
            { label }
          </label>
        </span>
      </div>
    </div>
    */
  }
  handleCheckbox(e) {
    this.privacyCheckboxChecked = e.target.checked;
  }
  getPrivacyFragment() {
    return h("div", { class: "privacy" },
      h("p", null,
        h("strong", null, "Bitte stimmen Sie der Daten\u00FCbermittlung zu:")),
      h("div", { class: "form-check" },
        h("input", { id: "modalAgreement", onInput: (e) => this.handleCheckbox(e), class: "form-check-input", type: "checkbox", name: "easycredit-agreement", value: "1" }),
        h("label", { class: "form-check-label", htmlFor: "modalAgreement" },
          h("small", null, this.privacyApprovalForm))));
  }
  getModalFragment() {
    return ([
      h("easycredit-modal", { ref: (el) => this.modal = el },
        h("div", { slot: "heading" }, "Weiter zum Ratenkauf"),
        h("div", { slot: "content" },
          this.getPrefixFragment(),
          this.getPrivacyFragment(),
          h("div", { class: "form-submit" },
            h("button", { class: "btn btn-primary", type: "button", onClick: () => { this.onSubmit(); }, disabled: !this.privacyCheckboxChecked }, "Akzeptieren"))))
    ]);
  }
  render() {
    if (!this.isActive) {
      return null;
    }
    return ([
      h("div", { class: "ec-checkout-container" },
        h("div", { class: "ec-checkout" },
          this.getPaymentPlan() && this.getPaymentPlanFragment(),
          !this.getPaymentPlan() &&
            h("div", { class: "ec-checkout-wrapper" }, this.getCheckoutFragment())),
        this.getModalFragment())
    ]);
  }
  static get is() { return "easycredit-checkout"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-checkout.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-checkout.css"]
  }; }
  static get properties() { return {
    "isActive": {
      "type": "boolean",
      "mutable": false,
      "complexType": {
        "original": "boolean",
        "resolved": "boolean",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": ""
      },
      "attribute": "is-active",
      "reflect": false,
      "defaultValue": "true"
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
        "text": ""
      },
      "attribute": "amount",
      "reflect": false
    },
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
        "text": ""
      },
      "attribute": "webshop-id",
      "reflect": false
    },
    "alert": {
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
        "text": ""
      },
      "attribute": "alert",
      "reflect": false
    },
    "paymentPlan": {
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
        "text": ""
      },
      "attribute": "payment-plan",
      "reflect": false
    },
    "askForPrefix": {
      "type": "boolean",
      "mutable": false,
      "complexType": {
        "original": "boolean",
        "resolved": "boolean",
        "references": {}
      },
      "required": false,
      "optional": false,
      "docs": {
        "tags": [],
        "text": ""
      },
      "attribute": "ask-for-prefix",
      "reflect": false,
      "defaultValue": "false"
    }
  }; }
  static get states() { return {
    "privacyApprovalForm": {},
    "privacyCheckboxChecked": {},
    "totals": {},
    "installments": {},
    "selectedInstallment": {},
    "example": {},
    "submitDisabled": {}
  }; }
  static get elementRef() { return "el"; }
  static get listeners() { return [{
      "name": "selectedInstallment",
      "method": "selectedInstallmentHandler",
      "target": undefined,
      "capture": false,
      "passive": false
    }]; }
}
