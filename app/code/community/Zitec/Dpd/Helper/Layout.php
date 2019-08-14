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
class Zitec_Dpd_Helper_Layout extends Mage_Core_Helper_Abstract
{

    const DEFAULT_TRACKING_TEMPLATE = 'sales/order/shipment/view/tracking.phtml';
    const DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE = "shipping/tracking/popup.phtml";

    /**
     *
     * @return string
     */
    public function getAdminhtmlTrackingTemplate()
    {
        if (!$this->_getHelper()->moduleIsActive()) {
            return self::DEFAULT_TRACKING_TEMPLATE;
        }

        $shipmentId = $this->_getRequest()->getParam('shipment_id');
        if (!$shipmentId) {
            return self::DEFAULT_TRACKING_TEMPLATE;
        }

        $shipment = Mage::getModel('sales/order_shipment')/* @var $shipment Mage_Sales_Model_Order_Shipment */
        ->load($shipmentId);
        if (!$this->_getHelper()->isShippingMethodDpd($shipment->getOrder()->getShippingMethod())) {
            return self::DEFAULT_TRACKING_TEMPLATE;
        }

        if ($this->_getHelper()->isMagentoVersionGreaterOrEqualTo('1', '7', '0', '0')) {
            return 'zitec_dpd/sales/order/shipment/view/tracking_17.phtml';
        } else {
            return 'zitec_dpd/sales/order/shipment/view/tracking.phtml';
        }
    }

    /**
     *
     * @return string
     */
    public function changeShippingTrackingPopupTemplate()
    {
        if (!$this->_getHelper()->moduleIsActive()) {
            return self::DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE;
        }

        $shippingInfo = Mage::registry("current_shipping_info");
        /* @var $shippingInfo Mage_Shipping_Model_Info */
        if (!$shippingInfo instanceof Mage_Shipping_Model_Info) {
            return self::DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE;
        }


        if ($shippingInfo->getOrderId()) {
            $orderId = $shippingInfo->getOrderId();
        } elseif ($shippingInfo->getShipId()) {
            $orderId = Mage::getModel('sales/order_shipment')->load($shippingInfo->getShipId())->getOrderId();
        } elseif ($shippingInfo->getTrackId()) {
            $orderId = Mage::getModel('sales/order_shipment_track')->load($shippingInfo->getTrackId())->getOrderId();
        } else {
            return self::DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE;
        }

        if (!$orderId) {
            return self::DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE;
        }

        $shippingMethod = Mage::getModel('sales/order')->load($orderId)->getShippingMethod();
        if (!$this->_getHelper()->isShippingMethodDpd($shippingMethod)) {
            return self::DEFAULT_SHIPPING_TRACKING_POPUP_TEMPLATE;
        }

        return 'zitec_dpd/shipping/tracking/popup.phtml';
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }
}


