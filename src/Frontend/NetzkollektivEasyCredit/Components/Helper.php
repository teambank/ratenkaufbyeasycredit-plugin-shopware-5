<?php
class EasyCredit_Helper
{
    protected $container;

    public function __construct() {
        $this->container = Shopware()->Container();
    }

    public function getModule($moduleName) {
        return $this->container->get('modules')->getModule($moduleName);
    }

    public function getPlugin() {
        return $this->container->get('plugins')->Frontend()->NetzkollektivEasyCredit();
    }

    public function getSession() {
        return $this->container->get('session');
    }

    public function getPluginSession() {
        return $this->getSession()->offsetGet('EasyCredit');
    }

    public function getContainer() {
        return $this->container;
    }

    public function getEntityManager() {
        return $this->container->get('models');
    }

    public function getPaymentMethods () {
        return $this->getPlugin()->getPaymentMethods();
    }

    public function getPaymentMethodIds () {
        return array_map(function ($payment) {
            return $payment->getId();
        }, $this->getPaymentMethods());        
    }

    public function getActivePaymentMethods()
    {
        return array_filter($this->getPaymentMethods(), function ($payment) {
            return $payment->getActive();
        });
    }

    protected function getVariable($var, $module) {
        if (Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s'.$var)) {
            return Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s'.$var);
        }
        if (isset(Shopware()->Session()->offsetGet('sOrderVariables')['s'.$var])) {
            return Shopware()->Session()->offsetGet('sOrderVariables')['s'.$var];
        }
        return Shopware()->Modules()->{$module}()->{'sGet'.$var}();
    }

    public function getBasket() {
        return $this->getVariable('Basket', 'Basket');
    }

    public function getUser() {
        return $this->getVariable('UserData', 'Admin');
    }

    public function getSelectedDispatch() {
        return Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('sDispatch');
    }

    public function getSelectedPayment()
    {
        if ($paymentId = $this->getSession()->offsetGet('sPaymentID')) {
            return $paymentId;
        }
        if ($paymentId = $this->getUser()['additional']['payment']['id']) {
            return $paymentId;
        }
    }

    public function getPaymentType($paymentId) {
        $methods = $this->getPaymentMethods();
        foreach ($methods as $method) {
            if ($method->getId() !== (int) $paymentId) {
                continue;
            }

            if ($method->getName() == 'easycredit_ratenkauf') {
                return 'INSTALLMENT_PAYMENT';
            }
            if ($method->getName() == 'easycredit_rechnung') {
                return 'BILL_PAYMENT';
            }
        }
    }

    public function getMethodByPaymentType($paymentType) {
        return current(array_filter($this->getPaymentMethods(), function ($payment) use ($paymentType) {
            $type = $this->getPaymentType($payment->getId());
            if ($paymentType === $type ||
                $paymentType === str_replace('_PAYMENT', '', $type)
            ) {
                return true;
            }
            return false;
        }));
    }
}
