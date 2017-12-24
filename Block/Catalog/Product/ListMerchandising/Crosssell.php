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


class Crosssell extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising {

    /**
     * Config path used to retrieve config setting values
     * @var string
     */

    protected $_configPath = 'crosssell';


    /**
     * add crosssell products to collection.
     *
     * @return $this
     */
    protected function _initCollection()
    {
        parent::_initCollection();
        $productIds = array();
        if (!$_product = $this->getCurrentProduct()) {
            foreach ($this->_getQuote()->getAllVisibleItems() as $_item) {
                $productIds = array_merge($productIds, $_item->getProduct()->getCrossSellProductIds());
            }
        } else {
            $productIds = $_product->getCrossSellProductIds();
        }
        //$this->enableCollectionRegistering();
        $this->_collection->addIdFilter($productIds);
        return $this;
    }

    protected function _getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}

