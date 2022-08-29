# easyCredit-Ratenkauf Plugin for Shopware 5 - Installment Payment Plugin

easyCredit-Ratenkauf is the easiest and fastest installment payment solution of Germany. Join today to get the simplest way of partial payment for your POS and E-Commerce. easyCredit-Ratenkauf gives you the opportunity to offer installments as an additional payment method in your German WooCommerce store.

Traditional financing solutions are often connected with complicated application processes for customers. With easyCredit-Ratenkauf you can offer a simple, fully online and fast financing solution. Customers can use ‚easyCredit-Ratenkauf‘ in just a few steps: choose their purchases, calculate their preferred installments, enter their personal data, and pay. No paperwork, immediate approval, and complete flexibility throughout. Simple. Fair. Paying in installments with ‚easyCredit-Ratenkauf‘.

# Getting started
Are you interested in using easyCredit-Ratenkauf? Contact us now:
* [sales.ratenkauf@easycredit.de](https://store.shopware.com/en/easyc36021249341f/ratenkauf-by-easycredit.html#)
* +49 (0)911 5390 2726

or register at [easycredit-ratenkauf.de](https://www.easycredit-ratenkauf.de/registrierung.htm) and we will contact you.

**Please note that a valid contract is required to use the plug-in.**

# Installation

```
cd my-shopware-dir

git clone https://github.com/teambank/ratenkaufbyeasycredit-plugin-shopware-5.git sw-easycredit
cp -r sw-easycredit/src/Frontend/NetzkollektivEasyCredit engine/Shopware/Plugins/Local/Frontend/

./bin/console sw:plugin:refresh
./bin/console sw:plugin:install NetzkollektivEasyCredit
./bin/console sw:plugin:activate NetzkollektivEasyCredit
```

# Compatibility

This extension aims to be as compatible as possible with current, future versions of Shopware 5. This version is regularly tested with Shopware versions:

* >= v5.2.x 
* <= v5.7.13

Earlier versions of Shopware 5 may work, but are not actively tested anymore.

If you still have any problems, please open a ticket or contact [ratenkauf@easycredit.de](mailto:ratenkauf@easycredit.de).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

# Security
If you have discovered a security vulnerability, please email [opensource@teambank.de](mailto:opensource@teambank.de).
