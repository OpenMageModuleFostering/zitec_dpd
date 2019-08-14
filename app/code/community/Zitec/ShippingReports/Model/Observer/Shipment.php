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
class Zitec_ShippingReports_Model_Observer_Shipment extends Mage_Core_Model_Abstract
{

    /*
     * These marks are used to prevent the events are invoked twice.
     */
    protected $_beforeSaveAlreadyRun = false;


    /*
     * sales_order_shipment_save_before
     * If invoked from the page 'New Shipment' Magento Admin
     * look if present in the POST data shipping cost added by module PackedShipment.
     * If yes is present, update shipping and order with the cost reports.
     * @param Varien_Event_Observer $o
     * @return Varien_Event_Observer
     */
    public function beforeSave($o)
    {
        if ($this->_beforeSaveAlreadyRun) {
            return $o;
        }

        // For all new submissions, it is initialized


        $module     = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action     = Mage::app()->getRequest()->getActionName();


        $shippingCost = Mage::app()->getRequest()->getParam('zitecShippingResportsShippingCost');
        if (is_null($shippingCost) || strlen($shippingCost) == 0) {
            return $o;
        }

        $shipment = $o->getEvent()->getShipment();


        $oldShippingCost = $shipment->getData('zitec_shipping_cost', $shippingCost);


        $shipment->setData('zitec_shipping_cost', $shippingCost);

        // The order is updated with the total shipping cost.
        $order = $shipment->getOrder();

        $totalShippingCost = $order->getData('zitec_total_shipping_cost');
        $totalShippingCost = $totalShippingCost ? $totalShippingCost : 0.000;

        $totalShippingCost += $shippingCost;

        if ($oldShippingCost) {
            $totalShippingCost -= $oldShippingCost;
        }

        $order->setData('zitec_total_shipping_cost', (string)$totalShippingCost);

        $this->_beforeSaveAlreadyRun = true;

        return $o;
    }


}

