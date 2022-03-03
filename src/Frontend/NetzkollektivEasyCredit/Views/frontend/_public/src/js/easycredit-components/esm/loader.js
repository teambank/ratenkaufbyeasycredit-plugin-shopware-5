import { C as CSS, p as plt, w as win, a as promiseResolve, b as bootstrapLazy } from './index-ea9637fe.js';

/*
 Stencil Client Patch Esm v2.12.1 | MIT Licensed | https://stenciljs.com
 */
const patchEsm = () => {
    // NOTE!! This fn cannot use async/await!
    // @ts-ignore
    if (!(CSS && CSS.supports && CSS.supports('color', 'var(--c)'))) {
        // @ts-ignore
        return import(/* webpackChunkName: "polyfills-css-shim" */ './css-shim-17041dc7.js').then(() => {
            if ((plt.$cssShim$ = win.__cssshim)) {
                return plt.$cssShim$.i();
            }
            else {
                // for better minification
                return 0;
            }
        });
    }
    return promiseResolve();
};

const defineCustomElements = (win, options) => {
  if (typeof window === 'undefined') return Promise.resolve();
  return patchEsm().then(() => {
  return bootstrapLazy([["easycredit-base",[[1,"easycredit-base"]]],["easycredit-box-flash_11",[[1,"easycredit-checkout",{"isActive":[4,"is-active"],"amount":[2],"webshopId":[1,"webshop-id"],"alert":[1],"paymentPlan":[1,"payment-plan"],"askForPrefix":[4,"ask-for-prefix"],"privacyApprovalForm":[32],"privacyCheckboxChecked":[32],"totals":[32],"installments":[32],"selectedInstallment":[32],"example":[32],"submitDisabled":[32]},[[0,"selectedInstallment","selectedInstallmentHandler"]]],[1,"easycredit-widget",{"webshopId":[1,"webshop-id"],"amount":[2],"installments":[32],"isValid":[32]}],[1,"easycredit-box-flash",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-listing",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-modal",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-top",{"slideIndex":[32],"isScrolled":[32]}],[1,"easycredit-checkout-label",{"name":[1025],"message":[1025]}],[1,"easycredit-merchant-manager",{"txId":[1,"tx-id"],"date":[1025],"tx":[32],"loading":[32],"status":[32],"submitDisabled":[32],"alert":[32],"progressItems":[32],"trackingNumber":[32],"amount":[32]}],[1,"easycredit-merchant-status-widget",{"txId":[1,"tx-id"],"date":[1],"tx":[32],"loading":[32]}],[0,"easycredit-checkout-installments",{"showMoreButtonText":[1025,"show-more-button-text"],"installments":[8],"rows":[2],"collapsed":[32],"collapsing":[32],"_installments":[32],"selectedInstallmentValue":[32]},[[0,"selectedInstallment","selectedInstallmentHandler"]]],[4,"easycredit-modal",{"loading":[4],"loadingMessage":[1,"loading-message"],"show":[4],"isOpen":[1028,"is-open"],"close":[64],"open":[64],"toggle":[64]}]]]], options);
  });
};

export { defineCustomElements };
