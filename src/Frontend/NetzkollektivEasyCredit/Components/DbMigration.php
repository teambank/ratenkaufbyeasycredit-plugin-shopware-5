<?php
class EasyCredit_DbMigration
{
    public function migrate()
    {
        $this->_applyBrandRelaunch();
        $this->_applyBillPaymentMigration();
    }

    public function _applyBrandRelaunch()
    {
        Shopware()->Db()->query("
            UPDATE s_core_plugins Set description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf') WHERE name = 'NetzkollektivEasyCredit';
            UPDATE s_core_paymentmeans Set description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf') WHERE name = 'easycredit';
            UPDATE s_core_config_forms Set
                description = REPLACE(description, 'ratenkauf by easyCredit','easyCredit-Ratenkauf'),
                label = REPLACE(label, 'ratenkauf by easyCredit','easyCredit-Ratenkauf')
            WHERE name = 'NetzkollektivEasyCredit';
        ");
    }

    public function _applyBillPaymentMigration()
    {
        Shopware()->Db()->query("
            UPDATE IGNORE s_core_paymentmeans Set name = 'easycredit_ratenkauf' WHERE name = 'easycredit';
            UPDATE s_core_plugins Set description = REPLACE(description, 'easyCredit-Ratenkauf','easyCredit-Rechnung & Ratenkauf') WHERE name = 'NetzkollektivEasyCredit';
            UPDATE s_core_config_forms Set
                description = REPLACE(description, 'easyCredit-Ratenkauf','easyCredit'),
                label = REPLACE(label, 'easyCredit-Ratenkauf','easyCredit')
            WHERE name = 'NetzkollektivEasyCredit';
        ");
    }
}
