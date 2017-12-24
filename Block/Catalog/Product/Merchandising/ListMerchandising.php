<?php
/**
 * Vortex Commerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled
 * with this package in the file CE-MODULE-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.vortexcommerce.com/CE-MODULE-LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vortexcommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to http://www.vortexcommerce.com for more information.
 *
 * @category    Vortex
 * @package     Vortex_Merchandising
 * @copyright   Copyright (c) 2016 Vortex Commerce Ltd (http://www.vortexcommerce.com)
 * @license     http://www.vortexcommerce.com/CE-MODULE-LICENSE.txt (VORTEX COMMERCE LTD EULA)
 * @author      Richard Cleverley, richard.cleverley@vortexcommerce.com, Vortex Commerce Ltd
 *
 * SUPPORT
 * For commercial support please visit http://www.vortexcommerce.com.
 *
 */

namespace Vortex\Merchandising\Block\Catalog\Product\Merchandising;

use Magento\Catalog\Model\Product as Product;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock as StockHelper;



class ListMerchandising extends \Vortex\Merchandising\Block\Catalog\Product\AbstractProduct
    implements \Vortex\Merchandising\Block\Catalog\Product\Merchandising\InterfaceMerchandising
    {

    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection          = null;
    protected $_identity            = null;
    /**
     * Path in config
     *
     * @var string
     */
    protected $_configPath          = 'default';
    /**
     * Attributes to filter against
     *
     * @var array
     */
    protected $_attributeFilters    = array();

    /**
     * Product collection factory
     *
     * @var object \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Product Collection
     *
     * @var object
     */
    protected $_productCollection;

    protected $_visibility;

    protected $_productStatus;

    protected $_catalogFlatState;

    protected $_categoryFactory;

    protected $_eventTypeFactory;

    protected $_stockHelper;

    protected $checkoutSession;

    protected $_scopeConfig;

    protected $_context;

    /**
     * Category display mode
     *
     * @var string
     */
    protected $_mode;

    public $helper;

    const DEFAULT_CACHE_LIFETIME    = 86400;
    const DEFAULT_COLLECTION_LIMIT  = 12;

    const ORDER_BY_DEFAULT          = 'price';
    const ORDER_BY_PRICE            = 'price';
    const ORDER_BY_FEATURED         = 'featured_homepage_order';

    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';


    /**
     * ListMerchandising constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ProductFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $catalogFlatState
     * @param ProductVisibility $visibility
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Vortex\Merchandising\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ProductFactory $productCollectionFactory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State  $catalogFlatState,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Vortex\Merchandising\Helper\Data $helper,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;

        $this->_postDataHelper = $postDataHelper;
        $this->_catalogFlatState = $catalogFlatState;
        $this->_visibility = $visibility;
        $this->_productStatus = $productStatus;
        $this->_categoryFactory = $categoryFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_eventTypeFactory = $eventTypeFactory;
        $this->_stockHelper = $stockHelper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_checkoutSession = $checkoutSession;
        $this->_context = $context;
        $this->_mode = 'grid';
        $this->helper = $helper;

        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );

    }

    /**
     * Gets default product collection if main merchandising class doesn't instantiate it's own.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            /**
             * @TODO Must be a better way of adding the attributes to the base collection!
             */
            $collection = $this->_productCollectionFactory->create()->getCollection()->addAttributeToSelect('*');
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }


    /**
     * get product collection and apply default filters.
     *
     * @return $this
     */
    protected function _initCollection()
    {
        /**
         * No collection to let's get the default product collection.
         */
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getProductCollection();
        }
        /**
         * Apply default filters.
         */
        $this->_collection
            ->addStoreFilter($this->getStore())
            ->setStoreId($this->getStore()->getId())
            //Only show enabled products
            ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()])
            ->setVisibility($this->_visibility->getVisibleInCatalogIds())
            //Add SEO URL rewrites to collection
            ->addAttributeToSelect('status')
            ->addUrlRewrite();
        return $this;
    }


    /**
     * @return bool|null|
     */
    public function getCollection()
    {
        if (is_null($this->_collection)) {
            \Magento\Framework\Profiler::start(get_class($this).'::_initCollection');
            $this->_initCollection();
            \Magento\Framework\Profiler::stop(get_class($this).'::_initCollection');
            \Magento\Framework\Profiler::start(get_class($this).'::_addAttributesToFilter');
            $this->_addAttributesToFilter();
            \Magento\Framework\Profiler::stop(get_class($this).'::_addAttributesToFilter');
            \Magento\Framework\Profiler::start(get_class($this).'::_addAttributeToSelect');
            $this->_addAttributeToSelect();
            \Magento\Framework\Profiler::stop(get_class($this).'::_addAttributeToSelect');
            \Magento\Framework\Profiler::start(get_class($this).'::_addCategoryFilter');
            $this->_addCategoryFilter();
            \Magento\Framework\Profiler::stop(get_class($this).'::_addCategoryFilter');
            \Magento\Framework\Profiler::start(get_class($this).'::_addPriceData');
            $this->_addPriceData();
            \Magento\Framework\Profiler::stop(get_class($this).'::_addPriceData');
            \Magento\Framework\Profiler::start(get_class($this).'::_addQty');
            $this->_addQty();
            \Magento\Framework\Profiler::stop(get_class($this).'::_addQty');
            \Magento\Framework\Profiler::start(get_class($this).'::_setLimits');
            $this->_setLimits();
            \Magento\Framework\Profiler::stop(get_class($this).'::_setLimits');
            \Magento\Framework\Profiler::start(get_class($this).'::_setOrderBy');
            $this->_setOrderBy();
            \Magento\Framework\Profiler::stop(get_class($this).'::_setOrderBy');
            \Magento\Framework\Profiler::start(get_class($this).'::_filterLoadedItems');
            $this->_filterLoadedItems();
            \Magento\Framework\Profiler::stop(get_class($this).'::_filterLoadedItems');
            \Magento\Framework\Profiler::start(get_class($this).'::_applyMinimumSizeFilter');
            $this->_applyMinimumSizeFilter();
            \Magento\Framework\Profiler::stop(get_class($this).'::_applyMinimumSizeFilter');
        }
        /**
         * Clear loaded collection as it will already have loaded initial products and won't get modified.
         */
        $this->_collection->clear();
        return ($this->_filterSuccessful() && $this->_collection->count()) ? $this->_collection : false;
    }

    /**
     * Get the currently loaded product collection
     *
     * @return bool|null
     */
    public function getLoadedProductCollection(){
        return $this->getCollection();
    }

    /**
     * Is the Flat Catalog turned on.
     * @return bool
     */
    protected function _isFlat()
    {

        return $this->_catalogFlatState->isFlatEnabled();

    }

    /**
     * Return current store object
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore(){
        return $this->_storeManager->getStore();
    }

    /**
     * Add attributes to collection filter.
     *
     * @return $this
     */
    protected function _addAttributesToFilter()
    {
        if (!empty($this->_attributeFilters)) {
            foreach ($this->_attributeFilters as $code => $value) {
                $this->_collection->addAttributeToFilter($code, $value);
            }
        }
        return $this;
    }

    /**
     * Add attributes to collection select query
     *
     * @return $this
     */
    protected function _addAttributeToSelect()
    {
        $attributes = $this->getAttributes();
        $attributes = array_diff($attributes, array(
            'image_label',
            'small_image_label',
            'thumbnail_label',
            'msrp_enabled',
            'msrp_display_actual_price_type',
            'price_type',
            'weight_type',
            'price_view',
            'shipment_type',
            'links_purchased_separately',
            'links_exist',
            'google_shopping_image'
        ));
        $this->_collection->addFieldToSelect($attributes);
        return $this;
    }

    /**
     * Collects all attributes to be added to the product collection.
     * It includes the default defined attributes if selected.
     *
     * @return array
     */
    public function getAttributes()
    {
        if (is_null($this->getData('_attributes'))) {
            if ($this->hasData('no_default_attributes')) {
                $defaultAttributes = array();
            } else {
                $defaultAttributes = $this->_catalogConfig->getProductAttributes();
            }
            if ($this->getData('attributes')) {
                $attributes = array_merge($this->getData('attributes'), $defaultAttributes);
                $attributes = array_unique($attributes);
            } else {
                $attributes = $defaultAttributes;
            }
            $this->setData('_attributes', $attributes);
        }
        return $this->getData('_attributes');
    }


    /**
     * @return mixed
     */
    public function getCategory()
    {
        if (is_null($this->getData('category'))) {
            if ($this->getData('no_auto_category_detect')) {
                $this->setData('category', false);
                return $this->getData('category');
            }
            //Get current category
            if ($category = $this->getCurrentCategory()) {
                $this->setData('category', $category);
                //Get current products categories if available.
            } elseif ($this->getProduct()) {
                $categories = $this->getProduct()->getCategoryCollection()
                    ->addAttributeToSort('level', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
                    ->getAllIds();
                if (!empty($categories) && $categories[0]) {
                    $this->setData('category', $categories[0]);
                }
            } else {
                $this->setData('category', false);
            }
        }
        if (!$this->getData('category') instanceof \Magento\Catalog\Model\Category\Interceptor) {
            $category = $this->_categoryFactory->create()->load($this->getData('category'));
            if ($category->getId()) {
                $this->setData('category', $category);
            } else {
                $this->setData('category', false);
            }
        }

        return $this->getData('category');
    }

    /**
     * Return the currently loaded category from registry
     *
     * @return bool|mixed
     */
    public function getCurrentCategory()
    {
        $category = $this->_coreRegistry->registry('current_category');
        if ($category && $category->getId()) {
            return $category;
        }
        return false;
    }

    /**
     * Return current product object from registry.
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        if(!$this->getData('current_product') && ($product = $this->_coreRegistry->registry('current_product'))) {
            if ($product->getId()) {
                $this->setData('current_product', $product);
            }
        }
        if(!$this->getData('current_product') && ($product = $this->_coreRegistry->registry('product'))) {
            if ($product->getId()) {
                $this->setData('current_product', $product);
            }
        }
        return $this->getData('current_product');
    }

    /**
     * Add category filter to collection.
     *
     * @return $this
     */
    protected function _addCategoryFilter()
    {
        if ($category = $this->getCategory()) {
            $this->_collection->setFlag('disable_root_category_filter', false);
            $this->_collection->addCategoryFilter($category);
        }
        return $this;
    }

    /**
     * Add price data to collection select statement.
     *
     * @return $this
     */
    protected function _addPriceData()
    {
        $this->_collection->addPriceData();
        return $this;
    }

    /**
     * Join stock to collection
     *
     * @return $this
     */
    protected function _addQty()
    {
        $this->_collection->joinField(
            'qty',
            'cataloginventory_stock_status_idx',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'inner'
        );
        return $this;
    }

    /**
     * Retrieve config values from scopeConfig factory
     *
     * @param bool|string $path
     * @return array
     */
    protected function _getConfig($path=false)
    {
        if ($path) {
            return $this->_scopeConfig->getValue("vortex_merchandising_collections/{$this->_configPath}/{$path}");
        }
        return $this->_scopeConfig->getValue("vortex_merchandising_collections/{$this->_configPath}");
    }

    /**
     * collection pagination
     *
     * @return $this
     */
    protected function _setLimits()
    {
        $this->_collection->setPage($this->getPage(), $this->getLimit());
        return $this;
    }

    /**
     * Get limit from config if not already set on object
     *
     * @return mixed
     */
    public function getLimit()
    {
        if (!$this->getData('limit')) {
            $limit = ($this->getConfig('limit')) ? $this->getConfig('limit') : self::DEFAULT_COLLECTION_LIMIT;
            $this->setData('limit', $limit);
        }
        return $this->getData('limit');
    }


    /**
     * Get Collection order
     *
     * @return string
     */
    public function getOrderBy()
    {
        if($this->_configPath == 'featured'){
            $this->setData('order_by',self::ORDER_BY_FEATURED);
            return self::ORDER_BY_FEATURED;
        }

        if (is_string($this->getConfig('order_by'))) {
            return $this->getConfig('order_by');
        }

        return self::ORDER_BY_DEFAULT;
    }

    /**
     * Get field to order collection on
     *
     * @return $this
     */
    protected function _setOrderBy()
    {
        switch ($this->getOrderBy()) {
            case self::ORDER_BY_PRICE:
                $this->_orderByPrice();
                break;
            case self::ORDER_BY_FEATURED:
                $this->_collection->addAttributeToSort(self::ORDER_BY_FEATURED,'ASC');
                break;
            default:
                // Check if order by attribute is allowed
                if ($this->getOrderBy()) {
                    $this->_collection->setOrder($this->getOrderBy(), $this->getDir());
                }
        }
        return $this;
    }

    /**
     * Add price ordering to collection.
     * Needs to take various pricing strategeies into account (special pricing etc)
     *
     * @return $this
     */
    protected function _orderByPrice()
    {
        if ($product = $this->getCurrentProduct()) {
            if ($product->getPrice() > 0 || $product->getSpecialPrice() > 0) {
                $special_price = $this->_collection->getConnection()->getCheckSql('{{special_price}} > ' . $product->getFinalPrice()
                    , '{{special_price}} - ' . $product->getFinalPrice(), $product->getFinalPrice() . ' - {{special_price}}');

                $price = $this->_collection->getConnection()->getCheckSql('{{price}} > ' . $product->getFinalPrice()
                    , '{{price}} - ' . $product->getFinalPrice(), $product->getFinalPrice() . ' - {{price}}');
                $diff = $this->_collection->getConnection()->getCheckSql('{{special_price}} > 0', $special_price, $price);
                $this->_collection->addExpressionAttributeToSelect('diff', $diff, array('price', 'special_price'));
                $this->_collection->addAttributeToSort('diff');
            }
        }
        return $this;
    }

    /**
     * Apply minimum size filter
     */
    protected function _applyMinimumSizeFilter()
    {
        // If minimum collection size is set apply filter.
        if (!is_null($this->getMinCollectionSize()) && ((int) $this->getMinCollectionSize() <= (int) $this->getLimit())) {
            if ((int) $this->_collection->count() < (int) $this->getMinCollectionSize()) {
                $this->setData('filter_successful', false);
            }
        }
    }

    /**
     * Append review summary to product collection
     */
    protected function _appendReview()
    {
        $this->_reviewFactory->create()->appendSummary($this->_collection);
    }

    /**
     *Exclude previously loaded products from this product collection
     *
     * @return $this
     */
    protected function _filterLoadedItems()
    {
        if ($entityIds = $this->_coreRegistry->registry('vortex_summit_collection_loaded_items')) {
            $this->_collection->addIdFilter($entityIds, true);
        }
        // Remove current product from filter
        if ($this->getCurrentProduct()) {
            $this->_collection->addIdFilter($this->getCurrentProduct()->getId(), true);
        }
        return $this;
    }

    /**
     * Get category display mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }


    /**
     * Have filters been successfully applied?
     *
     * @return bool
     */
    protected function _filterSuccessful()
    {
        if (!is_null($this->getData('filter_successful'))) {
            return $this->getData('filter_successful');
        }
        return true;
    }

    /**
     * Get how many days to lookup from present from config
     *
     * @return int
     */
    public function getLookupDays()
    {

        if (!$this->getData('lookup_days')) {
            $days = $this->_getConfig('lookup_days');
            $days = ($days) ? $days : 14;
            $this->setData('lookup_days', (int) $days);
        }
        return $this->getData('lookup_days');
    }

    /**
     * Add SQL to collection to limit resultset by from/to dates
     * and items that have been previously ordered.
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addOrderedQty($from = '', $to = '')
    {
        $connection = $this->_collection->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');


        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = soi.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->_collection->getSelect()->from(
            ['soi' => $this->_collection->getTable('sales_order_item')],
            ['ordered_qty' => 'SUM(soi.qty_ordered)', 'order_items_name' => 'soi.name']
        )->joinInner(
            ['order' => $this->_collection->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->where(
            'soi.parent_item_id IS NULL'
        )->group(
            'e.entity_id'
        )->having(
            'SUM(soi.qty_ordered) > ?',
            0
        );
        /**
         * Make sure catalog product item is in an order.
         */
        $this->_collection->joinTable(
            ['soip' => $this->_collection->getTable('sales_order_item')],
            'product_id = entity_id',
            ['product_id'],
            NULL,
            'inner'
        );

        /**
         * Use native method to make sure product is in stock.
         */
        $this->_stockHelper->addIsInStockFilterToCollection($this->_collection);


        return $this;
    }

    /**
     * Add From/To sql to collection
     *
     * @param $fieldName
     * @param $from
     * @param $to
     * @return string
     */
    protected function prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            '(%s BETWEEN %s AND %s)',
            $fieldName,
            $this->_collection->getConnection()->quote($from),
            $this->_collection->getConnection()->quote($to)
        );
    }

    /**
     * Add views count.
     * Native method doesn't return any records as part of this collection
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        $eventTypes = $this->_eventTypeFactory->create()->getCollection();
        foreach ($eventTypes as $eventType) {
            if ($eventType->getEventName() == $this->_eventType) {
                $productViewEvent = (int)$eventType->getId();
                break;
            }
        }

        /**
         * Join the report event table into the product collection rather than resetting collections select.
         */
        $this->_collection->getSelect()->joinLeft(
            ['reports_table_views' => $this->_collection->getTable('report_event')],
            $this->_collection->getConnection()->quoteInto(
                'object_id = e.entity_id', []
            )
        )->where('event_type_id = ?',
            $productViewEvent
        )->group(
            'e.entity_id'
        )->having('COUNT(event_id) > ?', 0
        )->order('COUNT(object_id) ' . self::SORT_ORDER_DESC
        );
        /**
         * Apply any date range to collection query.
         */

        if ($from != '' && $to != '') {
            $this->_collection->getSelect()->where('DATE(logged_at) >= ?', $from)->where('DATE(logged_at) <= ?', $to);
        }
        return $this;
    }

    /**
     * Set up toolbar
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getCollection();
        /**
         * We might not have a collection yet so if not get the default one.
         */
        if(!$collection){
            $collection = $this->_getProductCollection();
        }

        /**
         * Set the collection toolbar and configure it.
         */
        $toolbar->setCollection($collection);

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return parent::_beforeToHtml();
    }

    


}