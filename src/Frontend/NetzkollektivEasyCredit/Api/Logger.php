<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api;

class Logger implements \Netzkollektiv\EasyCreditApi\LoggerInterface {

    protected $_logger;

    protected $debug = false;

    public function __construct() {
        $this->_logger = Shopware()->Container()->get('pluginlogger');
        $this->_logger->getHandlers()[0]->getFormatter()->allowInlineLineBreaks(true);

        if (Shopware()->Config()->get('easycreditDebugLogging')) {
            $this->debug = true;
            $this->allowLineBreaks(true); // allow line breaks in log globally, when easycredit Debug Logging is active
        }
    }

    protected function allowLineBreaks($bool) {
        $handler = $this->_logger->getHandlers();
        if (
            is_array($handlers) 
            && isset($handlers[0]) 
            && $handlers[0] instanceof Monolog\Logger\StreamHandler
            && $handlers[0]->getFormatter() instanceof Monolog\Logger\LineFormatter
        ) {
            $handlers[0]->getFormatter()->allowInlineLineBreaks($bool); 
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

    public function logWarn($msg) {
        $this->_logger->warning(
            $this->_format($msg)
        );
        return $this;
    }

    public function logError($msg) {
        if (strpos($msg,'Der Finanzierungsbetrag liegt') === 0) {
            return $this->logInfo($msg);
        }

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
