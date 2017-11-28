<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentEasycredit' => 'onGetControllerPathPaymentEasycredit'
        );
    }

    public function onGetControllerPathPaymentEasycredit(\Enlight_Event_EventArgs $args) {
        return $this->Path() . 'Controllers/Backend/PaymentEasycredit.php';
    }

    public function Path() {
        return $this->getPlugin()->Path();
    }

    public function getPlugin() {
        return Shopware()->Plugins()->Frontend()->NetzkollektivEasyCredit();
    }

}
