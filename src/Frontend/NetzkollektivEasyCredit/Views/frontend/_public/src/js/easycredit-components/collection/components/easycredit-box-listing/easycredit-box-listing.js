import { Component, Method, State, h } from '@stencil/core';
export class EasycreditBoxListing {
  constructor() {
    this.isOpen = false;
  }
  async toggle() {
    this.isOpen = !this.isOpen;
  }
  render() {
    return ([
      h("div", { class: "ec-box-listing" },
        h("div", { class: "ec-box-listing__inner" },
          h("div", { class: "ec-box-listing__image" },
            h("div", { class: "ec-box-listing__image-logo" },
              h("img", { src: "/build/assets/ratenkauf-logo.svg" })),
            h("div", { class: "ec-box-listing__image-heading" },
              h("span", null, "Ganz entspannt"),
              " ",
              h("br", null),
              "in Raten zahlen."),
            h("div", { class: "ec-box-listing__image-product" },
              h("img", { src: "/build/assets/bike-quer.png" }))),
          h("div", { class: "ec-box-listing__content" },
            h("div", { class: "ec-box-listing__content-heading" }, "Online Shoppen und bequem in Raten zahlen!"),
            h("div", { class: "ec-box-listing__content-description" }, "Der ratenkauf by easyCredit bietet Ihnen die M\u00F6glichkeit hier im Online-Shop bequem und einfach in Raten zu zahlen. Direkt von zu Hause und ganz ohne Risiko. Denn zuerst erhalten Sie Ihre Bestellung und bezahlen sp\u00E4ter in Ihren Wunschraten."))),
        h("div", { class: "ec-box-listing__price active" },
          h("div", { class: "ec-box-listing__price-start" }, "Einfach im Bezahlvorgang ratenkauf by easyCredit und Wunschrate w\u00E4hlen.")))
    ]);
  }
  static get is() { return "easycredit-box-listing"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-box-listing.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-box-listing.css"]
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
