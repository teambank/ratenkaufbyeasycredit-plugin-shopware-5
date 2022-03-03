import { Component, h, Element, Method, Prop, Watch } from '@stencil/core';
export class EasycreditModal {
  constructor() {
    this.loading = false;
    this.loadingMessage = 'Loading...';
    this.isOpen = false;
  }
  watchShowHandler(shown) {
    if (shown) {
      this.open();
    }
    else {
      this.close();
    }
  }
  async close() {
    this.isOpen = false;
  }
  async open() {
    this.isOpen = true;
    this.element.querySelectorAll('[data-src]').forEach((el) => {
      el.src = el.dataset.src;
    });
  }
  async toggle() {
    (this.isOpen) ? this.close() : this.open();
  }
  getCloseIcon() {
    return h("svg", { width: "12px", height: "12px", viewBox: "0 0 12 12", version: "1.1", xmlns: "http://www.w3.org/2000/svg" },
      h("g", { id: "level0", stroke: "none", "stroke-width": "1", fill: "none", "fill-rule": "evenodd" },
        h("g", { id: "level1", transform: "translate(-874.000000, -447.000000)", fill: "#000000" },
          h("g", { id: "level2", transform: "translate(874.482759, 447.448276)" },
            h("rect", { id: "Rectangle", transform: "translate(5.172414, 5.172414) rotate(-45.000000) translate(-5.172414, -5.172414) ", x: "-1.29310345", y: "4.74137931", width: "12.9310345", height: "1", rx: "0.5" }),
            h("rect", { id: "Rectangle", transform: "translate(5.172414, 5.172414) rotate(45.000000) translate(-5.172414, -5.172414) ", x: "-1.29310345", y: "4.74137931", width: "12.9310345", height: "1", rx: "0.5" })))));
  }
  render() {
    return ([
      h("div", { class: { 'ec-modal': true, 'show': this.isOpen } },
        h("div", { class: "close", onClick: () => this.close() }, this.getCloseIcon()),
        h("h3", { class: "heading" },
          h("slot", { name: "heading" })),
        h("slot", { name: "content" })),
      h("div", { class: { 'ec-modal-backdrop': true, 'show': this.isOpen }, onClick: () => this.close() })
    ]);
  }
  static get is() { return "easycredit-modal"; }
  static get originalStyleUrls() { return {
    "$": ["easycredit-modal.scss"]
  }; }
  static get styleUrls() { return {
    "$": ["easycredit-modal.css"]
  }; }
  static get properties() { return {
    "loading": {
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
      "attribute": "loading",
      "reflect": false,
      "defaultValue": "false"
    },
    "loadingMessage": {
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
      "attribute": "loading-message",
      "reflect": false,
      "defaultValue": "'Loading...'"
    },
    "show": {
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
      "attribute": "show",
      "reflect": false
    },
    "isOpen": {
      "type": "boolean",
      "mutable": true,
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
      "attribute": "is-open",
      "reflect": false,
      "defaultValue": "false"
    }
  }; }
  static get methods() { return {
    "close": {
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
    },
    "open": {
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
    },
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
  static get elementRef() { return "element"; }
  static get watchers() { return [{
      "propName": "show",
      "methodName": "watchShowHandler"
    }]; }
}
