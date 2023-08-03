<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\ConstraintViolation;

class OrderRefunded extends OrderStatusChanged
{
    public function getConfigKey() {
        return 'easycreditMarkRefunded';
    }

    public function _onOrderStatusChanged(PreUpdateEventArgs $eventArgs) {

        try {
            try {
                $order = $eventArgs->getEntity();
                $transactionId = $order->getTransactionId();
                if (empty($transactionId)) {
                    throw new \Exception('Die zugehörige easyCredit-Ratenkauf Transaktion-ID dieser Bestellung ist nicht vorhanden.');
                }

                $merchantClient = Shopware()->Container()->get('easyCreditMerchant');
                $this->get('easyCreditMerchant')
                    ->apiMerchantV3TransactionTransactionIdRefundPost(
                        $transactionId,
                        new RefundRequest(['value'=> $order->getInvoiceAmount()])
                    );
            } catch (ApiException $e) {
                if ($e->getResponseObject() instanceof ConstraintViolation) {
                    $error = 'easyCredit-Ratenkauf: ';
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
