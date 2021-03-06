<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */
namespace Emarsys\Emarsys\Controller\Adminhtml\Testconnection;

use Emarsys\Emarsys\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class TestConnection for API credentials
 */
class Index extends \Magento\Backend\App\Action
{

    /**
     * @var Data
     */
    protected $emarsysHelper;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $config;

    /**
     * @var \Emarsys\Log\Helper\Logs
     */
    protected $logsHelper;

    /**
     * 
     * @param Data $emarsysHelper
     * @param \Magento\Backend\App\Action\Context $context
     * @param DateTime $date
     * @param \Emarsys\Log\Helper\Logs $logsHelper
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */
    public function __construct(
        Data $emarsysHelper,
        \Magento\Backend\App\Action\Context $context,
        DateTime $date,
        \Emarsys\Log\Helper\Logs $logsHelper,
        \Magento\Config\Model\ResourceModel\Config $config
    ) {
    
        $this->emarsysHelper = $emarsysHelper;
        $this->logsHelper = $logsHelper;
        $this->date = $date;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Emarsys test connection api credentials
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $website = $this->getRequest()->getParam('website');
        if (isset($params['store'])) {
            $storeId = $params['store'];
        } else {
            $storeId = 1;
        }
        $logsArray['job_code'] = 'testconnection';
        $logsArray['status'] = 'started';
        $logsArray['messages'] = 'Test connection initiated';
        $logsArray['created_at'] = $this->date->date('Y-m-d H:i:s', time());
        $logsArray['run_mode'] = 'Manual';
        $logsArray['auto_log'] = 'Complete';
        $logsArray['store_id'] = $storeId;
        $logsArray['website_id'] = $website;
        $logId = $this->logsHelper->manualLogs($logsArray);

        $username = trim($params['username']);
        $password = trim($params['password']);
        $endpoint = trim($params['endpoint']);
        $url = trim($params['url']);

        if ($username != "" && $password != '') {
            $scopeType = 'websites';
            $defaultScopeType = 'default';
            $defaultScopeId = '0';
            $scopeId = $website;
            if ($website == '') {
                $scopeType = 'default';
                $scopeId = 0;
            }
            $this->emarsysHelper->assignApiCredentials($username, $password, $endpoint, $url);
            $pingendpoint = 'settings';
            $response = $this->emarsysHelper->send('GET', $pingendpoint);

            if ($this->is_json($response)) {
                $jsonDecode = \Zend_Json::decode($response);
            } else {
                $jsonDecode['replyCode'] = 10;
            }

            if ($jsonDecode['replyCode'] == 0 && $jsonDecode['replyText'] == "OK") {
                try {
                    $this->config->saveConfig('emarsys_settings/emarsys_setting/enable', 1, $scopeType, $scopeId);
                    if ($website == 1) {
                        $this->config->saveConfig('emarsys_settings/emarsys_setting/enable', 1, $defaultScopeType, $defaultScopeId);
                    }

                    $logsArray['id'] = $logId;
                    $logsArray['emarsys_info'] = 'Test connection';
                    $logsArray['description'] = "Inserted status enbale";
                    $logsArray['action'] = 'Synced to Magento';
                    $logsArray['message_type'] = 'Success';
                    $logsArray['log_action'] = 'sync';
                    $this->logsHelper->logs($logsArray);

                    $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_endpoint', $endpoint, $scopeType, $scopeId);
                    if ($website == 1) {
                        $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_endpoint', $endpoint, $defaultScopeType, $defaultScopeId);
                    }

                    $logsArray['id'] = $logId;
                    $logsArray['emarsys_info'] = 'Test connection';
                    $logsArray['description'] = "Inserted endpoint.";
                    $logsArray['action'] = 'Synced to Magento';
                    $logsArray['message_type'] = 'Success';
                    $logsArray['log_action'] = 'sync';
                    $this->logsHelper->logs($logsArray);

                    if ($endpoint == 'custom') {
                        $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_custom_url', $url, $scopeType, $scopeId);
                        if ($website == 1) {
                            $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_custom_url', $url, $defaultScopeType, $defaultScopeId);
                        }
                        $logsArray['id'] = $logId;
                        $logsArray['emarsys_info'] = 'Test connection';
                        $logsArray['description'] = "Inserted custom url";
                        $logsArray['action'] = 'Synced to Magento';
                        $logsArray['message_type'] = 'Success';
                        $logsArray['log_action'] = 'sync';
                        $this->logsHelper->logs($logsArray);
                    }

                    $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_username', $username, $scopeType, $scopeId);
                    if ($website == 1) {
                        $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_username', $username, $defaultScopeType, $defaultScopeId);
                    }
                    $logsArray['id'] = $logId;
                    $logsArray['emarsys_info'] = 'Test connection';
                    $logsArray['description'] = "Inserted username";
                    $logsArray['action'] = 'Synced to Magento';
                    $logsArray['message_type'] = 'Success';
                    $logsArray['log_action'] = 'sync';
                    $this->logsHelper->logs($logsArray);

                    $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_password', $password, $scopeType, $scopeId);
                    if ($website == 1) {
                        $this->config->saveConfig('emarsys_settings/emarsys_setting/emarsys_api_password', $password, $defaultScopeType, $defaultScopeId);
                    }
                    $logsArray['id'] = $logId;
                    $logsArray['emarsys_info'] = 'Test connection';
                    $logsArray['description'] = "Inserted Password";
                    $logsArray['action'] = 'Synced to Magento';
                    $logsArray['message_type'] = 'Success';
                    $logsArray['log_action'] = 'sync';
                    $this->logsHelper->logs($logsArray);

                    $this->messageManager->addSuccess('Test connection is success.');

                    $logsArray['id'] = $logId;
                    $logsArray['executed_at'] = $this->date->date('Y-m-d H:i:s', time());
                    $logsArray['finished_at'] = $this->date->date('Y-m-d H:i:s', time());
                    $logsArray['status'] = 'success';
                    $logsArray['messages'] = 'Test connection Completed';
                    $this->logsHelper->manualLogsUpdate($logsArray);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->messageManager->addError('Test connection is failed.');
                    $logsArray['id'] = $logId;
                    $logsArray['executed_at'] = $this->date->date('Y-m-d H:i:s', time());
                    $logsArray['finished_at'] = $this->date->date('Y-m-d H:i:s', time());
                    $logsArray['status'] = 'error';
                    $logsArray['messages'] = 'Test connection failed';
                    $this->logsHelper->manualLogsUpdate($logsArray);
                }
            } else {
                $this->messageManager->addError('Test connection is failed. Please check credentials.');
                $logsArray['id'] = $logId;
                $logsArray['executed_at'] = $this->date->date('Y-m-d H:i:s', time());
                $logsArray['finished_at'] = $this->date->date('Y-m-d H:i:s', time());
                $logsArray['status'] = 'error';
                $logsArray['messages'] = 'Test connection is failed. Please check credentials. ' . json_encode($jsonDecode, JSON_PRETTY_PRINT);
                $this->logsHelper->manualLogsUpdate($logsArray);
            }
        } else {
            $this->messageManager->addError('Please enter the api credentials');
            $logsArray['id'] = $logId;
            $logsArray['executed_at'] = $this->date->date('Y-m-d H:i:s', time());
            $logsArray['finished_at'] = $this->date->date('Y-m-d H:i:s', time());
            $logsArray['status'] = 'error';
            $logsArray['messages'] = 'Test connection is failed. Please enter the api credentials';
            $this->logsHelper->manualLogsUpdate($logsArray);
        }
    }

    public function is_json($x)
    {
        if (!is_string($x) || !trim($x)) {
            return false;
        }
        return (
            // Maybe an empty string, array or object
            $x === '""' ||
            $x === '[]' ||
            $x === '{}' ||
            // Maybe an encoded JSON string
            $x[0] === '"' ||
            // Maybe a flat array
            $x[0] === '[' ||
            // Maybe an associative array
            $x[0] === '{'
        ) && ($x === 'null' || json_decode($x) !== null);
    }
}
