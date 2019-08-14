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
class Zitec_ShippingReports_Model_Observer_Order extends Mage_Core_Model_Abstract
{

    /*
     * These marks are used to prevent the events are invoked twice.
     */
    protected $_beforeSaveAlreadyRun = false;


    /*
     * sales_order_shipment_save_before
     * @param Varien_Event_Observer $o
     * @return Varien_Event_Observer
     */
    public function beforeSave($o)
    {
        if ($this->_beforeSaveAlreadyRun) {
            return $o;
        }

        $order = $o->getEvent()->getOrder();

        // The total shipping costs for new orders is initialized.
        if (!$order->getId()) {
            $order->setData('zitec_total_shipping_cost', 0);
        }
        $this->_beforeSaveAlreadyRun = true;

        return $o;
    }


}


