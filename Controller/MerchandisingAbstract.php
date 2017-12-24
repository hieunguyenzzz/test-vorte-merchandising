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

namespace Vortex\Merchandising\Controller;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class MerchandisingAbstract extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_storeInfo;
    protected $_storeManagerInterface;

    protected $_objectManager;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_resultPageFactory = $pageFactory;

        parent::__construct(
            $context
        );
    }

    /**
     * Blog index page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return parent::execute();
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        $profilerKey = 'CONTROLLER_ACTION:' . $request->getFullActionName();
        $eventParameters = ['controller_action' => $this, 'request' => $request];
        $this->_eventManager->dispatch('controller_action_predispatch', $eventParameters);
        $this->_eventManager->dispatch('controller_action_predispatch_' . $request->getRouteName(), $eventParameters);
        $this->_eventManager->dispatch(
            'controller_action_predispatch_' . $request->getFullActionName(),
            $eventParameters
        );
        \Magento\Framework\Profiler::start($profilerKey);

        $result = null;
        if ($request->isDispatched() && !$this->_actionFlag->get('', self::FLAG_NO_DISPATCH)) {
            \Magento\Framework\Profiler::start('action_body');
            $result = $this->execute();
            \Magento\Framework\Profiler::start('postdispatch');
            if (!$this->_actionFlag->get('', self::FLAG_NO_POST_DISPATCH)) {
                $this->_eventManager->dispatch(
                    'controller_action_postdispatch_' . $request->getFullActionName(),
                    $eventParameters
                );
                $this->_eventManager->dispatch(
                    'controller_action_postdispatch_' . $request->getRouteName(),
                    $eventParameters
                );
                $this->_eventManager->dispatch('controller_action_postdispatch', $eventParameters);
            }
            \Magento\Framework\Profiler::stop('postdispatch');
            \Magento\Framework\Profiler::stop('action_body');
        }
        \Magento\Framework\Profiler::stop($profilerKey);

        // Adding in default handle for merchandising pages
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addHandle('merchandiser');
        $this->_eventManager->dispatch(
            'controller_action_updatehandle_merchandiser',
            ['controller_action' => $this, 'page' => $resultPage]
        );

        return $result ?: $this->_response;
    }


}