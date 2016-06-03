<?php
namespace Shopware\Plugins\NetzkollektivEasycredit\Components;

use ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod;

class BaseEasycreditPaymentMethod extends GenericPaymentMethod
{
    protected function _validate($paymentData) {
        $errors = array();
        if (empty($paymentData['sEasycreditAgreement'])) {
            $errors["sErrorFlag"]['sEasycreditAgreement'] = true;
            $errors["sErrorMessages"][] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')
                    ->get('ErrorFillIn','Bitte bestätigen Sie die Datenübermittlung.');
        }
        if (!empty(Shopware()->Session()->EasyCredit["apiError"])) {
            $errors["sErrorFlag"]['sEasycreditError'] = true;
            $errors["sErrorMessages"][] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')
                    ->get('ErrorFillIn',Shopware()->Session()->EasyCredit["apiError"]); 
        }
        return $errors;
    }
}

/**
 * Returns true, if the signature of GenericPaymentMethod#validate appears consistent with Shopware before version
 * 5.0.4-RC1.
 *
 * Since version 5.0.4-RC1, the parameter must be an array (with no type hint).
 * Before, it was an \Enlight_Controller_Request_Request.
 *
 * The commit that changed the signature of #validate is
 * <https://github.com/shopware/shopware/commit/0608b1a7b05e071c93334b29ab6bd588105462d7>.
 */
function needs_legacy_validate_signature() {
    $parentClass = new \ReflectionClass('ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod');
    /* @var $parameters \ReflectionParameter[] */
    $parameters = $parentClass->getMethod('validate')->getParameters();
    foreach ($parameters as $parameter) {
        // Newer Shopware versions use an array parameter named paymentData.
        if ($parameter->getName() === 'request') {
            return true;
        }
    }
    return false;
}
if (needs_legacy_validate_signature()) {
    class EasycreditPaymentMethod extends BaseEasycreditPaymentMethod {
        public function validate(\Enlight_Controller_Request_Request $request) {
            return parent::_validate($request->getParams());
        }
    }
} else {
    class EasycreditPaymentMethod extends BaseEasycreditPaymentMethod {
        public function validate($paymentData) {
            return parent::_validate($paymentData);
        }
    }
}
