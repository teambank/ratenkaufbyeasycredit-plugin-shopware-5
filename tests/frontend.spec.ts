import { test, expect } from "@playwright/test";
import { takeScreenshot, scaleDown } from "./utils";
import { goToProduct } from "./common.ts";

test.beforeEach(scaleDown);
test.afterEach(takeScreenshot);

test.describe("Widget should be visible @product", () => {
  test("widgetProduct", async ({ page }) => {
    await goToProduct(page);
    await expect(
      await page
        .locator('[itemprop="offers"]')
        .getByText(/Finanzieren ab.+?Monat/)
    ).toBeVisible();
  });
});

test.describe("Widget should be visible outside amount constraint @product", () => {
  test("widgetProductOutsideAmount", async ({ page }) => {
    await goToProduct(page, "below50");
    await expect(
      await page
        .locator('[itemprop="offers"]')
        .getByText(/Finanzieren ab.+?Bestellwert/)
    ).toBeVisible();
  });
});
