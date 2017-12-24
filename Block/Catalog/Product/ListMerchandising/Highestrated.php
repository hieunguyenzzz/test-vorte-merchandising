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

class Highestrated extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising {

    /**
     * Config path used to retrieve config setting values
     * @var string
     */

    protected $_configPath = 'highestrated';

    /**
     * Initialise the product collection and join with the reviews and rating summary tables
     *
     * @return $this
     */
    protected function _initCollection()
    {
        parent::_initCollection();
        $this->_collection->joinField(
            'average_rating',
            'review_entity_summary',
            'rating_summary',
            'entity_pk_value=entity_id',
            array(
                'entity_type' => 1,
            ),
            'left'
        );
        $this->_collection->addAttributeToFilter('average_rating', array('gt' => 0));
        /**
         * Reset currently set order as we only want to order on the average rating.
         * Without this the collection will order on entity_id before the average rating.
         */
        $this->_collection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        /**
         * Now apply the new collection order.
         */
        $this->_collection->getSelect()->order('average_rating desc')->group('e.entity_id');
        return $this;
    }
}