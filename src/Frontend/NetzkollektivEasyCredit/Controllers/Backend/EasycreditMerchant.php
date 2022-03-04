<?php
use Shopware\Models\Order\Order;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\QueryBuilder;

abstract class Shopware_Controllers_Backend_EasycreditMerchant_Abstract extends Shopware_Controllers_Backend_Application {

    /**
     * @var string
     */
    protected $model = Order::class;

    /**
     * @var string
     */
    protected $alias = 'sOrder';

    public function getPlugin() {
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit();
    }

    /**
     * @return QueryBuilder
     */
    private function prepareOrderQueryBuilder(QueryBuilder $builder)
    {
        $paymentId = $this->getPlugin()->getPayment()->getId();

        $builder->innerJoin(
            'sOrder.payment',
            'payment',
            Join::WITH,
            'payment.id = :paymentId'
        )->setParameter('paymentId', $paymentId, \PDO::PARAM_INT);

        $builder->leftJoin('sOrder.languageSubShop', 'languageSubShop')
            ->leftJoin('sOrder.customer', 'customer')
            ->leftJoin('sOrder.orderStatus', 'orderStatus')
            ->leftJoin('sOrder.paymentStatus', 'paymentStatus')
            ->leftJoin('sOrder.attribute', 'attribute')
            ->addSelect('languageSubShop')
            ->addSelect('payment')
            ->addSelect('customer')
            ->addSelect('orderStatus')
            ->addSelect('paymentStatus')
            ->addSelect('attribute');

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListQuery()
    {
        return $this->prepareOrderQueryBuilder(parent::getListQuery());
    }

    /**
     * {@inheritdoc}
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        //Sets the initial sort to orderTime descending
        if (!$sort) {
            $defaultSort = [
                'property' => 'orderTime',
                'direction' => 'DESC',
            ];
            $sort[] = $defaultSort;
        }

        $orderList = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        /*
         * After the removal of the order/payment status description in Shopware 5.5,
         * we need to add the translations manually.
         */
        $orderStatusNamespace = $this->container->get('snippets')->getNamespace('backend/static/order_status');
        $paymentStatusNamespace = $this->container->get('snippets')->getNamespace('backend/static/payment_status');

        $orderList['data'] = array_map(static function ($order) use ($orderStatusNamespace, $paymentStatusNamespace) {
            if (!isset($order['orderStatus']['description'])) {
                $order['orderStatus']['description'] = $orderStatusNamespace->get($order['orderStatus']['name']);
            }

            if (!isset($order['paymentStatus']['description'])) {
                $order['paymentStatus']['description'] = $paymentStatusNamespace->get($order['paymentStatus']['name']);
            }

            return $order;
        }, $orderList['data']);

        return $orderList;
    }

    public function transactionAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        
        $transactionId = $this->Request()->getParam('id');

        foreach ($this->get('easyCreditMerchant')->searchTransactions() as $transaction) {
            if ($transactionId == $transaction->vorgangskennungFachlich) {
                echo json_encode($transaction);
            }
        }
        exit;
    }

    public function transactionsAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        if ($this->Request()->getMethod() == 'POST') {
            return $this->_postTransactions();
        }

        $transactions = [];
        foreach ($this->get('easyCreditMerchant')->searchTransactions() as $transaction) {
            $transactions[] = (array)$transaction;
        }

        echo json_encode($transactions);
        exit;
    }

    protected function _postTransactions() {
        $client = $this->get('easyCreditMerchant');

        $params = json_decode($this->Request()->getRawBody());

        try {
            switch ($params->status) {
                case "LIEFERUNG":
                    $client->confirmShipment($params->id);
                    $success = true;
                    break;
                case "WIDERRUF_VOLLSTAENDIG":
                case "WIDERRUF_TEILWEISE":
                case "RUECKGABE_GARANTIE_GEWAEHRLEISTUNG":
                case "MINDERUNG_GARANTIE_GEWAEHRLEISTUNG":
                    $client->cancelOrder(
                        $params->id,
                        $params->status,
                        new DateTime(),
                        $params->amount
                    );
                    break;
                default:
                    throw new \Exception('Status "'.$params->status.'" does not have any action');
            } 
        } catch (\Exception $e) {
            $this->Response()->setStatusCode(500);
            echo 'Es ist ein Fehler aufgetreten. Bitte führen Sie die Statusmeldung über unser Partnerportal durch und überprüfen Sie die Logdateien.';
            return false;
        }
        return true;
    }
}

if (interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    class Shopware_Controllers_Backend_EasycreditMerchant extends Shopware_Controllers_Backend_EasycreditMerchant_Abstract implements \Shopware\Components\CSRFWhitelistAware {

        /**
         * Returns a list with actions which should not be validated for CSRF protection
         *
         * @return string[]
         */
        public function getWhitelistedCSRFActions()
        {
            return array(
                'transaction',
                'transactions'
            );
        }
    }
} else {
    class Shopware_Controllers_Backend_EasycreditMerchant extends Shopware_Controllers_Backend_EasycreditMerchant_Abstract {

    }
}

