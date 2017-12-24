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

class Featured extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising {

    /**
     * Config path used to retrieve config setting values
     * @var string
     */

    protected $_configPath = 'featured';


    /**
     * Add featured attribute to product collection
     *
     * @return Vortex_Summit_Block_Catalog_Product_Abstract
     */
    protected function _addAttributesToFilter()
    {
        $featuredAttribute = $this->_getFeaturedAttribute();
        if (is_array($featuredAttribute)) {
            $condition = array();
            foreach ($featuredAttribute as $attribute) {
                $condition[] = array('attribute' => $attribute, 'eq' => true);
            }
            $this->_collection->addAttributeToFilter($condition, null, 'left');
        } else {
            $this->_collection->addAttributeToFilter($featuredAttribute, true);
        }
        return parent::_addAttributesToFilter();
    }

    /**
     * Get the feature attribute code for the specific page
     *
     * @return string|array
     */
    protected function _getFeaturedAttribute()
    {
        $featuredAttribute = $this->_getConfig('featured_attribute');
        if ($this->getData('featured_attribute')) {
            $featuredAttribute = $this->getData('featured_attribute');
        } elseif ($this->helper->getIsHomePage()) {
            $featuredAttribute = $this->_getConfig('homepage_featured_attribute');
        } elseif ($this->getCurrentCategory()) {
            $featuredAttribute = $this->_getConfig('category_featured_attribute');
        }
        return $featuredAttribute;
    }




}