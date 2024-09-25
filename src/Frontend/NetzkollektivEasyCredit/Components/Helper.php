<?php
class EasyCredit_Helper
{
    protected $container;

    public function __construct()
    {
        $this->container = Shopware()->Container();
    }

    public function getModule($moduleName)
    {
        return $this->container->get('modules')->getModule($moduleName);
    }

    public function getPlugin()
    {
        return $this->container->get('plugins')->Frontend()->NetzkollektivEasyCredit();
    }

    public function getSession()
    {
        return $this->container->get('session');
    }

    public function getPluginSession()
    {
        return $this->getSession()->offsetGet('EasyCredit');
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getEntityManager()
    {
        return $this->container->get('models');
    }

    public function getPaymentMethods()
    {
        return $this->getPlugin()->getPaymentMethods();
    }

    public function getPaymentMethodIds()
    {
        return array_map(function ($payment) {
            return $payment->getId();
        }, $this->getPaymentMethods());
    }

    public function getActivePaymentMethods()
    {
        return array_filter($this->getPaymentMethods(), function ($payment) {
            return $payment->getActive();
        });
    }

    protected function getVariable($var, $module)
    {
        if (Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s' . $var)) {
            return Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('s' . $var);
        }
        if (isset(Shopware()->Session()->offsetGet('sOrderVariables')['s' . $var])) {
            return Shopware()->Session()->offsetGet('sOrderVariables')['s' . $var];
        }
        return Shopware()->Modules()->{$module}()->{'sGet' . $var}();
    }

    public function getBasket()
    {
        return $this->getVariable('Basket', 'Basket');
    }

    public function getUser()
    {
        return $this->getVariable('UserData', 'Admin');
    }

    public function getSelectedDispatch()
    {
        return Shopware()->Front()->Plugins()->ViewRenderer()->Action()->View()->getAssign('sDispatch');
    }

    public function getSelectedPayment()
    {
        if ($paymentId = $this->getContainer()->get('front')->Request()->getPost('payment')) {
            return $paymentId; // redirect happens before save, so we need the post var for payment type determination
        }

        if ($paymentId = $this->getSession()->offsetGet('sPaymentID')) {
            return $paymentId;
        }
        if ($paymentId = $this->getUser()['additional']['payment']['id']) {
            return $paymentId;
        }
    }

    private $typeMapping = [
        'INSTALLMENT' => 'easycredit_ratenkauf',
        'BILL' => 'easycredit_rechnung'
    ];

    public function getPaymentType(int $paymentId)
    {
        $method = current(array_filter($this->getPaymentMethods(), function ($paymentMethod) use ($paymentId) {
            return $paymentMethod->getId() === (int) $paymentId;
        }));

        $typeMapping = array_flip($this->typeMapping);

        if ($method && isset($typeMapping[$method->getName()])) {
            return $typeMapping[$method->getName()];
        }
    }

    public function getMethodByPaymentType($paymentType)
    {
        $paymentType = str_replace('_PAYMENT', '', $paymentType);

        return current(array_filter($this->getPaymentMethods(), function ($payment) use ($paymentType) {
            return ($paymentType === $this->getPaymentType($payment->getId()));
        }));
    }

    public function getAvailableMethodTypes()
    {
        return array_map(function ($paymentMethod) {
            return $this->getPaymentType($paymentMethod->getId());
        }, $this->getActivePaymentMethods());
    }
}
