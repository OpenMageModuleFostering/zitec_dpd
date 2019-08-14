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
 * Report for price vs cost of shipping.
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_ShippingReports_Model_Mysql4_Profitability_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{


    public function addReportFields()
    {
        $resource = Mage::getModel('core/resource');
        /* @var $resource Mage_Core_Model_Resource */
        $salesOrderAddressTable = $resource->getTableName('sales/order_address');
        $this->getSelect()
            ->columns(array('zitec_shipping_profit' => new Zend_Db_Expr("main_table.base_shipping_amount - main_table.zitec_total_shipping_cost")))
            ->joinLeft(array('zitec_shipping_address' => $salesOrderAddressTable),
                "main_table.entity_id = zitec_shipping_address.parent_id AND " .
                "zitec_shipping_address.address_type = 'shipping' ",
                array('zitec_shipping_name'       => "CONCAT(zitec_shipping_address.lastname, ', ', zitec_shipping_address.firstname) ",
                      'zitec_shipping_postcode'   => "zitec_shipping_address.postcode",
                      'zitec_shipping_region'     => "zitec_shipping_address.region",
                      'zitec_shipping_country_id' => "zitec_shipping_address.country_id"
                ));

        return $this;
    }

    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', array('in' => (array)$storeIds));
        }

        return $this;
    }

    /* 
     * @param Zend_Date $from
     * @param Zend_Date $to
     */

    public function setDateRange($from, $to)
    {
        $fromDate = $from->toString('YYYY-MM-dd HH:mm:ss');
        $toDate   = $to->toString('YYYY-MM-dd HH:mm:ss');

        $this->addAttributeToFilter('created_at', array('from' => $fromDate, 'to' => $toDate));

        return $this;
    }

}

