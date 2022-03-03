import { Component, Prop, State, h } from '@stencil/core';
import { fetchTransaction } from '../../utils/utils';
export class EasycreditMerchantStatusWidget {
  constructor() {
    this.tx = {};
    this.loading = false;
  }
  async componentWillLoad() {
    this.loading = true;
    fetchTransaction(this.txId).then((transaction) => {
      this.tx = transaction;
      this.loading = false;
    }).catch((e) => {
      console.error(e);
      this.loading = false;
    });
  }
  getStatusLabel() {
    let labels = {
      'REPORT_CAPTURE': 'Lieferung melden',
      'REPORT_CAPTURE_EXPIRING': '',
      'IN_BILLING': 'In Abrechnung',
      'BILLED': 'Abgerechnet',
      'EXPIRED': 'Abgelaufen',
      'REPORT_CAPTURE_FAILED': '',
      'REPORT_CAPTURE_EXPIRING_FAILED': '',
      'IN_BILLING_FAILED': '',
      'BILLED_FAILED': 'Abrechnung, fehlgeschlagen',
      'EXPIRED_FAILED': '',
      'REPORT_CAPTURE_PENDING': '',
      'REPORT_CAPTURE_EXPIRING_PENDING': '',
      'IN_BILLING_PENDING': '',
      'BILLED_PENDING': '',
      'EXPIRED_PENDING': ''
    };
    if (!this.tx || !this.tx.status) {
      return 'nicht verfügbar';
    }
    if (!labels[this.tx.status] || labels[this.tx.status] === '') {
      return this.tx.status;
    }
    return labels[this.tx.status];
  }
  render() {
    return ([h("div", { class: { 'easycredit-tx-status-widget': true, 'loading': this.loading } },
        h("span", { class: "logo" }),
        h("span", null, this.getStatusLabel()))
    ]);
  }
  static get is() { return "easycredit-merchant-status-widget"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-merchant-status-widget.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-merchant-status-widget.css"]
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
      "attribute": "date",
      "reflect": false
    }
  }; }
  static get states() { return {
    "tx": {},
    "loading": {}
  }; }
}
