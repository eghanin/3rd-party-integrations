<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2016 Emarsys Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */

namespace Emarsys\Emarsys\Block\Adminhtml\Mapping\Event\Renderer;

use Magento\Framework\DataObject;

class EmarsysEvent extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Customer\Collection
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Sync
     */
    protected $syncResourceModel;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Event
     */
    protected $resourceModelEvent;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Model\Session $session
     * @param \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Emarsys\Emarsys\Model\ResourceModel\Customer $resourceModelEvent
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emarsys\Emarsys\Model\ResourceModel\Event $resourceModelEvent,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Emarsys\Emarsys\Helper\Data $EmarsysHelper,
        \Emarsys\Emarsys\Model\ResourceModel\Emarsysevents\CollectionFactory $EmarsyseventCollection
    ) {
    
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->backendHelper = $backendHelper;
        $this->resourceModelEvent = $resourceModelEvent;
        $this->_storeManager = $storeManager;
        $this->EmarsyseventCollection = $EmarsyseventCollection;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->_urlInterface = $urlInterface;
    }


    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $storeId = $this->session->getData('store');
        $url = $this->_urlInterface->getUrl('*/*/changeValue');
        $row->getData('id');
        $params = ['mapping_id' => $row->getData('id'), 'store_id' => $storeId];
        $placeHolderUrl = $this->backendHelper->getUrl("*/*/placeholders", $params);
        $placeholderJsonRequestUrl = $this->backendHelper->getUrl("*/*/placeholderjson/mapping_id/" . $row->getData('id') . "/store_id/" . $storeId);

        $jsonRequestUrl = $this->backendHelper->getUrl("adminhtml/suite2email_placeholders/jsonrequest/", $params);
        $emarsysEvents = $this->EmarsyseventCollection->create()->addFieldToFilter('store_id', ['eq'=>$storeId]);
        $ronly = '';
        $buttonClass = '';
        if ($this->EmarsysHelper->isReadonlyMagentoEventId($row->getData('magento_event_id'))) {
            $ronly .= ' disabled = disabled';
            $buttonClass = ' disabled';
        }
        $html = '<select class="admin__control-select mapping-select" ' . $ronly . ' name="directions"  style="width:200px;" onchange="changeEmarsysValue(\'' . $url . '\',this.value, \'' . $row->getData('magento_event_id') . '\', \'' . $row->getData('id') . '\')";>
			<option value="0">Please Select</option>';
        foreach ($emarsysEvents as $emarsysEvent) {
            $sel = '';
            //echo $row->getData("emarsys_event_id")."---->".$emarsysEvent->getId()."--->".$emarsysEvent->getEmarsysEvent()."<br>";
            if ($row->getData("emarsys_event_id") == $emarsysEvent->getId()) {
                $sel .= 'selected = selected';
            }
            $html .= '<option ' . $sel . ' value="' . $emarsysEvent->getId() . '">' . $emarsysEvent->getEmarsysEvent() . '</option>';
        }

        $html .= '</select>';
        $html .= '&nbsp;&nbsp;&nbsp;<button ' . $buttonClass . ' type="button" class="scalable task form-button ' . $buttonClass . '" name="json" id="json"  onclick="openMyPopup(\'' . $placeholderJsonRequestUrl . '\');" >JSON Request</button>';
        $html .= '&nbsp;&nbsp;<button class="scalable task form-button" name="placeholders" id="placeholders"  onclick="placeholderRedirect(\'' . $placeHolderUrl . '\');" >Placeholders</button>';
        return $html;
    }
}
