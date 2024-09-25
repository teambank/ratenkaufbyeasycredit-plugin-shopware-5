<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Teambank\EasyCreditApiV3\ApiException;
use Teambank\EasyCreditApiV3\Model\CaptureRequest;
use Teambank\EasyCreditApiV3\Model\ConstraintViolation;

class OrderShipped extends OrderStatusChanged
{
    public function getConfigKey() {
        return 'easycreditMarkShipped';
    }
    
    public function _onOrderStatusChanged(PreUpdateEventArgs $eventArgs) {
        try {
            try {
                $order = $eventArgs->getEntity();
                $txId = $order->getTransactionId();
                if (empty($txId)) {
                    throw new \Exception('Die zugehörige easyCredit Transaktion-ID dieser Bestellung ist nicht vorhanden.');
                }

                $merchantClient = Shopware()->Container()->get('easyCreditMerchant');
                $this->get('easyCreditMerchant')
                    ->apiMerchantV3TransactionTransactionIdCapturePost(
                        $txId,
                        new CaptureRequest([])
                    );
            } catch (ApiException $e) {
                if ($e->getResponseObject() instanceof ConstraintViolation) {
                    $error = 'easyCredit: ';
                    foreach ($e->getResponseObject()->getViolations() as $violation) {
                        $error .= $violation->getMessage();
                    }
                    return $this->handleError($error);
                }

                throw $e;
            } 
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }

    }
}
