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
 * @package     Merchandising
 * @copyright   Copyright (c) 2016 Vortex Commerce Ltd (http://www.vortexcommerce.com)
 * @license     http://www.vortexcommerce.com/CE-MODULE-LICENSE.txt (VORTEX COMMERCE LTD EULA)
 * @author      Richard Cleverley, richard.cleverley@vortexcommerce.com, Vortex Commerce Ltd
 *
 * SUPPORT
 * For commercial support please visit http://www.vortexcommerce.com.
 *
 */
namespace Vortex\Merchandising\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getIsHomepage()
    {
        $currentUrl = $this->_getUrl('', ['_current' => true]);
        $urlRewrite = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
}