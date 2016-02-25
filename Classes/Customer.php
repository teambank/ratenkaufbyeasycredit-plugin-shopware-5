<?php

use Netzkollektiv\EasyCredit;


class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Customer implements EasyCredit\CustomerInterface
{
    protected $_user = null;

    public function __construct() {

        if ($this->isLoggedIn()) {
            $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
        }
    }

    public function isLoggedIn() {
        return (isset(Shopware()->Session()->sUserId) && !empty(Shopware()->Session()->sUserId));
    }

    public function getCreatedAt() {
        if ($this->_user) {
            return $this->_user->getFirstLogin()->format('Y-m-d');
        }

        return null;
    }

    public function getOrderCount() {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $query = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->getCustomerDetailQuery($this->_user->getId());
        $data = $query->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $data['orderCount'];
    }

    public function getRisk() {
        return false;
    }
}
