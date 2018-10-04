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

        try {
            $order = $eventArgs->getEntity();
            if (empty($order->getTransactionId())) {
                throw new \Exception('Die zugehörige ratenkauf by easyCredit Transaktion-ID dieser Bestellung ist nicht vorhanden.');
            }

           $merchantClient = Shopware()->Container()->get('easyCreditMerchant');
           $transactions = $merchantClient->search($order->getTransactionId());
           if (count($transactions) != 1) {
                throw new \Exception('Die zugehörige ratenkauf by easyCredit Transaktion existiert nicht.');
            }
            $merchantClient->cancelOrder($order->getTransactionId(),'WIDERRUF_VOLLSTAENDIG');

        } catch (\Exception $e) {
            $error = $e->getMessage().' '; 
            $error.= "Bitte ändern Sie den Bestellstatus zur Ratenzahlung manuell im Händler-Interface (https://app.easycredit.de/).";

            $GLOBALS['easycreditMerchantStatusChangedError'] = $error;
        }
    }
}
