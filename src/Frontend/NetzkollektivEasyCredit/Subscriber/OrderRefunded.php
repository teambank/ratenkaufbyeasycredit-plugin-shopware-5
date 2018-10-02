<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

class OrderRefunded extends OrderStatusChanged
{
    public function getConfigKey() {
        return 'easycreditMarkRefunded';
    }

    public function _onOrderStatusChanged(PreUpdateEventArgs $eventArgs) {
file_put_contents('/tmp/bla','refunded'.PHP_EOL,FILE_APPEND);


    }
}
