<?php
require_once __DIR__.'/_init.php';
require_once __DIR__.'/_checkout-init.php';

use \Teambank\RatenkaufByEasyCreditApiV3\Integration;
use \Teambank\RatenkaufByEasyCreditApiV3\Service;

$checkout->start($wrappedTransactionInitRequest);

print_r($storage);

echo 'Go to: '.$checkout->getRedirectUrl().PHP_EOL;

waitForEnter();

$checkout->loadTransaction();

print_r($storage);

$checkout->authorize('100002345');

// polling for status until AUTHORIZED
do {
    waitForSeconds(5);

    $result = $checkout->loadTransaction();
}
while ($result->getStatus() !== Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformationResponse::STATUS_AUTHORIZED);

print_r($result);
