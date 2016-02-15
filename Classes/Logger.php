<?php
use Netzkollektiv\EasyCredit;

class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Logger implements EasyCredit\LoggerInterface {
    public function log($message, $logLevel = null) {
//        $message = \Doctrine\Common\Util\Debug::dump($message);
        $message = print_r($message, true);

        $logger = Shopware()->PluginLogger();

        switch ($logLevel) {
            case 'WARN':
                $logger->warning($message);
                break;
            case 'ERR':
                $logger->error($message);
            case 'INFO':
            default:
                $logger->info($message);
                break;

        }
    }
}
