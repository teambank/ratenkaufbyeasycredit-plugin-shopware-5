<?php
namespace Shopware\Plugins\NetzkollektivEasycredit\Subscriber;

use Enlight\Event\SubscriberInterface;

class Payment implements SubscriberInterface
{
    public static function getSubscribedEvents() {
        return array(
            'Shopware_Modules_Admin_InitiatePaymentClass_AddClass' => 'onAddPaymentClass'
        );
    }

    public function onAddPaymentClass(\Enlight_Event_EventArgs $args) {
        if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
            $dirs = $args->getReturn();
            $dirs['easycredit'] = 'Shopware\Plugins\NetzkollektivEasycredit\Components\EasycreditPaymentMethod';
            $args->setReturn($dirs);
        }
    }
}
