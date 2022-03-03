import { Component, Method, State, h } from '@stencil/core';
// import { EasycreditModal } from '../easycredit-modal/easycredit-modal'
export class EasycreditBoxModal {
  constructor() {
    this.isOpen = true;
  }
  async toggle() {
    this.isOpen = !this.isOpen;
  }
  /*
  // Automatic Popup opening
  $(document).ready(function() {
    var isshow = localStorage.getItem('isshow');
    if (isshow == null) {
      localStorage.setItem('isshow', 1);

      setTimeout(function() {
        jQuery('.easycredit-box-modal').addClass('easycredit-box-modal-active');
      }, 5000); // milliseconds
    }
  });

  $('.easycredit-box-modal-button').on('click', function (e) {
    $('.easycredit-box-modal').addClass('easycredit-box-modal-active');
  });
  $(".easycredit-box-modal-close").on('click', function (e) {
    $(".easycredit-box-modal").removeClass('easycredit-box-modal-active');
  });
  $(".easycredit-box-modal").on('click', function (e) {
    var $target = $(e.target);

    if ($target.hasClass('easycredit-box-modal')) {
      $(".easycredit-box-modal").removeClass('easycredit-box-modal-active');
    }
  });
  */
  render() {
    return ([
      h("div", { class: { 'ec-box-modal': true, 'show': this.isOpen } },
        h("div", { class: "ec-box-modal__inner" },
          h("div", { class: "ec-box-modal__close", onClick: () => this.toggle() },
            h("img", { src: "/build/assets/icon-close-white.svg" })),
          h("div", { class: "ec-box-modal__image" },
            h("div", { class: "ec-box-modal__image-logo" },
              h("img", { src: "/build/assets/ratenkauf-logo.svg" })),
            h("div", { class: "ec-box-modal__image-heading" },
              h("span", null, "Ganz entspannt"),
              " in Raten zahlen."),
            h("div", { class: "ec-box-modal__image-product" },
              h("img", { src: "/build/assets/bike.png" })),
            h("div", { class: "ec-box-modal__image-logo-secondary" },
              h("img", { src: "/build/assets/logo-finanzgruppe.png" }))),
          h("div", { class: "ec-box-modal__content" },
            h("div", { class: "ec-box-modal__content-heading" }, "Online Shoppen und bequem in Raten zahlen!"),
            h("div", { class: "ec-box-modal__content-description" }, "Der ratenkauf by easyCredit bietet Ihnen die M\u00F6glichkeit hier im Online-Shop bequem und einfach in Raten zu zahlen. Direkt von zu Hause und ganz ohne Risiko. Denn zuerst erhalten Sie Ihre Bestellung und bezahlen sp\u00E4ter in Ihren Wunschraten."),
            h("div", { class: "ec-box-modal__price" },
              h("div", { class: "ec-box-modal__price-start" }, "Einfach im Bezahlvorgang ratenkauf by easyCredit und Wunschrate w\u00E4hlen.")),
            h("div", { class: "ec-box-modal__content-heading" }, "Sofort - Flexibel - Transparent"))),
        h("div", { class: "ec-box-modal__backdrop", onClick: () => this.toggle() }))
    ]);
  }
  static get is() { return "easycredit-box-modal"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-box-modal.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-box-modal.css"]
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
