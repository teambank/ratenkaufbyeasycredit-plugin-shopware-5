<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class ItemBuilder
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

    public function __construct($shopwareItem)
    {

        $this->db = Shopware()->Db();
        $this->rawItem = $shopwareItem;

        $this->name = $this->rawItem['articlename'];
        $this->qty = $this->rawItem['quantity'];

        $this->price = (isset($this->rawItem['additional_details']['price_numeric'])) ?
            $this->rawItem['additional_details']['price_numeric']
          : $this->rawItem['priceNumeric'];

        $this->manufacturer = $this->rawItem['additional_details']['supplierName'];
        $this->sku = $this->rawItem['id'];

        $this->articleId = $this->rawItem['articleID'];
        $this->loadCategory();
    }

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

    public function getCategory()
    {
        return $this->categoryName;
    }

    public function getSkus()
    {
        $skus = array();
        foreach (array('articleID','ordernumber','ean','suppliernumber') as $key) {
            if (!isset($this->rawItem[$key]) || $this->rawItem[$key] === '') {
                continue;
            }
            $skus[] = new \Teambank\RatenkaufByEasyCreditApiV3\Model\ArticleNumberItem([
                'numberType' => $type,
                'number' => $sku
            ]);
        }
        return $skus;
    }

    public function build() {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem([
            'productName' => $this->getName(),
            'quantity' => $this->getQty(),
            'price' => $this->getPrice(),
            'manufacturer' => $this->getManufacturer(),
            'productCategory' => $this->getCategory(),
            'articleNumber' => $this->getSkus()
        ]);      
    }
}
