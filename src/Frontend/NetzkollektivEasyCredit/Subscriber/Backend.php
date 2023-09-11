<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap as Bootstrap;
use Enlight\Event\SubscriberInterface;
use Shopware\Models\Order\Repository as OrderRepository;

class Backend implements SubscriberInterface
{
    protected $bootstrap;

    protected $helper;

    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->helper = new \EasyCredit_Helper();
    }

    public static function getSubscribedEvents() {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentEasycredit' => 'onGetControllerPathPaymentEasycredit',
            'Enlight_Controller_Action_Backend_Order_Save' => 'preventShippingAddressChange',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'afterOrderSave',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Config' => 'addConfigFields',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_PluginManager' => 'addConfigFields',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onPostDispatchBackendIndex'
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function afterOrderSave($args) {
        if ($args->getSubject()->Request()->getActionName() != 'save') {
            return;
        }
        $this->handleMerchantStatusChangedError($args);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function handleMerchantStatusChangedError($args) {
        $key = 'easycreditMerchantStatusChangedError';
        if ($GLOBALS[$key]) {

            $_this = $args->get('subject');
            $_this->View()->assign(array(
                'success' => false,
                'data' => $_this->View()->data,                
                'message' => $GLOBALS[$key]
            ));            

            unset($GLOBALS[$key]);
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function preventShippingAddressChange($args) {
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

        /** @var OrderRepository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $query = $repository->getOrdersQuery(array(array('property' => 'orders.id', 'value' => $id)), array());

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
                    'message' => 'Die Lieferadresse kann bei mit easyCredit-Ratenkauf bezahlten Bestellungen nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung hierfür und legen Sie eine neue Bestellung an.'
                ));

                return true;
            }
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addConfigFields($args)
    {
        $event = $args->getName();

        /** @var \Shopware_Controllers_Backend_Customer $controller */
        $controller = $args->getSubject();

        $view = $controller->View();
        $request = $controller->Request();

        if ($request->getActionName() == 'load') {
            $this->migrateConfigField();

            if (is_callable($this->helper->getPlugin(),'assertMinimumVersion') && $this->helper->getPlugin()->assertMinimumVersion('5.0.0')) {
                $this->bootstrap->updateSpecialFieldTypes();
                $this->bootstrap->registerTemplateDir();
                if ( $event == 'Enlight_Controller_Action_PostDispatchSecure_Backend_Config' ) {
                    $view->extendsTemplate('backend/easycredit_config/helper/easycredit_intro.js');
                } else if ( $event == 'Enlight_Controller_Action_PostDispatchSecure_Backend_PluginManager' ) {
                    $view->extendsTemplate('backend/plugin_manager/helper/easycredit_intro.js');
                }
            }
        }
    }

    public function migrateConfigField () {
        // move config values to renamed config element && remove old config element
        $fieldId = (int) Shopware()->Db()->fetchOne("SELECT e.id FROM s_core_config_elements e
            INNER JOIN s_core_config_forms f ON e.form_id = f.id WHERE f.name= 'NetzkollektivEasyCredit' AND e.name = 'easycreditApiToken'
        ");
        if ($fieldId > 0) {
            Shopware()->Db()->query("
                UPDATE IGNORE
                    s_core_config_values vls INNER JOIN s_core_config_elements element ON element.id = vls.element_id
                    Set element_id = (
                        SELECT id FROM s_core_config_elements _element WHERE _element.name = 'easycreditApiPassword'
                    )
                WHERE element.id = ?;
                DELETE FROM s_core_config_values WHERE element_id = ?;
                DELETE FROM s_core_config_elements WHERE id = ?;
            ", [$fieldId, $fieldId, $fieldId]);
        }
    }

    public function onPostDispatchBackendIndex($args)
    {
        $action = $args->getSubject();
        $request = $action->Request();
        $response = $action->Response();
        $view = $action->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getActionName() !== 'index'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $router = $action->Front()->Router();
        $this->bootstrap->registerTemplateDir();
        $view->extendsTemplate('backend/index/backend-easycredit.tpl');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onGetControllerPathPaymentEasycredit($args) {
        return $this->helper->getPlugin()->Path() . 'Controllers/Backend/PaymentEasycredit.php';
    }
}
