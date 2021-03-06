<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Schedular
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */

namespace Emarsys\Scheduler\Block\Adminhtml\Scheduler;

/**
 * Class Grid
 * @package Emarsys\Scheduler\Block\Adminhtml\Scheduler
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $dataCollection;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $cronConfig;
    /**
     * @var \Magento\Framework\Dataobject
     */
    protected $dataObject;
    /**
     * @var \Emarsys\Scheduler\Model\SchedulerFactory
     */
    protected $schedulerFactory;
    /**
     * @var \Emarsys\Scheduler\Helper\Data
     */
    protected $schedulerHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Emarsys\Scheduler\Helper\Data $schedulerHelper
     * @param \Magento\Framework\Data\Collection $dataCollection
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Emarsys\Scheduler\Model\SchedulerFactory $schedulerFactory
     * @param \Magento\Cron\Model\ConfigInterface $cronConfig
     * @param \Magento\Framework\Dataobject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emarsys\Scheduler\Helper\Data $schedulerHelper,
        \Magento\Framework\Data\Collection $dataCollection,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Emarsys\Scheduler\Model\SchedulerFactory $schedulerFactory,
        \Magento\Cron\Model\ConfigInterface $cronConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Dataobject $dataObject,
        $data = []
    ) {
    
        $this->schedulerHelper = $schedulerHelper;
        $this->redirectFactory = $redirectFactory;
        $this->backendHelper = $backendHelper;
        $this->dataCollection = $dataCollection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cronConfig = $cronConfig;
        $this->dataObject = $dataObject;
        $this->getRequest = $request;
        $this->schedulerFactory = $schedulerFactory;
        parent::__construct($context, $backendHelper, $data = []);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('LogsGrid');
        $this->setDefaultSort('frontend_label');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('frontend_label');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $filterCode = $this->getRequest()->getParam('filtercode');
        $storeId = $this->getRequest->getParam('store');
        $collection = $this->schedulerFactory->create()
            ->getCollection()
            ->setOrder('created_at', 'desc')
            ->addFieldToFilter('store_id', $storeId);

        if ($filterCode != '') {
            $collection->addFieldToFilter('job_code', $filterCode);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $viewHelper = $this->schedulerHelper;
        $this->addColumn("job_code", [
            "header" => __("Code"),
            "align" => "left",
            'width' => '25',
            "index" => "job_code",
            'type' => 'options',
            'options' => [
                'customer' => 'Customer',
                'subscriber' => 'Subscriber',
                'order' => 'Order',
                'testconnection' => 'Test Connection',
                'product' => 'Product',
                'initialdbload' => 'Initial Load',
                'transactional_mail' => 'Transactional Mail',
                'Event Mapping' =>'Event Mapping',
                'Order Mapping' =>'Order Mapping',
                'Customer Mapping' =>'Customer Mapping',
                'Customer Filed Mapping' =>'Customer Filed Mapping',
                'Product Mapping' =>'Product Mapping'
            ]
        ]);

        $this->addColumn("created_at", [
            "header" => __("Created"),
            "align" => "left",
            "index" => "created_at",
            'width' => '150',
            'type' => 'timestamp',
            'frame_callback' => [$viewHelper, 'decorateTimeFrameCallBack']
        ]);

        $this->addColumn("executed_at", [
            "header" => __("Executed"),
            "align" => "left",
            "index" => "executed_at",
            'width' => '150',
            'type' => 'timestamp',
            'frame_callback' => [$viewHelper, 'decorateTimeFrameCallBack']
        ]);

        $this->addColumn("finished_at", [
            "header" => __("Finished"),
            "align" => "left",
            "index" => "finished_at",
            'width' => '150',
            'type' => 'timestamp',
            'frame_callback' => [$viewHelper, 'decorateTimeFrameCallBack']
        ]);

        $this->addColumn("run_mode", [
            "header" => __("Run Mode"),
            "align" => "left",
            "index" => "run_mode",
            'width' => '150',
            'type' => 'options',
            'options' => ['Automatic' => 'Automatic', 'Manual' => 'Manual']
        ]);

        $this->addColumn("auto_log", [
            "header" => __("Type"),
            "align" => "left",
            "index" => "auto_log",
            'width' => '150',
            'type' => 'options',
            'options' => ['Complete' => 'Complete', 'Individual' => 'Individual']
        ]);

        $this->addColumn("messages", [
            "header" => __("Messages"),
            "align" => "left",
            "index" => "messages",
            'width' => '150'
        ]);

        $this->addColumn("status", [
            "header" => __("Status"),
            "align" => "center",
            "index" => "status",
            'width' => '150',
            'type' => 'options',
            'renderer' => 'Emarsys\Scheduler\Block\Adminhtml\Scheduler\Renderer\StatusColor',
            'options' => [
                'success' => 'success',
                'error' => 'error',
                'missed' => 'missed',
                'running' => 'running',
                'notice' => 'notice',
                'started' => 'Started'
            ]
        ]);

        $this->addColumn("id", [
            "header" => __("Details"),
            "align" => "left",
            "index" => "id",
            'renderer' => 'Emarsys\Scheduler\Block\Adminhtml\Scheduler\Renderer\ViewButton',
            'width' => '150'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string|void
     */
    public function getRowUrl($item)
    {
        parent::getRowUrl($item);
    }
}
