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
$storage = new Storage();
$checkout = new Integration\Checkout(
    $apiInstance,
    $storage,
    new Integration\Util\AddressValidator()
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
