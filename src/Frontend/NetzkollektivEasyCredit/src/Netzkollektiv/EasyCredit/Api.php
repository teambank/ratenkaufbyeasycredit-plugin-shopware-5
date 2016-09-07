<?php
namespace Netzkollektiv\EasyCredit;

class Api
{
    protected $_apiBaseUrl = 'https://www.easycredit.de/ratenkauf-ws/rest';
    protected $_apiVersion = 'v0.3';

    protected $_customerPrefixMalePatterns = array('Herr','Mr','male','männlich');
    protected $_customerPrefixFemalePatterns = array('Frau','Ms','Miss','Mrs','female','weiblich');

    protected $_config = null;

    public function __construct($config, $logger) {
        $this->_config = $config;
        $this->_logger = $logger;
    }

    protected function _getRequestContext($method, $postData = null) {

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $this->_getWebshopId(),
            'tbk-rk-token' => $this->getToken(),
        ); 

        $ctx = array('http'=>array(
            'ignore_errors' => true
        ));

        if (!is_null($postData)) {
            $headers['Content-Type'] = 'application/json;charset=UTF-8';
            $ctx['http']['content'] = json_encode($postData);
        }

        foreach ($headers as $key => $header) {
            $headers[$key] = implode(': ',array($key,$header));
        }

        $ctx['http']['method'] = strtoupper($method);
        $ctx['http']['header'] = implode("\r\n",$headers);

