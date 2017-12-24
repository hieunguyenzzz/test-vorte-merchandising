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

class Mostviewed extends \Vortex\Merchandising\Block\Catalog\Product\Merchandising\ListMerchandising
{

    /**
     * Config path used to retrieve config setting values
     * @var string
     */

    protected $_configPath = 'mostviewed';

    /**
     * Event type used to filter collections
     * @var string
     */
    protected $_eventType ='catalog_product_view';


    /**
     * Initialise product collection and add date filters.
     *
     * @return $this
     */
    protected function _initCollection()
    {
        $today = time();
        $last = $today - (86400 * $this->getLookupDays());
        $from = date("Y-m-d", $last);
        $to = date("Y-m-d", $today);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_collection = $objectManager->create('Magento\Reports\Model\ResourceModel\Product\Collection');
        $this->addViewsCount($from, $to);

        if ($this->getFilterStore()) {
            $this->_collection->setStoreId($this->getStore()->getId())
                ->addStoreFilter($this->getStore()->getId());
        }

        $this->_collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()])
            //Only show Catalog,Search & Catalog products
            ->setVisibility($this->_visibility->getVisibleInCatalogIds())
            //Add SEO URL rewrites to collection
            ->addUrlRewrite();

        return $this;
    }



}