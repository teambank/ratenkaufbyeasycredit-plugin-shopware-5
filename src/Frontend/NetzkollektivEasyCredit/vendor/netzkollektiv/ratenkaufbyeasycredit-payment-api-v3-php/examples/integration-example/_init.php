<?php
require_once(__DIR__  .  '/../../vendor/autoload.php');

function getInitRequest() {
    return new \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInitRequest([
        'financingTerm' => 12,
        'orderDetails' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\OrderDetails([
            'orderValue' => 200,
            'orderId' => '100002345',
            'numberOfProductsInShoppingCart' => 1,
            'invoiceAddress' => new Teambank\RatenkaufByEasyCreditApiV3\Model\Address([
                'address' => 'Beuthener Str. 25',
                'additionalAddressInformation' => 'z Hd. Herr Ralf Ratenkauf',
                'zip' => '90471',
                'city' => 'Nürnberg',
                'country' => 'DE'
            ]),
            'shippingAddress' => new Teambank\RatenkaufByEasyCreditApiV3\Model\ShippingAddress([
                'firstName' => 'Ralf',
                'lastName' => 'Ratenkauf',
                'address' => 'Beuthener Str. 25',
                'additionalAddressInformation' => 'z Hd. Herr Ralf Ratenkauf',
                'zip' => '90471',
                'city' => 'Nürnberg',
                'country' => 'DE'
            ]),
            'shoppingCartInformation' => [
                new Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem([
                    'productName' => 'E-Bike',
                    'quantity' => '1',
                    'price' => '2499.99',
                    'manufacturer' => 'Magetique',
                    'productCategory' => 'Bikes',
                    'articleNumber' => [
                        new \Teambank\RatenkaufByEasyCreditApiV3\Model\ArticleNumberItem([
                            'numberType' => 'sku',
                            'number' => 'magetique-ebike'
                        ])
                    ] 
                ])
            ]
        ]),
        'shopsystem' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem([
            'shopSystemManufacturer' => 'NETZKOLLEKTIV API Evaluation',
            'shopSystemModuleVersion' => '0.1'
        ]),
        'customer' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Customer([
            'gender' => \Teambank\RatenkaufByEasyCreditApiV3\Model\Customer::GENDER_MR,
            'firstName' => 'Ralf',
            'lastName' => 'Ratenkauf',
            'birthDate' => '1980-04-01',
            'birthName' => 'Ratenkauf',
            'birthPlace' => 'Fürth',
            'title' => \Teambank\RatenkaufByEasyCreditApiV3\Model\Customer::TITLE_DR,
            'contact' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Contact([
                'email' => 'service@easycredit-ratenkauf.de',
                'mobilePhoneNumber' => '01701234567',
                'skipMobilePhoneNumberCheck' => false,
                'phoneNumber' => '0911 5390-2726'
            ]),
            'bank' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Bank([
                'iban' => 'DE88100900001234567892'
            ]),
            'employment' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Employment([
                'employmentType' => \Teambank\RatenkaufByEasyCreditApiV3\Model\Employment::EMPLOYMENT_TYPE_EMPLOYEE,
                'monthlyNetIncome' => 1999.31
            ])
        ]),
        'customerRelationship' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship([
            'customerStatus' => \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship::CUSTOMER_STATUS_NEW_CUSTOMER,
            'customerSince' => new DateTime('1800-04-01'),
            'orderDoneWithLogin' => false,
            'numberOfOrders' => '2',
            'negativePaymentInformation' => \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship::NEGATIVE_PAYMENT_INFORMATION_NO_PAYMENT_DISRUPTION,
            'riskyItemsInShoppingCart' => true,
            'logisticsServiceProvider' => 'DHL Standard'        
        ]),
        'redirectLinks' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\RedirectLinks([
            'urlSuccess' => 'http://google.de/success',
            'urlCancellation' => 'http://google.de/cancel',
            'urlDenial' => 'http://google.de/denial'       
        ])
    ]);
}

// Configure HTTP basic authorization: basicAuth
echo getenv('EASYCREDIT_USER').' : '.getenv('EASYCREDIT_PASSWORD');

$config = \Teambank\RatenkaufByEasyCreditApiV3\Configuration::getDefaultConfiguration()
    ->setHost('https://ratenkauf.easycredit.de')
    ->setUsername(getenv('EASYCREDIT_USER'))
    ->setPassword(getenv('EASYCREDIT_PASSWORD'));


$transactionApiInstance = new \Teambank\RatenkaufByEasyCreditApiV3\Service\TransactionApi(
    new \Teambank\RatenkaufByEasyCreditApiV3\Client(['debug'=>true]),
    $config
);
$webshopApiInstance = new \Teambank\RatenkaufByEasyCreditApiV3\Service\WebshopApi(
    new \Teambank\RatenkaufByEasyCreditApiV3\Client(['debug'=>true]),
    $config
);
$installmentplanApiInstance = new \Teambank\RatenkaufByEasyCreditApiV3\Service\InstallmentplanApi(
    new \Teambank\RatenkaufByEasyCreditApiV3\Client(['debug'=>true]),
    $config
);
