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

    public function getPayment () {
        return $this->getPlugin()->getPayment();
    }

    protected function getVariable($var, $module) {
        if (Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s'.$var)) {
            return Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s'.$var);
        }
        $orderVariables = Shopware()->Session()->offsetGet('sOrderVariables');
        if (isset($orderVariables['s'.$var])) {
            return $orderVariables['s'.$var];
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
}
