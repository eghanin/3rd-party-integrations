<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2016 Kensium Solution Pvt.Ltd. (http://www.kensiumsolutions.com/)
 */
namespace Emarsys\Emarsys;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class Observer implements ObserverInterface
{
    /**
     *
     * @var $config 
     */
    protected $config;
    /**
     *
     * @var $date 
     */
    protected $date;
    /**
     *
     * @var $baseDirPath 
     */
    protected $baseDirPath;
    /**
     *
     * @var $scopeConfig 
     */
    protected $scopeConfig;
    /**
     *
     * @var $_resource 
     */
    protected $_resource;
    /**
     *
     * @var $resourceConfig 
     */
    protected $resourceConfig;
    /**
     *
     * @var $ioFile 
     */
    protected $ioFile;
    /**
     *
     * @var $dataHelper 
     */
    protected $dataHelper;
    /**
     *
     * @var $storeManager 
     */
    protected $storeManager;
    /**
     *
     * @var $_logger 
     */
    protected $_logger;
    /**
     *
     * @var $_transportBuilder 
     */
    protected $_transportBuilder;
    /**
     *
     * @var $inlineTranslation 
     */
    protected $inlineTranslation;

    /**
     * 
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Filesystem\DirectoryList $baseDirPath
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Emarsys\Emarsys\Helper\Data $dataHelper
     * @param \Magento\Framework\App\DeploymentConfig $config
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $baseDirPath,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Emarsys\Emarsys\Helper\Data $dataHelper,
        \Magento\Framework\App\DeploymentConfig $config,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
    
        $this->config = $config;
        $this->date = $date;
        $this->baseDirPath = $baseDirPath;
        $this->scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        $this->resourceConfig = $resourceConfig;
        $this->ioFile = $ioFile;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->_logger = $logger;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }

    public function cleanLog()
    {
        foreach($this->storeManager->getStores() as $storeData)
        {
            $websiteId = $storeData->getWebsiteId();
            $storeId = $storeData->getStoreId();
            $scopeType = 'websites';

            $configData = $this->config->getConfigData();
            $connection = $configData['db']['connection']['default'];

            $username = $connection['username'];
            $password = $connection['password'];
            $hostname = $connection['host'];
            $database = $connection['dbname'];

            $username = escapeshellcmd($username);
            $password = escapeshellcmd($password);
            $hostname = escapeshellcmd($hostname);
            $database = escapeshellcmd($database);

            $logCleaning = $this->scopeConfig->getValue('logs/log_setting/log_cleaning', $scopeType, $websiteId);
            if ($logCleaning == '' && $websiteId == 1) {
                $logCleaning = $this->scopeConfig->getValue('logs/log_setting/log_cleaning');
            }

            $archive = $this->scopeConfig->getValue('logs/log_setting/archive_data', $scopeType, $websiteId);
            if ($archive == '' && $websiteId == 1) {
                $archive = $this->scopeConfig->getValue('logs/log_setting/archive_data');
            }

            if ($logCleaning) {
                $logCleaningDays = $this->scopeConfig->getValue('logs/log_setting/log_days', $scopeType, $websiteId);
                if ($logCleaningDays == '' && $websiteId == 1) {
                    $logCleaningDays = $this->scopeConfig->getValue('logs/log_setting/log_days');
                }
                $cleanUpDate = $this->date->date('Y-m-d', strtotime("-" . $logCleaningDays . " days"));
                $cleanUpDate = $this->dataHelper->getDateTimeInLocalTimezone($cleanUpDate);
                /* Create archive folder*/

                $varDir = $this->baseDirPath->getRoot() . "/";
                $archivePath = $this->scopeConfig->getValue('logs/log_setting/archive_datapath', $scopeType, $websiteId);
                if ($archivePath == '' && $storeId == 1) {
                    $archivePath = $this->scopeConfig->getValue('logs/log_setting/archive_datapath');
                }
                $archiveFolder = $varDir . $archivePath . $this->date->date('Y-m-d');
                $this->ioFile->checkAndCreateFolder($archiveFolder);

                /* backup sql file*/
                $backupFile = $archiveFolder . "/emarsys_logs.sql";

                /* logs table */
                $logTable = $this->resourceConfig->getTable('emarsys_log_details');

                /* Dump record of logs table in one file */
                $successLog = 0;
                $errorLog = 0;

                try {
                    if ($archive == 'archive') {
                        $command = "mysqldump -u$username -p$password -h$hostname $database $logTable > $backupFile";
                        system($command, $result);
                    }

                    /* Delete record from log_details tables */
                    $sqlConnection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
                    try {
                        $query = "SELECT id FROM " . $this->resourceConfig->getTable($logTable) . " WHERE DATE(created_at) <= '" . $cleanUpDate . "'";
                        $queryRead = $sqlConnection->query($query);
                        $row = $queryRead->fetchAll();
                        if (count($row)) {
                            foreach ($row as $result) {
                                $sqlConnection->query("DELETE FROM " . $this->resourceConfig->getTable($logTable) . "  WHERE id = " . $result['id']);
                            }
                        }
                    } catch (Exception $e) {
                        $errorLog = 1;
                        $errorResult[] = $e->getMessage();
                    }
                    $successLog = 1;
                } catch (Exception $e) {
                    $errorLog = 1;
                    $errorResult[] = $e->getMessage();
                }

                /**
                 * Email send if failure
                 */

                $errorEmailData = $this->dataHelper->logErrorSenderEmail();
                $senderEmailId = $errorEmailData['email'];
                $senderEmailName = $errorEmailData['name'];
                $logEmailRecipient = $this->scopeConfig->getValue('logs/log_setting/log_email_recipient', $scopeType, $websiteId);
                if ($logEmailRecipient == '' && $websiteId == 1) {
                    $logEmailRecipient = $this->scopeConfig->getValue('logs/log_setting/log_email_recipient');
                }
                if ($successLog == 1) {
                    $templateOptions = ['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => $storeId];
                    $templateVars = [
                        'store' => $storeId,
                        'message' => $backupFile
                    ];
                    $from = [
                        'email' => $senderEmailId,
                        'name' => $senderEmailName
                    ];
                    $this->inlineTranslation->suspend();
                    $to = [
                        'email' => $logEmailRecipient,
                        'name' => $logEmailRecipient
                    ];
                    $transport = $this->_transportBuilder->setTemplateIdentifier('emarsys_log_cleaning_template')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                }

                /*Send email for Error in archived File*/
                if ($errorLog == 1) {
                    $errorMessage = implode(",", $errorResult);
                    $templateOptions = ['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => $storeId];
                    $templateVars = [
                        'store' => $storeId,
                        'message' => $errorMessage
                    ];
                    $from = [
                        'email' => $senderEmailId,
                        'name' => $senderEmailName
                    ];
                    $this->inlineTranslation->suspend();
                    $to = [
                        'email' => $logEmailRecipient,
                        'name' => $logEmailRecipient
                    ];
                    $transport = $this->_transportBuilder->setTemplateIdentifier('emarsys_log_cleaning_error_template')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                }

                /**
                 * Delete the archived Data older than $deleteArchivedData
                 */

                if ($archive == 'archive') {
                    $deleteArchivedData = $this->scopeConfig->getValue('logs/log_setting/delete_archive_days', $scopeType, $websiteId); // Number of days to delete old archived data
                    if ($deleteArchivedData == '' && $websiteId == 1) {
                        $deleteArchivedData = $this->scopeConfig->getValue('logs/log_setting/delete_archive_days');
                    }
                    $deleteDate = $this->date->date('Y-m-d', strtotime("-" . $deleteArchivedData . " days")); //date to delete old archived data
                    $deleteDate = $this->dataHelper->getDateTimeInLocalTimezone($deleteDate);
                    $archiveFolderPath = $archivePath;                                      //Archive folder path
                    $dir = new \DirectoryIterator($archiveFolderPath);                      //Sub-dir inside archive dir
                    foreach ($dir as $fileinfo) {
                        if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                            $path = $archiveFolderPath . "/" . $fileinfo->getFilename();        //get sub-dir name
                            $stat = stat($path);
                            $folderCreateDate = $this->date->date('Y-m-d', $stat['ctime']);  //create date of sub-dir
                            $folderCreateDate = $this->dataHelper->getDateTimeInLocalTimezone($folderCreateDate);
                            /* If create date of sub-dir less than delete date of sub-dir then delete sub-dir */
                            if ($folderCreateDate <= $deleteDate && is_dir($path)) {
                                array_map('unlink', glob($path . "/*"));                      //delete all files inside directory
                                rmdir($path);                                               //delete directory
                            }
                        }
                    }
                }
            }
        }
    }
}
