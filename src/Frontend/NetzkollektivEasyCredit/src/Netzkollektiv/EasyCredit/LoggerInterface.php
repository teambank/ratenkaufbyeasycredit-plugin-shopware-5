<?php
namespace Netzkollektiv\EasyCredit;

interface LoggerInterface {
    public function log($message, $logLevel = null);
}
