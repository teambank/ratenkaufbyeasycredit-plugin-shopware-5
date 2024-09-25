<?php
namespace Shopware\Plugins\NetzkollektivEasyCredit\Api\Quote;

class ItemBuilder
{
    private $db;

    private $product;

    private $categoryName;

    public function __construct($shopwareItem)
    {
        $this->db = Shopware()->Db();
        $this->product = $shopwareItem;
    }

    private function getCategoryName() {
		if ($this->categoryName === null) {
            $query = 'SELECT description FROM s_categories c
                INNER JOIN s_articles_categories ac ON ac.categoryID = c.id WHERE articleID = ?'; 

            $this->categoryName = $this->db->fetchOne(
                $query,
                array($this->product['articleID'])
            );
        }
        return $this->categoryName;
    }

    public function getSkus()
    {
        $skus = array();
        foreach (array('articleID','ordernumber','ean','suppliernumber') as $key) {
            if (!isset($this->product[$key]) || $this->product[$key] === '') {
                continue;
            }
            $skus[] = new \Teambank\EasyCreditApiV3\Model\ArticleNumberItem([
                'numberType' => $key,
                'number' => $this->product[$key]
            ]);
        }
        return $skus;
    }

    public function build() {
        return new \Teambank\EasyCreditApiV3\Model\ShoppingCartInformationItem([
            'productName' => $this->product['articlename'],
            'productUrl' => $url = Shopware()->Front()->Router()->assemble([
                'module' => 'frontend',
                'controller' => 'detail',
                'sArticle' => $this->product['articleID'],
            ]),
            'productImageUrl' => $this->product['image']['source'], 
            'quantity' => $this->product['quantity'],
            'price' => (isset($this->product['additional_details']['price_numeric'])) ?
                $this->product['additional_details']['price_numeric']
              : $this->product['priceNumeric'],
            'manufacturer' => $this->product['additional_details']['supplierName'],
            'productCategory' => $this->getCategoryName(),
            'articleNumber' => $this->getSkus()
        ]);      
    }
}
