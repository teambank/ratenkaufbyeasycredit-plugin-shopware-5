<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;

class CustomerBuilder
{
    protected $_user = null;

    protected $_userData;

    public function __construct() {
        if (isset(Shopware()->Session()->sUserId) && !empty(Shopware()->Session()->sUserId)) {
            $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
        }

        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            $this->_userData = Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            $this->_userData = Shopware()->Modules()->Admin()->sGetUserData();
        }
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

        $query = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->getCustomerDetailQuery($this->_user->getId());
        $data = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $data['orderCount'];
    }

    public function build() {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Customer([
            'gender' => $this->getPrefix(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'birthDate' => $this->getDob(),
            'contact' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Contact([
                'email' => $this->getEmail(),
                //'mobilePhoneNumber' => '0123456789',
                //'phoneNumbersConfirmed' => true
            ]),
            //'bank' => new \Teambank\RatenkaufByEasyCreditApiV3\Model\Bank([
            //    'iban' => 'DE88100900001234567892'
            //]),
            'companyName' => $this->getCompany() ? $this->getCompany() : null
        ]);
    }
}
