<?php
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;

use Symfony\Component\Form\FormFactoryInterface;
use Shopware\Models\Customer\Customer;

class EasyCredit_CustomerServiceEmotion
{
    protected $helper;

    protected $swConfig;

    protected $connection;

    protected $contextService;

    protected $registerService;

    public function __construct() {
        $this->helper = new EasyCredit_Helper();
        $this->swConfig = $this->helper->getContainer()->get('config');
        $this->connection = $this->helper->getEntityManager()->getConnection();
    }

    /**
     * @param \Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation $transaction
     */
    public function createCustomer($transaction)
    {
        $customer = $transaction->getTransaction()->getCustomer();
        $contact = $customer->getContact();
        $address = $transaction->getTransaction()->getOrderDetails()->getShippingAddress();

        $countryData = $this->getCountryData($address->getCountry());

        $customerModel = $this->registerCustomer([
            "auth" => array(
                "password" => md5(uniqid(rand())),
                "email" => $contact->getEmail(),
                "accountmode" => 1,
                "encoderName" => "md5"
            ),
            "payment" => array(
                "object" => array(
                    "id" => $this->helper->getPayment()->getId()
                )
            ),
            'salutation' => $this->getSalutation($customer->getGender()),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getAddress(),
            'additionalAddressLine1' => null,
            'zipcode' => $address->getZip(),
            'city' => $address->getCity(),
            'country' => $countryData['id'],
            'state' => $countryData['areaID'],
            'phone' => $contact->getMobilePhoneNumber(),
        ]);
        return $customerModel;
    }

    /**
     * @return Customer
     */
    private function registerCustomer(array $customerData)
    {
        $userId = $this->helper->getModule('admin')->sSaveRegisterMainData($customerData);

        $billingData = array('billing' => $customerData);
        if (preg_match('/^([\S\s]+?)\s+([\d\-\s]*?\s*[\w])?$/', $customerData['street'], $matches)) {
            $billingData['billing']['street'] = $matches[1];
            $billingData['billing']['streetnumber'] = $matches[2];
        }
        $this->helper->getModule('admin')->sSaveRegisterBilling($userId, $billingData);

        return $customerData;
    }

    public function loginCustomer($customerData)
    {
        $request = $this->helper->getContainer()->get('front')->Request();
        $request->setPost('email', $customerData['auth']['email']);
        $request->setPost('passwordMD5', $customerData['auth']['password']);
        $this->helper->getModule('admin')->sLogin(true);

        // Set country and area to session, so the cart will be calculated correctly,
        // e.g. the country changed and has different taxes
        $session = $this->helper->getSession();
        $session->offsetSet('sRegister', $customerData);
        $session->offsetSet('sRegisterFinished', true);
        $session->offsetSet('sCountry', $customerData['country']);
        $session->offsetSet('sArea', $customerData['state']);
    }


    const MR_SALUTATION = 'mr';

    /**
     * @return string
     */
    private function getSalutation($salutation)
    {
        $swSalutations = $this->swConfig->get('shopsalutations');
        if (!\is_string($swSalutations) || $swSalutations === '') {
            return self::MR_SALUTATION;
        }

        $swSalutations = \explode(',', $swSalutations);

        $key = \array_search(strtolower($salutation), $swSalutations);
        if ($key !== false) {
            return $swSalutations[$key];
        }

        return isset($swSalutations[0]) ? $swSalutations[0] : self::MR_SALUTATION;
    }

    /**
     * @param string $countryCode
     *
     * @return array
     */
    private function getCountryData($countryCode)
    {
        $sql = 'SELECT id, areaID FROM s_core_countries WHERE countryiso=:countryCode';
        return current($this->connection->fetchAll($sql, ['countryCode' => $countryCode]));
    }
}
