import { Component, State, h } from '@stencil/core';
export class EasycreditBoxTop {
  constructor() {
    this.slideIndex = 0;
    this.isScrolled = false;
  }
  componentWillLoad() {
    setInterval(() => {
      this.slideIndex = (this.slideIndex === 0) ? this.slideIndex + 1 : 0;
    }, 5000);
    document.addEventListener('scroll', () => {
      this.isScrolled = (window.scrollY >= 50);
    });
  }
  render() {
    return ([
      h("div", { class: { 'ec-box-top': true, 'orange': (this.slideIndex == 1), 'scrolled': this.isScrolled } },
        h("div", { class: "ec-box-top__slider" },
          h("div", { class: { 'ec-box-top__slide': true, 'slide-1': true, 'active': this.slideIndex === 0 } },
            h("div", { class: "ec-box-top__content" },
              h("div", { class: "ec-box-top__content-logo" },
                h("img", { src: "/build/assets/ratenkauf-logo.svg" })),
              h("div", { class: "ec-box-top__content-text" }, "Ganz entspannt in Raten zahlen."))),
          h("div", { class: { 'ec-box-top__slide': true, 'slide-2': true, 'active': this.slideIndex === 1 } },
            h("div", { class: "ec-box-top__content" },
              h("div", { class: "ec-box-top__content-logo" },
                h("img", { src: "/build/assets/ratenkauf-logo.svg" })),
              h("div", { class: "ec-box-top__content-text" }, "Hier im Shop schon ab 200\u20AC in Raten zahlen.")))))
    ]);
  }
  static get is() { return "easycredit-box-top"; }
  static get encapsulation() { return "shadow"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-box-top.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-box-top.css"]
  }; }
  static get states() { return {
    "slideIndex": {},
    "isScrolled": {}
  }; }
}