        return stream_context_create($ctx);
    }

    protected function _getWebshopId() {
        if (!isset($this->_config['api_key']) || empty($this->_config['api_key'])) {
            throw new \Exception('api key not configured');
        }
        return $this->_config['api_key'];
    }

    public function getToken() {
        if (!isset($this->_config['api_token']) || empty($this->_config['api_token'])) {
            throw new \Exception('api token not configured');
        }
        return $this->_config['api_token'];
    }

    public function getRedirectUrl($token) {
        return 'https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung='.$token;
    }


    public function callProcessInit($quote, $cancelUrl, $returnUrl, $rejectUrl) {
        $data = $this->getProcessInitRequest($quote, $cancelUrl, $returnUrl, $rejectUrl);

        return $this->call('POST','vorgang', $data);
    }

    public function callModelCalculation($amount) {
        $data = array(
            'webshopId' => $this->_getWebshopId(),
            'finanzierungsbetrag' => $amount,
        );

        return $this->call('GET', 'modellrechnung/durchfuehren', $data);
    }

    public function callDecision($token) {
        return $this->call('GET','vorgang/'.$token.'/entscheidung');
    }

    public function callStatus($token) {
        return $this->call('GET','vorgang/'.$token);
    }

    public function callFinancing($token) {
        return $this->call('GET','vorgang/'.$token.'/finanzierung');
    }

    public function callConfirm($token) {
        return $this->call('POST','vorgang/'.$token.'/bestaetigen');
    }

    public function callAgreement() {
        return $this->call('GET','texte/zustimmung/'.$this->_getWebshopId());
    }

    protected function _buildUrl($method, $resource) {
        $url = implode('/',array(
            $this->_apiBaseUrl,
            $this->_apiVersion,
            $resource
        ));
        return $url;
    }

    public function call($method, $resource, $data = array()) { 

        $url = $this->_buildUrl($method, $resource);
        $method = strtoupper($method);

        $this->_logger->log($data);
        $client = new \Zend_Http_Client($url,array(
            'keepalive' => true
        ));
        $client->setHeaders(array(
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $this->_getWebshopId(),
            'tbk-rk-token' => $this->getToken()
        ));

        if ($method == 'POST') { 
            $this->_logger->log(json_encode($data));
            $client->setRawData(
                json_encode($data), 
                'application/json;charset=UTF-8'
            );
            $data = null;
        } else {
            $client->setParameterGet($data);
        }
$this->_logger->log($data);
        $response = $client->request($method);

        # TODO catch these exceptions
        if ($response->isError()) {
            $this->_logger->log($response->getBody());
            throw new \Exception('Received error from easyCredit API');
        }

        $result = $response->getBody();

        if (empty($result)) {

            $this->_logger->log('EasyCredit result is empty');
            throw new \Exception('EasyCredit result is empty');
        }

        $result = json_decode($result);
        $this->_logger->log($result);

        if ($result == null) {
            $this->_logger->log('EasyCredit result is null');
            throw new \Exception('result is null');
        }

        if (isset($result->wsMessages)) {
            $this->_logger->log($result->wsMessages);
            $this->_handleMessages($result);
        }
        return $result;
    }

    protected function _handleMessages($result) {
        if (!isset($result->wsMessages->messages)) {
            unset($result->wsMessages);
            return;
        }
        $messages = $result->wsMessages->messages;

        foreach ($messages as $message) {
            switch (trim($message->severity)) {
                case 'ERROR':
                    throw(new \Exception($message->renderedMessage));
                    break;
                case 'INFO':
                    //echo($message->renderedMessage);
                    break;
            }
        }

        unset($result->wsMessages);
    }

    protected function _convertAddress($address, $isShipping = false) {
        $_address = array(
             'strasseHausNr' => $address->getStreet(1),
             'adresszusatz' => is_array($address->getStreet()) ? implode(',',array_slice($address->getStreet(),1)) : '',
             'plz' => $address->getPostcode(),
             'ort' => $address->getCity(),
             'land' => $address->getCountryId()
        );

        $completeAddress = implode(" ",$address->getStreet());
        if ($isShipping && stripos($completeAddress,'packstation') !== false) {
            $_address['packstation'] = true;
        }

        return $_address; 
    }


    protected function _guessCustomerPrefix($prefix) {
        foreach ($this->_customerPrefixMalePatterns as $pattern) {
            if (stripos($prefix,$pattern) !== false) {
                return 'HERR';
            }
        }
        foreach ($this->_customerPrefixFemalePatterns as $pattern) {
            if (stripos($prefix,$pattern) !== false) {
                return 'FRAU';
            }
        }
    }

    protected function _convertPersonalData($quote) {

        $prefix = $this->_guessCustomerPrefix($quote->getCustomerPrefix());

        $person = array(
            'anrede' => $prefix,
            'vorname' => $quote->getCustomerFirstname(),
            'nachname' => $quote->getCustomerLastname(),
        );

        if ($quote->getCustomerDob()) {
            $person['geburtsdatum'] = $quote->getCustomerDob();
        }

        return $person;
    }

    public function _convertItems($items) {
        $_items = array();

        foreach ($items as $item) {
            $_item = array(
                'produktbezeichnung'    => $item->getName(),
                'menge'                 => $item->getQty(),
                'preis'                 => $item->getPrice(),
                'hersteller'            => $item->getManufacturer(),
            );

            $_item['produktkategorie'] = $item->getCategoryName();
            $_item['artikelnummern'][] = array(
                'nummerntyp'    => 'shopware-sku',
                'nummer'        => $item->getSku()
            );

            $_items[] = array_filter($_item);
        }
        return $_items;
    }

    protected $_customerRisk = array(
        'KEINE_INFORMATION',
        'KEINE_ZAHLUNGSSTOERUNGEN',
        'ZAHLUNGSVERZOEGERUNG',
        'ZAHLUNGSAUSFALL',
    );
    
    protected function _convertRiskDetails($quote) {
        $details = array(
            'bestellungErfolgtUeberLogin'   => $quote->getCustomer()->isLoggedIn(),
            'risikoartikelImWarenkorb'      => $quote->isRiskProductInCart(),
            'anzahlProdukteImWarenkorb'     => count($quote->getAllVisibleItems())
        );

        if ($quote->getCustomer()->isLoggedIn()) {
            $details = array_merge($details, array(
                'kundeSeit'                     => $quote->getCustomer()->getCreatedAt(),
                'anzahlBestellungen'            => $quote->getCustomer()->getOrderCount(),
                'negativeZahlungsinformation'   => $quote->getCustomer()->getRisk(),
            ));
        }
        return $details;
    }

    public function getProcessInitRequest($quote, $cancelUrl, $returnUrl, $rejectUrl) {
        $data = array(
            'shopKennung' => $this->_getWebshopId(),
            'bestellwert' => $quote->getGrandTotal(),
            'ruecksprungadressen' => array(
                'urlAbbruch' => $cancelUrl,
                'urlErfolg' => $returnUrl,
                'urlAblehnung' => $rejectUrl
            ),
            'laufzeit' => 36,
            'personendaten' => $this->_convertPersonalData($quote),
            'kontakt' => array(
                'email' => $quote->getCustomerEmail(),
            ),
            'risikorelevanteAngaben' => $this->_convertRiskDetails($quote),
            'rechnungsadresse' => $this->_convertAddress($quote->getBillingAddress()),
            'lieferadresse' => $this->_convertAddress($quote->getShippingAddress(), true),
            'warenkorbinfos' => $this->_convertItems($quote->getAllVisibleItems()),
        );
        return array_filter($data);
    }
}
