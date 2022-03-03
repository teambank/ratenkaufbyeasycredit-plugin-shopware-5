<?php
use \Teambank\RatenkaufByEasyCreditApiV3\Integration;
use \Teambank\RatenkaufByEasyCreditApiV3\Service;

$wrappedTransactionInitRequest = new \Teambank\RatenkaufByEasyCreditApiV3\Integration\TransactionInitRequestWrapper([
    'transactionInitRequest' => getInitRequest(),
    'invoiceFirstName' => 'Ralfi',
    'invoiceLastName' => 'Ratenkaufi',
    'company' => 'TurmoHummel GmbH'
]);

class Storage implements Integration\StorageInterface  {
    protected $data = [];

    public function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }
    public function get($key) {
        return $this->data[$key];

    }
    public function clear() {
        $this->data = [];
    }
}
class Logger implements \Psr\Log\LoggerInterface {
    public function log($level, $message, array $context = array()) {}
    public function emergency($message, array $context = array()) {}
    public function alert($message, array $context = array()) {}
    public function critical($message, array $context = array()) {}
    public function error($message, array $context = array()) {}
    public function info($message, array $context = array()) {}
    public function notice($message, array $context = array()) {}
    public function warning($message, array $context = array()) {}
    public function debug($message, array $context = array()) {}
}

$storage = new Storage();
$checkout = new Integration\Checkout(
    $webshopApiInstance,
    $transactionApiInstance,
    $installmentplanApiInstance,
    $storage,
    new Integration\Util\AddressValidator(),
    new Integration\Util\PrefixConverter(),
    new Logger()
);

function waitForEnter() {
    $handle = fopen ("php://stdin","r");

    echo "Press enter once finished the payment terminal:";

    if ($line = trim(fgets($handle), "\n\r")) {
        return;
    }

    fclose($handle);
}

function waitForSeconds($seconds) {
    foreach (range(1,5) as $index) {
        echo '.';
        sleep(1);
    }
    echo PHP_EOL;
}
