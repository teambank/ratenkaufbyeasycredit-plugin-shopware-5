'use strict';

const index = require('./index-54408337.js');

/*
 Stencil Client Patch Browser v2.12.1 | MIT Licensed | https://stenciljs.com
 */
const getDynamicImportFunction = (namespace) => `__sc_import_${namespace.replace(/\s|-/g, '_')}`;
const patchBrowser = () => {
    {
        // shim css vars
        index.plt.$cssShim$ = index.win.__cssshim;
    }
    // @ts-ignore
    const scriptElm = Array.from(index.doc.querySelectorAll('script')).find((s) => new RegExp(`\/${index.NAMESPACE}(\\.esm)?\\.js($|\\?|#)`).test(s.src) ||
            s.getAttribute('data-stencil-namespace') === index.NAMESPACE)
        ;
    const opts = scriptElm['data-opts'] || {} ;
    if ('onbeforeload' in scriptElm && !history.scrollRestoration /* IS_ESM_BUILD */) {
        // Safari < v11 support: This IF is true if it's Safari below v11.
        // This fn cannot use async/await since Safari didn't support it until v11,
        // however, Safari 10 did support modules. Safari 10 also didn't support "nomodule",
        // so both the ESM file and nomodule file would get downloaded. Only Safari
        // has 'onbeforeload' in the script, and "history.scrollRestoration" was added
        // to Safari in v11. Return a noop then() so the async/await ESM code doesn't continue.
        // IS_ESM_BUILD is replaced at build time so this check doesn't happen in systemjs builds.
        return {
            then() {
                /* promise noop */
            },
        };
    }
    {
        opts.resourcesUrl = new URL('.', new URL(scriptElm.getAttribute('data-resources-url') || scriptElm.src, index.win.location.href)).href;
        {
            patchDynamicImport(opts.resourcesUrl, scriptElm);
        }
        if (!index.win.customElements) {
            // module support, but no custom elements support (Old Edge)
            // @ts-ignore
            return Promise.resolve().then(function () { return require(/* webpackChunkName: "polyfills-dom" */ './dom-417ccd20.js'); }).then(() => opts);
        }
    }
    return index.promiseResolve(opts);
};
const patchDynamicImport = (base, orgScriptElm) => {
    const importFunctionName = getDynamicImportFunction(index.NAMESPACE);
    try {
        // test if this browser supports dynamic imports
        // There is a caching issue in V8, that breaks using import() in Function
        // By generating a random string, we can workaround it
        // Check https://bugs.chromium.org/p/chromium/issues/detail?id=990810 for more info
        index.win[importFunctionName] = new Function('w', `return import(w);//${Math.random()}`);
    }
    catch (e) {
        // this shim is specifically for browsers that do support "esm" imports
        // however, they do NOT support "dynamic" imports
        // basically this code is for old Edge, v18 and below
        const moduleMap = new Map();
        index.win[importFunctionName] = (src) => {
            const url = new URL(src, base).href;
            let mod = moduleMap.get(url);
            if (!mod) {
                const script = index.doc.createElement('script');
                script.type = 'module';
                script.crossOrigin = orgScriptElm.crossOrigin;
                script.src = URL.createObjectURL(new Blob([`import * as m from '${url}'; window.${importFunctionName}.m = m;`], {
                    type: 'application/javascript',
                }));
                mod = new Promise((resolve) => {
                    script.onload = () => {
                        resolve(index.win[importFunctionName].m);
                        script.remove();
                    };
                });
                moduleMap.set(url, mod);
                index.doc.head.appendChild(script);
            }
            return mod;
        };
    }
};

patchBrowser().then(options => {
  return index.bootstrapLazy([["easycredit-base.cjs",[[1,"easycredit-base"]]],["easycredit-box-flash_11.cjs",[[1,"easycredit-checkout",{"isActive":[4,"is-active"],"amount":[2],"webshopId":[1,"webshop-id"],"alert":[1],"paymentPlan":[1,"payment-plan"],"askForPrefix":[4,"ask-for-prefix"],"privacyApprovalForm":[32],"privacyCheckboxChecked":[32],"totals":[32],"installments":[32],"selectedInstallment":[32],"example":[32],"submitDisabled":[32]},[[0,"selectedInstallment","selectedInstallmentHandler"]]],[1,"easycredit-widget",{"webshopId":[1,"webshop-id"],"amount":[2],"installments":[32],"isValid":[32]}],[1,"easycredit-box-flash",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-listing",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-modal",{"isOpen":[32],"toggle":[64]}],[1,"easycredit-box-top",{"slideIndex":[32],"isScrolled":[32]}],[1,"easycredit-checkout-label",{"name":[1025],"message":[1025]}],[1,"easycredit-merchant-manager",{"txId":[1,"tx-id"],"date":[1025],"tx":[32],"loading":[32],"status":[32],"submitDisabled":[32],"alert":[32],"progressItems":[32],"trackingNumber":[32],"amount":[32]}],[1,"easycredit-merchant-status-widget",{"txId":[1,"tx-id"],"date":[1],"tx":[32],"loading":[32]}],[0,"easycredit-checkout-installments",{"showMoreButtonText":[1025,"show-more-button-text"],"installments":[8],"rows":[2],"collapsed":[32],"collapsing":[32],"_installments":[32],"selectedInstallmentValue":[32]},[[0,"selectedInstallment","selectedInstallmentHandler"]]],[4,"easycredit-modal",{"loading":[4],"loadingMessage":[1,"loading-message"],"show":[4],"isOpen":[1028,"is-open"],"close":[64],"open":[64],"toggle":[64]}]]]], options);
});
