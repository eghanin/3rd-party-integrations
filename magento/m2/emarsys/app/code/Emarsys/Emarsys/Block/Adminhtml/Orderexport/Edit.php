<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */

namespace Emarsys\Emarsys\Block\Adminhtml\Orderexport;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
    
        $this->getRequest = $request;
        $this->storeManager = $context->getStoreManager();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Subscriber edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Emarsys_Emarsys';
        $this->_controller = 'adminhtml_orderexport';
        $storeId = $this->getRequest->getParam('store');
        if ($storeId != '') {
            $url = $this->getUrl("emarsys_emarsys/orderexport/orderExport/storeId/$storeId");
        } else {
            $url = $this->getUrl("emarsys_emarsys/orderexport/orderExport");
        }

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        if (isset($storeId)) {
            $this->buttonList->add(
                'showreport',
                [
                    'label' => __("Export"),
                    'class' => 'save primary',
                    'onclick' => "bulkOrderExportData('" . $url . "')",
                ]
            );
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
