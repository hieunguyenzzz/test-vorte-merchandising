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

namespace Vortex\Merchandising\Block\Catalog\Product\ListMerchandising;

class Recentlyviewed extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising {

    /**
     * Config path used to retrieve config setting values
     * @var string
     */
    protected $_configPath = 'recentlyviewed';


    /**
     *  Initialise collection
     */
    protected function _initCollection()
    {
        $excludeIds = array();
        if ($this->getCurrentProduct()) {
            $excludeIds[] = $this->getCurrentProduct()->getId();
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_collection = $objectManager->create('Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection');
        $this->_collection->excludeProductIds($excludeIds);
        $this->_addIndexFilter();
        parent::_initCollection();
    }

    /**
     * Order by added at
     *
     * @return $this
     */
    protected function _setOrderBy()
    {
        $this->_collection->setAddedAtOrder();
        return $this;
    }

    /**
     * Get block cache life time
     *  - Disable block caching
     *
     * @return false
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * Join product viewed report table to collection.
     * Core Magento does this with customer session so needs to be done manually here.
     */
    protected function _addIndexFilter()
    {
        $this->_collection->joinTable(
            ['idx_table' => $this->_collection->getTable('report_viewed_product_index')],
            'product_id=entity_id',
            ['product_id' => 'product_id', 'item_store_id' => 'store_id', 'added_at' => 'added_at']
        );
        $this->_collection->getSelect()->group('e.entity_id');
    }


}