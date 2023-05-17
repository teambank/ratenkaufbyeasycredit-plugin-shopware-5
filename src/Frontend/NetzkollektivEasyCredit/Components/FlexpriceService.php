<?php
class EasyCredit_FlexpriceService
{
    protected $helper;
    protected $basket;
    protected $db;
    protected $crudService;

    protected $_attributes = [
        's_articles_attributes' => 'easyCredit-Ratenkauf: Zinsflex für diesen Artikel deaktivieren',
        's_categories_attributes' => 'easyCredit-Ratenkauf: Zinsflex für diese Kategorie deaktivieren',
    ];

    public function __construct () {
        $this->helper = new EasyCredit_Helper();
        $this->db = Shopware()->Db();
        $this->crudService = $this->helper->getContainer()->get('shopware_attribute.crud_service');
    }

    public function updateConfiguration() {

        $container = $this->helper->getContainer();

        $isFlexpriceAvailable = $container->get('easyCreditCheckout')
            ->getWebshopDetails()
            ->getFlexprice();

        $refreshMeta = false;
        if ($isFlexpriceAvailable) {
            foreach ($this->_attributes as $table => $label) {
                if ($this->crudService->get($table, 'easycredit_disable_zinsflex')) {
                    continue;
                }
                $this->crudService->update($table, 'easycredit_disable_zinsflex', 'boolean', [
                    'label'            => $label,
                    'displayInBackend' => true,
                    'position'         => 50,
                    'custom'           => true,
                    'translatable'     => true,
                ]);
                $refreshMeta = true;
            }
        } else {
            foreach ($this->_attributes as $table => $label) {
                if (!$this->crudService->get($table, 'easycredit_disable_zinsflex')) {
                    continue;
                }
                $this->crudService->delete($table, 'easycredit_disable_zinsflex');
                $refreshMeta = true;
            }
        }

        if ($refreshMeta) {
            $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
            if ($metaDataCache !== null && method_exists($metaDataCache, 'deleteAll')) {
                $metaDataCache->deleteAll();
            }
            Shopware()->Models()->generateAttributeModels( array_keys($this->_attributes) );
        }
    }

    public function shouldDisableFlexprice($items = null) {
        if ($items === null) {
            $basket = Shopware()->Modules()->Basket()->sGetBasket();
            $items = $basket['content'];
        }
        if ($items === null) {
            return false;
        }

        if ($this->crudService->get('s_articles_attributes', 'easycredit_disable_zinsflex')) {
            foreach ($items as $item) {
                if (isset($item['additional_details']['easycredit_disable_zinsflex']) &&
                    $item['additional_details']['easycredit_disable_zinsflex']
                ) {
                    return true;
                }

                if (isset($item['easycredit_disable_zinsflex']) &&
                    $item['easycredit_disable_zinsflex']
                ) {
                    return true;
                }
            }
        }

        if ($this->crudService->get('s_categories_attributes', 'easycredit_disable_zinsflex')) {
            $productIds = array_map(function($item) {
                return (int)$item['articleID'];
            }, $items);

            if ($productIds === null) {
                return false;
            }

            $query = $this->helper->getContainer()->get(\Doctrine\DBAL\Connection::class)->createQueryBuilder();
            $query->select('MAX(ca.easycredit_disable_zinsflex)')
              ->from('s_articles_categories', 'ac')
              ->join('ac', 's_categories_attributes', 'ca', 'ca.categoryID = ac.categoryID')
              ->where('ac.articleID IN (:ids)')
              ->setParameter(':ids', $productIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

            $disable = $query->execute()->fetchOne();
            return ($disable > 0);
        }

        return false;
    }
}
