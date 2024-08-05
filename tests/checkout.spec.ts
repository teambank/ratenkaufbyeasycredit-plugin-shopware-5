import { test, expect } from "@playwright/test";
import { randomize, takeScreenshot, scaleDown, delay } from "./utils";
import {
  goToProduct,
  addCurrentProductToCart,
  selectAndProceed,
  startExpress,
  fillCheckout,
  goThroughPaymentPage,
  confirmOrder,
  checkAddressInvalidation,
  checkAmountInvalidation
} from "./common";
import { PaymentTypes } from "./types.ts";

test.beforeEach(scaleDown);
test.afterEach(takeScreenshot);

test.describe("go through standard @installment", () => {
  test("standardCheckoutInstallment", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");

    await fillCheckout(page);
    await selectAndProceed({ page, paymentType: PaymentTypes.INSTALLMENT });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
  });
});

test.describe("go through standard @bill", () => {
  test("standardCheckoutBill", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");

    await fillCheckout(page);

    await selectAndProceed({ page, paymentType: PaymentTypes.BILL });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
  });
});

test.describe("go through @express @installment", () => {
  test("expressCheckout", async ({ page }) => {
    await goToProduct(page);

    await startExpress({ page, paymentType: PaymentTypes.INSTALLMENT });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
      express: true,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
  });
});

test.describe("go through @express @bill", () => {
  test("expressCheckout", async ({ page }) => {
    await goToProduct(page);

    await startExpress({ page, paymentType: PaymentTypes.BILL });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.BILL,
      express: true,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
  });
});

test.describe("company should not be able to pay @bill @installment", () => {
  test("companyBlocked", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");
    await fillCheckout(page, true);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page
        .locator(`easycredit-checkout-label[payment-type=${paymentType}]`)
        .click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText(
        "Die Zahlung mit easyCredit ist nur für Privatpersonen möglich."
      );
    }
  });
});

test.describe("amount change should invalidate payment", () => {
  test("checkoutAmountChange", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");
    await fillCheckout(page);

    await selectAndProceed({ page, paymentType: PaymentTypes.INSTALLMENT });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT
    });

    await checkAmountInvalidation(page);
  });
});

test.describe("address change should invalidate payment", () => {
  test("checkoutAddressChange", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");
    await fillCheckout(page);

    await selectAndProceed({ page, paymentType: PaymentTypes.INSTALLMENT });

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT
    });

    await checkAddressInvalidation(page);
  });
});


test.describe("address change should invalidate payment", () => {
  test("expressCheckoutAddressChange", async ({ page }) => {
    await goToProduct(page);
    await startExpress({ page, paymentType: PaymentTypes.INSTALLMENT });    

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
      express: true,
    });

    await checkAddressInvalidation(page);
  });
});

test.describe("amount change should invalidate payment @express", () => {
  test("expressCheckoutAmountChange", async ({ page }) => {
    await goToProduct(page);
    await startExpress({ page, paymentType: PaymentTypes.INSTALLMENT });    

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
      express: true,
    });

    await checkAmountInvalidation(page);
  });

test.describe("product below amount constraint should not be buyable @bill @installment", () => {
  test("productOutsideAmountConstraints", async ({ page }) => {
    await goToProduct(page, "below50");
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");
    await fillCheckout(page);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page
        .locator(`easycredit-checkout-label[payment-type=${paymentType}]`)
        .click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText("liegt außerhalb der zulässigen Beträge");
    }
  });
});

test.describe("product above amount constraint should not be buyable @bill @installment", () => {
  test("productOutsideAmountConstraints", async ({ page }) => {
    await goToProduct(page, "above10000");
    await addCurrentProductToCart(page);

    await page.goto("checkout/confirm");
    await fillCheckout(page);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page
        .locator(`easycredit-checkout-label[payment-type=${paymentType}]`)
        .click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText("liegt außerhalb der zulässigen Beträge");
    }
  });
});
});
