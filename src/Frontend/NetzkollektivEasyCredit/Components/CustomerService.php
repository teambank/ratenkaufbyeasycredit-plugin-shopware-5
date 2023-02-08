<?php
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;

use Symfony\Component\Form\FormFactoryInterface;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Address;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;

class EasyCredit_CustomerService
{
    protected $helper;

    protected $swConfig;

    protected $connection;

    protected $contextService;

    protected $registerService;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct() {
        $this->helper = new EasyCredit_Helper();
        $this->swConfig = $this->helper->getContainer()->get('config');
        $this->connection = $this->helper->getEntityManager()->getConnection();
        $this->formFactory = $this->helper->getContainer()->get('shopware.form.factory');
        $this->contextService = $this->helper->getContainer()->get('shopware_storefront.context_service');
        $this->registerService = $this->helper->getContainer()->get('shopware_account.register_service');
    }

    public function createCustomer(TransactionInformation $transaction)
    {
        $customer = $transaction->getTransaction()->getCustomer();
        $contact = $customer->getContact();
        $address = $transaction->getTransaction()->getOrderDetails()->getShippingAddress();

        $countryId = $this->getCountryId($address->getCountry());

        $customerModel = $this->registerCustomer([
            'email' => $contact->getEmail(),
            'password' => null,
            'accountmode' => 1,
            'salutation' => $this->getSalutation($customer->getGender()),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getAddress(),
            'additionalAddressLine1' => null,
            'zipcode' => $address->getZip(),
            'city' => $address->getCity(),
            'country' => $countryId,
            'state' => null,
            'phone' => $contact->getMobilePhoneNumber(),
        ]);
        return $customerModel;
    }

    /**
     * @return Customer
     */
    private function registerCustomer(array $customerData)
    {
        $customer = new Customer();
        $personalForm = $this->formFactory->create(PersonalFormType::class, $customer);
        $personalForm->submit($customerData);

        $customer->setPaymentId($this->helper->getPayment()->getId());

        $address = new Address();
        $addressForm = $this->formFactory->create(AddressFormType::class, $address);
        $addressForm->submit($customerData);

        $context = $this->contextService->getShopContext();
        $shop = $context->getShop();

        $this->registerService->register($shop, $customer, $address);

        return $customer;
    }

    public function loginCustomer(Customer $customerModel)
    {
        $request = $this->helper->getContainer()->get('front')->Request();
        $request->setPost('email', $customerModel->getEmail());
        $request->setPost('passwordMD5', $customerModel->getPassword());
        $this->helper->getModule('admin')->sLogin(true);

        // Set country and area to session, so the cart will be calculated correctly,
        // e.g. the country changed and has different taxes
        $session = $this->helper->getSession();
        $customerShippingCountry = $customerModel->getDefaultShippingAddress()->getCountry();
        $session->offsetSet('sCountry', $customerShippingCountry->getId());
        $session->offsetSet('sArea', $customerShippingCountry->getArea()->getId());
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
     * @return int
     */
    private function getCountryId($countryCode)
    {
        $sql = 'SELECT id FROM s_core_countries WHERE countryiso=:countryCode';
        return (int) $this->connection->fetchColumn($sql, ['countryCode' => $countryCode]);
    }
}
