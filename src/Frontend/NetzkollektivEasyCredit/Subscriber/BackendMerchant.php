<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Subscriber;

use Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap as Bootstrap;
use Enlight\Event\SubscriberInterface;

class BackendMerchant implements SubscriberInterface
{
    protected $bootstrap;

    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_EasycreditMerchant' => 'onGetControllerPath',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onPostDispatchBackendIndex',
        );
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
        $view->assign('easyCreditConfig', json_encode(array(
            'endpoints' => [
                'get' => $router->assemble(
                    array(
                        'module' => 'backend',
                        'controller' => 'EasycreditMerchant',
                        'action' => 'transaction'
                    )
                ),
                'list' => $router->assemble(
                    array(
                        'module' => 'backend',
                        'controller' => 'EasycreditMerchant',
                        'action' => 'transactions'
                    )
                ),
                'post' => $router->assemble(
                    array(
                        'module' => 'backend',
                        'controller' => 'EasycreditMerchant',
                        'action' => 'transactions'
                    )
                )
            ]
        )));
        $view->extendsTemplate('backend/index/backend-merchant.tpl');
    }

    public function onGetControllerPath() {
        $this->bootstrap->registerTemplateDir();
        return $this->bootstrap->Path() . 'Controllers/Backend/EasycreditMerchant.php';
    }
}
