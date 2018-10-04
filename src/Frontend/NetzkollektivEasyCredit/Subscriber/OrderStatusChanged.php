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

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $order = $eventArgs->getEntity();
        if (!($order instanceof Order)
            || !$eventArgs->hasChangedField('orderStatus')
            || $order->getPayment()->getId() == null
            || !$this->get('plugins')->Frontend()->NetzkollektivEasyCredit()->isSelected($order->getPayment()->getId()) 
        ) {
            return;
        }

        $orderStatus = $eventArgs->getNewValue('orderStatus')->getId();
        if ($this->config($this->getConfigKey())
            && $this->config($this->getConfigKey().'Status') == $eventArgs->getNewValue('orderStatus')->getId()
        ) {
            $this->_onOrderStatusChanged($eventArgs);
        }
    }

    protected function config($key) {
        return $this->get('config')->getByNamespace('NetzkollektivEasyCredit', $key);
    }

    public function get($container) {
        return Shopware()->Container()->get($container);
    }
}
