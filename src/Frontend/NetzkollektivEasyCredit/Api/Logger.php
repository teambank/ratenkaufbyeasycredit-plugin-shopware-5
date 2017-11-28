<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Logger implements \Netzkollektiv\EasyCreditApi\LoggerInterface {

    protected $_logger;

    protected $debug = false;

    public function __construct() {
        $this->_logger = Shopware()->PluginLogger();

        if (Shopware()->Config()->get('easycreditDebugLogging')) {
            $this->debug = true;
        }
    }

    public function log($msg) {
        $this->logInfo($msg);
        return $this;
    }

    public function logDebug($msg) {
        if (!$this->debug) {
            return;
        }

        $this->_logger->info(
            $this->_format($msg)
        );
        return $this;
    }

    public function logInfo($msg) {
        if (!$this->debug) {
            return;
        }

        $this->_logger->info(
            $this->_format($msg)
        );
        return $this;
    }

    public function logError($msg) {
        $this->_logger->error(
            $this->_format($msg)
        );
        return $this;
    }

    public function _format($msg) {
        if (is_array($msg) || is_object($msg)) {
            return print_r($msg,true);
        }
        return $msg;
    }
}
