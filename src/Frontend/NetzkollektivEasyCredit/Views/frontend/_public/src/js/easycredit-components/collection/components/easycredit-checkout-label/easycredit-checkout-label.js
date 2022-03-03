import { Component, Prop, h } from '@stencil/core';
export class EasycreditCheckoutLabel {
  constructor() {
    this.name = 'ratenkauf by easyCredit';
    this.message = 'Ganz entspannt in Raten zahlen.';
  }
  render() {
    return ([
      h("div", { class: "ec-checkout-label-container" },
        h("div", { class: "ec-checkout-label" },
          h("strong", null, this.name),
          h("br", null),
          h("small", null, this.message),
          h("svg", { width: "46px", height: "46px", viewBox: "0 0 46 46", version: "1.1", xmlns: "http://www.w3.org/2000/svg" },
            h("defs", null),
            h("g", { id: "ratenkauf-icon", stroke: "none", "stroke-width": "1", fill: "none", "fill-rule": "evenodd" },
              h("g", null,
                h("path", { d: "M46,23.0003853 C46,35.7027693 35.7025967,46 23,46 C10.2966326,46 0,35.7027693 0,23.0003853 C0,10.2972307 10.2966326,0 23,0 C35.7025967,0 46,10.2972307 46,23.0003853", id: "blue", fill: "#005DA9" }),
                h("polygon", { id: "orange", fill: "#EC6608", points: "19.1197164 22.579685 12 16 12 37 19.1197164 37 19.3713154 37 34 37" }),
                h("path", { d: "M25.7341311,8 L19.2650884,8 C15.2520812,8 12,11.2829473 12,15.3330708 L12,30 C12,25.9498765 15.2520812,22.6669292 19.2650884,22.6669292 L25.7341311,22.6653539 C29.7471384,22.6653539 33,19.3824066 33,15.3330708 C33,11.2829473 29.7471384,8 25.7341311,8", id: "white", fill: "#FFFFFF" }))))))
    ]);
  }
  static get is() { return "easycredit-checkout-label"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-checkout-label.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-checkout-label.css"]
  }; }
  static get properties() { return {
    "name": {
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
      "attribute": "name",
      "reflect": false,
      "defaultValue": "'ratenkauf by easyCredit'"
    },
    "message": {
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
      "attribute": "message",
      "reflect": false,
      "defaultValue": "'Ganz entspannt in Raten zahlen.'"
    }
  }; }
}
