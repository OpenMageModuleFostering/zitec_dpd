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

try {
    $installer->getConnection()->addColumn($installer->getTable('sales/shipment'), 'shipping_label', 'LONGBLOB');
}catch (Exception $e){
    Mage::logException($e);
}

try {
    $installer->getConnection()->addColumn($installer->getTable('sales/shipment_track'), 'track_number', 'TEXT');
}catch (Exception $e){
    Mage::logException($e);
}



$installer->endSetup();