import { Component, Method, State, h } from '@stencil/core';
export class EasycreditBoxFlash {
  constructor() {
    this.isOpen = false;
  }
  async toggle() {
    this.isOpen = !this.isOpen;
  }
  render() {
    return ([
      h("div", { class: { 'ec-box-flash': true, 'active': this.isOpen }, onClick: () => this.toggle() },
        h("div", { class: "ec-box-flash__inner" },
          h("div", { class: "ec-box-flash__close" },
            h("img", { src: "/build/assets/icon-close-white.svg" })),
          h("div", { class: "ec-box-flash__image" },
            h("div", { class: "ec-box-flash__image-logo" },
              h("img", { src: "/build/assets/ratenkauf-logo.svg" })),
            h("div", { class: "ec-box-flash__price" },
              h("div", { class: "ec-box-flash__price-start" }, "Einfach im Bezahlvorgang ratenkauf by easyCredit und Wunschrate w\u00E4hlen.")),
            h("div", { class: "ec-box-flash__image-product" },
              h("img", { src: "/build/assets/bike-quer.png" })),
            h("div", { class: "ec-box-flash__image-logo-secondary" },
              h("img", { src: "/build/assets/logo-finanzgruppe.png" }))),
          h("div", { class: "ec-box-flash__content" },
            h("div", { class: "ec-box-flash__content-heading" }, "Ganz entspannt in Raten zahlen."),
            h("div", { class: "ec-box-flash__content-description" }, "Der ratenkauf by easyCredit bietet Ihnen die M\u00F6glichkeit hier im Online-Shop bequem und einfach in Raten zu zahlen. Direkt von zu Hause und ganz ohne Risiko. Denn zuerst erhalten Sie Ihre Bestellung und bezahlen sp\u00E4ter in Ihren Wunschraten."),
            h("div", { class: "ec-box-flash__content-heading" }, "Sofort - Flexibel - Transparent"))))
    ]);
  }
  static get is() { return "easycredit-box-flash"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-box-flash.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-box-flash.css"]
  }; }
  static get states() { return {
    "isOpen": {}
  }; }
  static get methods() { return {
    "toggle": {
      "complexType": {
        "signature": "() => Promise<void>",
        "parameters": [],
        "references": {
          "Promise": {
            "location": "global"
          }
        },
        "return": "Promise<void>"
      },
      "docs": {
        "text": "",
        "tags": []
      }
    }
  }; }
}
