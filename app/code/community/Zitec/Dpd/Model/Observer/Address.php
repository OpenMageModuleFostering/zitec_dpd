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
class Zitec_Dpd_Model_Observer_Address
{

    /**
     * Communicate the updated address to DPD.
     */
    public function salesOrderAddressAfterSave(Varien_Event_Observer $observer)
    {
        if (!$this->_getHelper()->isAdmin()) {
            return;
        }


        $address = $observer->getEvent()->getAddress();
        /* @var $address Mage_Sales_Model_Order_Address */
        if ($address->getAddressType() != 'shipping') {
            return;
        }

        $order = $address->getOrder();
        /* @var $order Mage_Sales_Model_Order */
        if (!$this->_getHelper()->moduleIsActive($order->getStore())) {
            return;
        }

        if (!$this->_getHelper()->isShippingMethodDpd($order->getShippingMethod())) {
            return;
        }

        if (!$this->_communicateAddresUpdateToDpd($address)) {
            return;
        }

        $this->_getHelper()->addNotice($this->__("The new shipping address for shipments associated with this order have been communicated successfully to DPD."));

    }

    /**
     * change the status of postcode validation
     *
     */
    public function salesOrderAddressBeforeSave(Varien_Event_Observer $observer)
    {
        if (!$this->_getHelper()->isAdmin()) {
            return;
        }


        $address = $observer->getEvent()->getAddress();
        /* @var $address Mage_Sales_Model_Order_Address */
        if ($address->getAddressType() != 'shipping') {
            return;
        }

        $order = $address->getOrder();
        /* @var $order Mage_Sales_Model_Order */
        if (!$this->_getHelper()->moduleIsActive($order->getStore())) {
            return;
        }

        if (!$this->_getHelper()->isShippingMethodDpd($order->getShippingMethod())) {
            return;
        }

        $origPostcode = $address->getOrigData('postcode');
        $newPostcode  = $address->getPostcode();
        if ($origPostcode != $newPostcode){
            $address->setValidAutoPostcode(1);
        }


    }


    /**
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return boolean
     */
    protected function _communicateAddresUpdateToDpd(Mage_Sales_Model_Order_Address $address)
    {

        $shipsCollectionForOrder = Mage::getResourceModel('zitec_dpd/dpd_ship_collection');
        /* @var $shipsCollectionForOrder Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection */
        $shipsCollectionForOrder->setOrderFilter($address->getParentId());
        if (!$shipsCollectionForOrder->count()) {
            return false;
        }
        foreach ($shipsCollectionForOrder as $ship) {
            /* @var $ship Zitec_Dpd_Model_Dpd_Ship */
            $dpdShipment = unserialize($ship->getSaveShipmentCall());
            /* @var $dpdShipmnent Zitec_Dpd_Api_Shipment_Save */
            try {
                $response = $dpdShipment->setReceiverAddress($address)
                    ->execute();
                /* @var $response Zitec_Dpd_Api_Shipment_Save_Response */
            } catch (Exception $e) {
                Mage::throwException(sprintf($this->__('An error occurred updating the shipping address details with DPD: <br /> "%s"', $e->getMessage())));
            }
            if ($response->hasError()) {
                Mage::throwException(sprintf($this->__('DPD could not update the shipment address. The following error was returned: <br /> "%s: %s"'), $response->getErrorCode(), $response->getErrorText()));
            }

            try {
                $labelPdfStr = $this->_getWsHelper()->getNewPdfShipmentLabelsStr($response->getDpdShipmentId(), $response->getDpdShipmentReferenceNumber());
            } catch (Exception $e) {
                Mage::throwException(sprintf($this->__('An error occurred retrieving the updated shipping labels from DPD. <br />"s%"'), $e->getMessage()));
            }

            $ship->setSaveShipmentCall(serialize($dpdShipment))
                ->setSaveShipmentResponse(serialize($response))
                ->setShippingLabels(base64_encode($labelPdfStr))
                ->save();

            Mage::getModel('sales/order_shipment')
                ->load($ship->getShipmentId())
                ->setShippingLabel($labelPdfStr)
                ->save();
        }

        return true;
    }


    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }

    /**
     *
     * @param string $translateStr
     *
     * @return string
     */
    protected function __($translateStr)
    {
        return $this->_getHelper()->__($translateStr);
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Ws
     */
    protected function _getWsHelper()
    {
        return Mage::helper('zitec_dpd/ws');
    }
}


