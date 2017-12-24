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

class Justrated extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising {

    /**
     * Config path used to retrieve config setting values
     * @var string
     */

    protected $_configPath = 'justtrated';

    /**
     * Initialise the product collection and join with the reviews table
     *
     * @return $this
     */
    protected function _initCollection()
    {
        parent::_initCollection();
        $this->_collection->joinField(
            'review_created_at',
            'review',
            'created_at',
            'entity_pk_value=entity_id',
            array(
                'entity_id'		=> 1,
                'status_id'		=> 1,
            ),
            'left'
        );
        $this->_collection->addFieldToFilter('review_created_at', array('notnull' => true));

        /**
         * Reset currently set order as we only want to order on the review creation date.
         * Without this the collection will order on entity_id before the creation date.
         */
        $this->_collection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        /**
         * Now apply the new collection order.
         */

        $this->_collection->getSelect()->order('review_created_at desc')->group('e.entity_id');        return $this;
    }
}