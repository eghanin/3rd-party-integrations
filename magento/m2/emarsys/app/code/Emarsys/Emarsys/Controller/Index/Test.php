<?php

namespace Emarsys\Emarsys\Controller\Index;

use Magento\Framework\App\Action\Context;

class Test extends \Magento\Framework\App\Action\Action
{

    /**
     * 
     * @param Context $context
     * @param \Emarsys\Emarsys\Helper\Event $eventHelper
     */
    public function __construct(
        Context $context,
        \Emarsys\Emarsys\Helper\Event $eventHelper
    ) {
    
        parent::__construct($context);
        $this->eventHelper = $eventHelper;
    }


    public function execute()
    {
        $eventSchema = $this->eventHelper->getEmar();
        $emarsysApiIds = [];
        $eventSchema = $this->eventHelper->getEventSchema();
        foreach ($eventSchema['data'] as $_eventSchema) {
            $emarsysApiIds[] = $_eventSchema['id'];
        }
        $emarsysLocalIds = $this->eventHelper->getLocalEmarsysEvents();
        $result = array_diff($emarsysApiIds, $emarsysLocalIds);
        if (count($result)) {
            $this->eventHelper->saveEmarsysEventSchemaNotification();
        }
    }
}
