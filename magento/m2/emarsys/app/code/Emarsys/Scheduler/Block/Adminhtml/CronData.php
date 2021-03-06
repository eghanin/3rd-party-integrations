<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Schedular
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */
namespace Emarsys\Scheduler\Block\Adminhtml;

class CronData extends \Magento\Framework\View\Element\Template
{
    protected $_config;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cron\Model\ConfigInterface $config,
        $data = []
    ) {
    
        parent::__construct($context, $data = []);
        $this->_config = $config;
        $jobGroupsRoot = $this->_config->getJobs();
        $data = $this->_request->getParam('group');
    }

    function getCronData()
    {
        return $this->_config->getJobs();
    }
}
