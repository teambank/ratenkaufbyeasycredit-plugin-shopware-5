<?php
namespace Netzkollektiv\EasyCredit;

interface QuoteInterface {

    public function getGrandTotal();
    public function getBillingAddress();
    public function getShippingAddress();

    public function getCustomerPrefix();
    public function getCustomerFirstname();
    public function getCustomerLastname();
    public function getCustomerEmail();
    public function getCustomerDob();

    public function getRiskProducts();

    public function getAllVisibleItems();
}
