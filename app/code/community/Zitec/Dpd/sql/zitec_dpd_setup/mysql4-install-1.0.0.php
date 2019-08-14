<?php
/**
 * Zitec_Dpd – shipping carrier extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @copyright  Copyright (c) 2014 Zitec COM
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('zitec_dpd_ships')};
        CREATE TABLE {$this->getTable('zitec_dpd_ships')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `shipment_id` int(10) unsigned DEFAULT NULL,
          `order_id` int(10) unsigned DEFAULT NULL,
          `save_shipment_call` mediumtext,
          `save_shipment_response` mediumtext,
          `shipping_labels` mediumtext,
          `manifest` mediumtext,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        
        
        DROP TABLE IF EXISTS {$this->getTable('zitec_dpd_tablerate')};
        CREATE TABLE {$this->getTable('zitec_dpd_tablerate')} (
            `pk` int(10) unsigned NOT NULL auto_increment,
            `website_id` int(11) NOT NULL default '0',
            `dest_country_id` varchar(4) NOT NULL default '0',
            `dest_region_id` int(10) NOT NULL default '0',
            `dest_zip` varchar(10) NOT NULL default '',
            `weight` decimal(12,4) NOT NULL default '0.0000',
            `price` varchar(10) NOT NULL default '0.0000',
            `method` varchar(6) NOT NULL default '0',
            `markup_type` varchar(5) NOT NULL default '0',
            PRIMARY KEY  (`pk`),
            UNIQUE KEY `dest_country` (`website_id`,`dest_country_id`,`dest_region_id`,`dest_zip`,`weight`,`method`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");







$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('zitec_dpd_pickup_order')};

        CREATE TABLE {$this->getTable('zitec_dpd_pickup_order')} (
            `entity_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `reference` VARCHAR( 255 ) NOT NULL ,
            `dpd_id` INT NOT NULL ,
            `pickup_date` DATETIME NOT NULL ,
            `pickup_time_from` DATETIME NOT NULL ,
            `pickup_time_to` DATETIME NOT NULL ,
            `call_data` MEDIUMTEXT NOT NULL ,
            `response_data` MEDIUMTEXT NOT NULL ,
        INDEX (  `pickup_date` ,  `pickup_time_from` ,  `pickup_time_to` ) ,
        UNIQUE (
            `reference`
        )
        ) ENGINE = INNODB DEFAULT CHARSET=utf8;

       ");



$salesSetup = Mage::getModel('eav/entity_setup', 'sales_setup');
/** @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup->addAttribute('shipment', 'zitec_dpd_pickup_id', array('type' => 'int'));







$salesSetup = Mage::getModel('eav/entity_setup', 'sales_setup');
/** @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup->addAttribute('shipment', 'zitec_dpd_pickup_time', array('type' => 'datetime', "grid" => true));
$salesSetup->addAttribute('shipment', 'zitec_dpd_manifest_closed', array('type' => 'int', "grid" => true, "default" => 0));









$installer->getConnection()->addColumn($this->getTable('zitec_dpd_tablerate'), 'cashondelivery_surcharge', "varchar(20) DEFAULT NULL");

$salesSetup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$salesSetup->addAttribute('quote_address', 'zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));
$salesSetup->addAttribute('quote_address', 'base_zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));

$salesSetup->addAttribute('order', 'zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));
$salesSetup->addAttribute('order', 'base_zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));

$salesSetup->addAttribute('invoice', 'zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));
$salesSetup->addAttribute('invoice', 'base_zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));

$salesSetup->addAttribute('creditmemo', 'zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));
$salesSetup->addAttribute('creditmemo', 'base_zitec_dpd_cashondelivery_surcharge', array('type' => 'decimal'));

$salesSetup->addAttribute('quote_address', 'zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('quote_address', 'base_zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));

$salesSetup->addAttribute('order', 'zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('order', 'base_zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));

$salesSetup->addAttribute('invoice', 'zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('invoice', 'base_zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));

$salesSetup->addAttribute('creditmemo', 'zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('creditmemo', 'base_zitec_dpd_cashondelivery_surcharge_tax', array('type' => 'decimal'));

$status = Mage::getModel('sales/order_status');
$status->setStatus('zitec_dpd_pending_cashondelivery');
$status->setLabel('DPD Pending Cash On Delivery');
$status->assignState('pending_payment');
$status->save();






$installer->getConnection()->addColumn($this->getTable('zitec_dpd_tablerate'), 'price_vs_dest', "int(10) NOT NULL default '0'");






$installer->getConnection()->addColumn($this->getTable('zitec_dpd_tablerate'), 'cod_min_surcharge', "decimal(12,4)");






$installer->getConnection()->dropKey($this->getTable('zitec_dpd_tablerate'), "dest_country");

$installer->getConnection()->addKey(
    $this->getTable('zitec_dpd_tablerate'),
    "dest_country",
    array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'weight', 'method', 'price_vs_dest'),
    "unique");







//$installer->getConnection()->dropColumn($this->getTable('zitec_dpd_ships'), 'manifest');

$installer->getConnection()->addColumn($installer->getTable('zitec_dpd_ships'), 'manifest_id', "int");

$installer->run("

DROP TABLE IF EXISTS {$installer->getTable('zitec_dpd_manifest')};


CREATE TABLE  {$installer->getTable('zitec_dpd_manifest')} (
`manifest_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`manifest_ref` VARCHAR( 30 ) NOT NULL ,
`manifest_dpd_id` VARCHAR( 30 )  NULL ,
`manifest_dpd_name` VARCHAR( 30 ) NULL ,
`pdf` MEDIUMTEXT NOT NULL
) ENGINE = INNODB; ");






$installer->getConnection()->dropColumn($this->getTable('zitec_dpd_ships'), 'manifest');







$installer->endSetup();