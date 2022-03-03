import { Component, Prop, State, h } from '@stencil/core';
import { formatCurrency, formatDatetime, fetchTransaction, refundTransaction, captureTransaction } from '../../utils/utils';
export class EasycreditMerchantStatusWidget {
  constructor() {
    this.tx = null;
    this.loading = false;
    this.submitDisabled = false;
    this.progressItems = [];
    this.typeLabels = {
      'ORDER': 'Bestellung',
      'CAPTURE': 'Lieferung',
      'REFUND': 'Rückabwicklung'
    };
  }
  async componentWillLoad() {
    this.loadTransaction();
  }
  loadTransaction() {
    this.loading = true;
    fetchTransaction(this.txId).then((transaction) => {
      this.tx = transaction;
      this.amount = this.tx.orderDetails.currentOrderValue;
      this.loading = false;
    }).catch((e) => {
      console.error(e);
      this.loading = false;
    });
  }
  youngerThanOneDay() {
    const oneDay = 24 * 60 * 60 * 1000; // in ms
    let parsed = Date.parse(this.date);
    return !isNaN(parsed) || parsed > (Date.now() - oneDay);
  }
  orderAmount() {
    return formatCurrency(this.tx.orderDetails.currentOrderValue)
      + ' / ' + formatCurrency(this.tx.orderDetails.originalOrderValue);
  }
  canShip() {
    return !this.tx.bookings.filter(b => b.type === 'CAPTURE').length;
  }
  canRefund() {
    return this.tx.orderDetails.currentOrderValue > 0;
  }
  showAlert(alert) {
    this.alert = alert;
    let el;
    if (el = this.alertElement) {
      el.classList.remove('easycredit-tx-alert');
      el.offsetWidth;
      el.classList.add('easycredit-tx-alert');
    }
  }
  async updateTransaction() {
    this.loading = true;
    try {
      if (this.status === 'REFUND') {
        await refundTransaction(this.tx.transactionId, {
          value: this.amount
        });
      }
      else if (this.status === 'CAPTURE') {
        await captureTransaction(this.tx.transactionId, {
          trackingNumber: this.trackingNumber
        });
      }
      this.showAlert({
        message: 'Der Status wurde erfolgreich übermittelt.',
        type: 'success'
      });
    }
    catch (e) {
      this.showAlert({
        message: 'Die Statusübermittlung ist fehlgeschlagen.',
        type: 'error'
      });
    }
    await this.loadTransaction();
    this.loading = false;
  }
  getProgressBarFragment() {
    if (!this.tx) {
      return;
    }
    let progressItems = [...this.tx.bookings];
    progressItems.push({
      created: this.tx.orderDetails.orderDate,
      status: 'DONE',
      type: 'ORDER'
    });
    let progressBar = progressItems.sort((a, b) => {
      return Date.parse(a.created) - Date.parse(b.created);
    }).map((booking, idx, arr) => {
      return ([
        h("div", { class: { 'progress': true, 'light': booking.status === 'PENDING', 'error': booking.status === 'FAILED' } },
          h("strong", null, this.typeLabels[booking.type]),
          h("br", null),
          h("span", null, booking.created ? formatDatetime(booking.created) : 'n/a'),
          h("br", null),
          booking.message &&
            h("span", null,
              booking.message,
              h("br", null)),
          (idx != arr.length - 1) && h("span", null, "|"))
      ]);
    });
    return ([
      h("div", { class: "progress-bar" }, progressBar)
    ]);
  }
  getInfoFragment() {
    if (!this.tx) {
      return;
    }
    return ([
      h("div", { class: "transaction-info" },
        h("p", null,
          h("strong", null, "Kunde:"),
          " ",
          this.tx.customer.firstName,
          " ",
          this.tx.customer.lastName,
          h("br", null),
          h("strong", null, "Kundennummer:"),
          " ",
          this.tx.customer.customerNumber,
          h("br", null),
          h("strong", null, "Kontonummer:"),
          " ",
          this.tx.creditAccountNumber,
          h("br", null),
          h("strong", null, "Transaktions-ID:"),
          " ",
          this.tx.transactionId,
          h("br", null),
          h("strong", null, "Bestellwert:"),
          " ",
          this.orderAmount(),
          h("br", null)))
    ]);
  }
  getAlertFragment() {
    if (!this.alert) {
      return;
    }
    return h("p", { class: `easycredit-tx-alert ${this.alert.type}`, ref: (el) => this.alertElement = el }, this.alert.message);
  }
  getActionsFragment() {
    if (!this.tx || (!this.canShip() && !this.canRefund())) {
      return;
    }
    return ([
      h("div", null,
        this.getAlertFragment(),
        h("input", { value: this.tx.transactionId, type: "hidden", name: "easycredit-merchant[transaction_id]" }),
        h("p", null,
          h("label", { htmlFor: "easycredit-merchant-status" }, "Status"),
          h("br", null),
          h("select", { id: "easycredit-merchant-status", onInput: (e) => this.status = e.target.value, name: "easycredit-merchant[status]" },
            h("option", { value: "" }, "Bitte w\u00E4hlen ..."),
            this.canShip() && h("option", { value: "CAPTURE" }, "Lieferung"),
            this.canRefund() && h("option", { value: "REFUND" }, "R\u00FCckabwicklung"))),
        this.status === 'CAPTURE' &&
          h("p", { class: "tracking-number" },
            h("label", { htmlFor: "easycredit-merchant-tracking-number" }, "Trackingnummer (Versanddienstleister)"),
            h("br", null),
            h("input", { id: "easycredit-merchant-tracking-number", name: "easycredit-merchant[trackingNumber]", type: "text", onInput: (e) => this.trackingNumber = e.target.value, maxlength: "50" })),
        this.status === 'REFUND' &&
          h("p", { class: "refund" },
            h("span", { class: "amount" },
              h("label", { htmlFor: "easycredit-merchant-amount" }, "Minderung um "),
              h("br", null),
              h("input", { id: "easycredit-merchant-amount", name: "easycredit-merchant[amount]", type: "number", onInput: (e) => this.amount = e.target.value, value: this.amount, min: "0.01", max: this.tx.orderDetails.currentOrderValue }),
              " \u20AC / ",
              formatCurrency(this.tx.orderDetails.currentOrderValue))),
        h("p", null,
          h("button", { type: "button", class: "set_merchant_status", disabled: this.loading || !this.status, onClick: () => { this.updateTransaction(); } }, "Senden")))
    ]);
  }
  getNotAvailableFragment() {
    if (this.tx) {
      return;
    }
    if (this.youngerThanOneDay()) {
      return [
        h("span", null,
          "Die Transaktion ",
          h("strong", null, this.txId),
          " ist noch nicht verf\u00FCgbar. Es kann bis zu einem Tag dauern bis die Transaktion angezeigt wird.")
      ];
    }
    return [
      h("span", null,
        "Die Transaktion ",
        h("strong", null, this.txId),
        " ist nicht vorhanden. Bitte loggen Sie sich im ",
        h("a", { href: "https://partner.easycredit-ratenkauf.de/portal/" }, "Partnerportal"),
        " ein oder kontaktieren Sie unseren Partnerservice.")
    ];
  }
  render() {
    return ([h("div", { class: { 'easycredit-tx-manager': true, 'loading': this.loading } },
        h("div", { class: "spinner" }),
        this.getInfoFragment(),
        this.getProgressBarFragment(),
        this.getActionsFragment(),
        this.getNotAvailableFragment())
    ]);
  }
  static get is() { return "easycredit-merchant-manager"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-merchant-manager.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-merchant-manager.css"]
  }; }
  static get properties() { return {
    "txId": {
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
      "attribute": "tx-id",
      "reflect": false
    },
    "date": {
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
      "attribute": "date",
      "reflect": false
    }
  }; }
  static get states() { return {
    "tx": {},
    "loading": {},
    "status": {},
    "submitDisabled": {},
    "alert": {},
    "progressItems": {},
    "trackingNumber": {},
    "amount": {}
  }; }
}
