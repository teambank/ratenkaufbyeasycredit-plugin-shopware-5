<?php
namespace Netzkollektiv\EasyCredit;

interface CustomerInterface {

    public function isLoggedIn();
    public function getCreatedAt();
    public function getOrderCount();
    public function getRisk();

}
