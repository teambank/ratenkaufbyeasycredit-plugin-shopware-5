<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

abstract class OrderStatusChanged implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate
        ];
    }

    abstract public function getConfigKey();

    protected function handleError ($error) {
        $error.= " Bitte ändern Sie den Bestellstatus zur Ratenzahlung manuell im Partner-Portal (https://partner.easycredit-ratenkauf.de/portal).";
        $GLOBALS['easycreditMerchantStatusChangedError'] = $error;
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $helper = new \EasyCredit_Helper();

        $order = $eventArgs->getEntity();
        if (!($order instanceof Order)
            || !$eventArgs->hasChangedField('orderStatus')
            || $order->getPayment()->getId() == null
            || !$helper->getPlugin()->isSelected($order->getPayment()->getId()) 
        ) {
            return;
        }

        if ($this->config($this->getConfigKey())
            && $this->config($this->getConfigKey().'Status') == $eventArgs->getNewValue('orderStatus')->getId()
        ) {
            $this->_onOrderStatusChanged($eventArgs);
        }
    }

    abstract protected function _onOrderStatusChanged(PreUpdateEventArgs $eventArgs);

    protected function config($key) {
        return $this->get('config')->getByNamespace('NetzkollektivEasyCredit', $key);
    }

    public function get($container) {
        return Shopware()->Container()->get($container);
    }
}
