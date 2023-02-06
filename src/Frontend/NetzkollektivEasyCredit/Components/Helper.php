<?php
class EasyCredit_Helper
{
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

    public function getContainer() {
        return $this->container;
    }

    public function getEntityManager() {
        return $this->container->get('models');
    }

    public function getPayment () {
        return $this->getPlugin()->getPayment();
    }
}