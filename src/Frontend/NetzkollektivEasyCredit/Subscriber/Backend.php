<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentEasycredit' => 'onGetControllerPathPaymentEasycredit',
            'Enlight_Controller_Action_Backend_Order_Save' => 'preventShippingAddressChange'
        );
    }

    public function preventShippingAddressChange(\Enlight_Event_EventArgs $args) {
        $_this = $args->get('subject');

        $postData = $_this->Request()->getParams();
        if (isset($postData['payment'][0]['name']) && $postData['payment'][0]['name'] != 'easycredit') {
            return;
        }
        if (!isset($postData['shipping'])) {
            return;
        }

        $id = $_this->Request()->getParam('id', null);
        if (empty($id)) {
            return;
        }

        $query = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->getOrdersQuery(array(array('property' => 'orders.id', 'value' => $id)), array());

        $orderData = $query->getArrayResult();

        if (!isset($orderData[0]['shipping'])) {
            return;
        }
        $orderData = $orderData[0]['shipping'];
        $postData = $postData['shipping'][0];

        foreach (array('firstName','lastName','street','zipCode','city','country_id') as $attribute) {
            if ($orderData[$attribute] != $postData[$attribute]) {

                $_this->View()->assign(array(
                    'success' => false,
                    'data' => $orderData[0],
                    'message' => 'Die Lieferadresse kann bei mit ratenkauf by easyCredit bezahlten Bestellungen nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung hierfür und legen Sie eine neue Bestellung an.'
                ));

                return true;
            }
        }
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
