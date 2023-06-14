<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;
use Shopware\Models\Customer\Repository as CustomerRepository;

class CustomerBuilder
{
    protected $_user = null;

    protected $_userData;
    protected $helper;

    public function __construct() {
        if (isset(Shopware()->Session()->sUserId) && !empty(Shopware()->Session()->sUserId)) {
            $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
        }

        $this->helper = new \EasyCredit_Helper();
        $this->_userData = $this->helper->getUser();
    }

    public function getPrefix()
    {
        $prefixConverter = new PrefixConverter();
        return $prefixConverter->convert($this->_userData['billingaddress']['salutation']);
    }

    public function getFirstname() {
        return $this->_userData['billingaddress']['firstname'];
    }

    public function getLastname() {
        return $this->_userData['billingaddress']['lastname'];
    }

    public function getEmail() {
        return $this->_userData['additional']['user']['email'];
    }

    public function getDob() {
        $dob = $this->_userData['billingaddress']['birthday'];

        if ($dob == '0000-00-00') {
            $dob = null;
        }

        return $dob;
    }

    public function getCompany() {
        return $this->_userData['billingaddress']['company'];
    }

    public function getTelephone() {
        return $this->_userData['billingaddress']['phone'];
    }

    public function isLoggedIn() {
        return $this->_user
            && $this->_user->getAccountMode() == 0;
    }

    public function getCreatedAt() {
        if ($this->_user) {
            return $this->_user->getFirstLogin();
        }

        return null;
    }

    public function getOrderCount() {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        /** @var CustomerRepository */
        $customerRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
        $query = $customerRepository->getCustomerDetailQuery($this->_user->getId());
        $data = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $data['orderCount'];
    }

    public function build() {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Customer(array_filter([
            'gender' => $this->getPrefix(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'birthDate' => $this->getDob(),
            'contact' => $this->getEmail() ? new \Teambank\RatenkaufByEasyCreditApiV3\Model\Contact([
                'email' => $this->getEmail()
            ]) : null,
            'companyName' => $this->getCompany() ? $this->getCompany() : null
        ]));
    }
}
