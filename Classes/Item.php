<?php

use Netzkollektiv\EasyCredit;


class Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Classes_Item implements EasyCredit\ItemInterface
{
    private $db;
    private $rawItem;

    private $categoryId;
    private $articleId;

    private $name;
    private $qty;
    private $price;
    private $categoryName;
    private $sku;

    private function getCategoryId() {
        $query = 'SELECT categoryID from s_articles_categories WHERE articleID = ?';

        $categoryId = $this->db->fetchOne(
            $query,
            array($this->articleId)
        );

        return $categoryId;
    }

    private function getCategoryDescriptions() {
        $query = 'SELECT description FROM s_categories WHERE id = ?';

        $categoryDescription = $this->db->fetchOne(
            $query,
            array($this->categoryId)
        );

        return $categoryDescription;
    }

    private function loadCategory() {
        $this->categoryId = $this->getCategoryId();

        if (!$this->categoryId) {
            $this->categoryName = '';
            return;
        }

        $this->categoryName = $this->getCategoryDescriptions();
    }

    public function __construct($shopwareItem)
    {

        $this->db = Shopware()->Db();
        $this->rawItem = $shopwareItem;

        $this->name = $this->rawItem['articlename'];
        $this->qty = $this->rawItem['quantity'];
        $this->price = $this->rawItem['additional_details']['price_numeric'];
        $this->manufacturer = $this->rawItem['additional_details']['supplierName'];
        $this->sku = $this->rawItem['id'];

        $this->articleId = $this->rawItem['articleID'];
        $this->loadCategory();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function getSku()
    {
        return $this->sku;
    }
}