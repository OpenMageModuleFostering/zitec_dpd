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

$salesSetup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$salesSetup->addAttribute('quote_address', 'valid_auto_postcode', array('type' => 'smallint'));
$salesSetup->addAttribute('order_address', 'valid_auto_postcode', array('type' => 'smallint'));
$salesSetup->addAttribute('quote_address', 'auto_postcode', array('type' => 'varchar','length'=>10));
$salesSetup->addAttribute('order_address', 'auto_postcode', array('type' => 'varchar','length'=>10));


$installer->endSetup();