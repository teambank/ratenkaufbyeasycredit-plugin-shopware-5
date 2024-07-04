<?php
use Shopware\Models\Order\Order;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\QueryBuilder;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;

abstract class Shopware_Controllers_Backend_EasycreditMerchant_Abstract extends Shopware_Controllers_Backend_Application {

    /**
     * {@inheritdoc}
     */
    protected $model = Order::class;

    /**
     * @var string
     */
    protected $alias = 'sOrder';

    /**
     * @return QueryBuilder
     */
    private function prepareOrderQueryBuilder(QueryBuilder $builder)
    {
        $helper = new EasyCredit_Helper();

        $builder->innerJoin(
            'sOrder.payment',
            'payment',
            Join::WITH,
            $builder->expr()->in('payment.id', ':paymentIds')
        );        
        $builder->setParameter('paymentIds', $helper->getPaymentMethodIds(), Connection::PARAM_INT_ARRAY);

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

        $builder->where("sOrder.transactionId != ''");

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

    protected function respondWithJson ($content, $code = 200) {
        if (method_exists($this->Response(),'setHeader')) {
            $this->Response()->setHeader('content-type','application/json');
        } else {
            $this->Response()->headers->set('content-type', 'application/json');
        }

        http_response_code($code);
        echo json_encode($content);
        exit; // NOSONAR
    }

    public function transactionsAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        
        $transactionIds = $this->Request()->getParam('ids');

        $response = $this->get('easyCreditMerchant')
            ->apiMerchantV3TransactionGet(null, null,  null, 100, null, null, null, null, array('tId' => $transactionIds));

        return $this->respondWithJson($response);
    }

    public function transactionAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        
        $transactionId = $this->Request()->getParam('id');

        $response = $this->get('easyCreditMerchant')
            ->apiMerchantV3TransactionTransactionIdGet($transactionId);

        return $this->respondWithJson($response);
    }

    public function captureAction()
    {
        try {
            $transactionId = $this->Request()->getParam('id');

            if (method_exists($this->Request(),'getContent')) {
                $requestData = json_decode($this->Request()->getContent());
            } else {
                $requestData = json_decode($this->Request()->getRawBody());
            }

            $response = $this->get('easyCreditMerchant')
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $transactionId,
                    new CaptureRequest(['trackingNumber' => $requestData->trackingNumber])
                );
            return $this->respondWithJson($response);
        } catch (ApiException $e) {
            $this->respondWithJson($e->getResponseBody(), $e->getCode());
        } catch (\Throwable $e) {
            return $this->respondWithJson(['error' => $e->getMessage()], 500);
        }
    }

    public function refundAction()
    {
        try {
            $transactionId = $this->Request()->getParam('id');

            if (method_exists($this->Request(),'getContent')) {
                $requestData = json_decode($this->Request()->getContent());
            } else {
                $requestData = json_decode($this->Request()->getRawBody());
            }
            
            $response = $this->get('easyCreditMerchant')
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $transactionId,
                    new RefundRequest(['value' => $requestData->value])
                );

            return $this->respondWithJson($response);
        } catch (ApiException $e) {
            return $this->respondWithJson($e->getResponseBody(), $e->getCode());
        } catch (\Throwable $e) {
            return $this->respondWithJson(['error' => $e->getMessage()], 500);
        }
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
                'transactions',
                'capture',
                'refund'
            );
        }
    }
} else {
    class Shopware_Controllers_Backend_EasycreditMerchant extends Shopware_Controllers_Backend_EasycreditMerchant_Abstract {

    }
}
