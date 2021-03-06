<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */

namespace Emarsys\Emarsys\Block\Adminhtml\Mapping\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Product
     */
    protected $resourceModelProduct;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $dataCollection;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $_collection
     * @param \Magento\Framework\Data\Collection $dataCollection
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Emarsys\Emarsys\Model\ResourceModel\Product $resourceModelProduct
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Backend\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $_collection,
        \Magento\Framework\Data\Collection $dataCollection,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Emarsys\Emarsys\Model\ResourceModel\Product $resourceModelProduct,
        \Magento\Framework\Module\Manager $moduleManager,
        $data = []
    ) {
    
        $this->session = $context->getBackendSession();
        $this->_collection = $_collection;
        $this->moduleManager = $moduleManager;
        $this->backendHelper = $backendHelper;
        $this->dataCollection = $dataCollection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resourceModelProduct = $resourceModelProduct;
        parent::__construct($context, $backendHelper, $data = []);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('grid_record_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('grid_record');
        $this->session->setData('gridData', '');
        $storeId = $this->getRequest()->getParam('store');
        $methods = $this->_collection->addVisibleFilter();
        $productMethods = [];
        foreach ($methods as $productCode => $productModel) {
            $productMethods['product'] = $productCode;
            $rowObj = $this->dataObjectFactory->create();
            $rowObj->setData($productMethods);
            $this->dataCollection->addItem($rowObj);
        }

        $gridArray = [];
        foreach ($this->dataCollection as $col) {
            $attrCode = $col->getAttributeCode();
            $gridArray[$attrCode] = ['emarsys_attr_code' => 'please select', 'store_id' => $storeId];
        }
        $this->session->setData('gridData', $gridArray);
        $this->session->setData('storeId', $storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collection->addVisibleFilter()->setOrder('main_table.frontend_label', 'ASC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Magento Product Attribute'),
                'type' => 'varchar',
                'index' => 'frontend_label',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'emarsys_attr_code',
            [
                'header' => __('Emarsys Attribute'),
                'renderer' => '\Emarsys\Emarsys\Block\Adminhtml\Mapping\Product\Renderer\EmarsysProduct',
                'filter' => false
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId == 0 || $storeId == null) {
            $storeId = 1;
        }
        $moduleName = 'emarsys_emarsys';
        $controllerNamePleaseSelect = 'mapping_product';
        $controllerNameField = 'mapping_field';
        $controllerNameProduct = 'mapping_product';
        $controllerNameCustomer = 'mapping_customer';
        $controllerNameOrder = 'mapping_order';
        $controllerNameEvent = 'mapping_event';

        $adminUrlEmarsysField = $this->backendHelper->getUrl($moduleName . '/' . $controllerNameField . '/index/store/' . $storeId);
        $adminUrlEmarsysProduct = $this->backendHelper->getUrl($moduleName . '/' . $controllerNameProduct . '/index/store/' . $storeId);
        $adminUrlEmarsysCustomer = $this->backendHelper->getUrl($moduleName . '/' . $controllerNameCustomer . '/index/store/' . $storeId);
        $adminUrlEmarsysOrder = $this->backendHelper->getUrl($moduleName . '/' . $controllerNameOrder . '/index/store/' . $storeId);
        $adminUrlEmarsysEvent = $this->backendHelper->getUrl($moduleName . '/' . $controllerNameEvent . '/index/store/' . $storeId);
        $adminUrlEmarsysPleaseSelect = $this->backendHelper->getUrl($moduleName . '/' . $controllerNamePleaseSelect . '/index/store/' . $storeId);

        $html = parent::getMainButtonsHtml();
        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
}
