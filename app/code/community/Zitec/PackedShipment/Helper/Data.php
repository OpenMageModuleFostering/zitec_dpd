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
class Zitec_PackedShipment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * The total weight of a shipment is returned.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return float
     */
    public function getShipmentWeight(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $shipmentWeight = 0;
        foreach ($shipment->getAllItems() as $item) {
            $shipmentWeight += $item->getWeight() * $item->getQty();
        }

        return $shipmentWeight;
    }


    /**
     * True is returned if we transport the shipment in a single package.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     */
    public function mustShipInOneParcel(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $carrier = $shipment->getOrder()->getShippingCarrier();
        if ($carrier instanceof Zitec_PackedShipment_Model_Carrier_Interface) {
            $shippingMethod = $shipment->getOrder()->getShippingMethod();

            return $carrier->shippingMethodRequiresShipmentsOfOnlyOneParcel($shippingMethod);
        }

        return true;
    }

    /**
     * True is returned if the carrier can calculate shipping costs.
     *
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     *
     * @return boolean
     */
    public function carrierSupportsCalculationOfShippingCosts(Mage_Shipping_Model_Carrier_Abstract $carrier = null)
    {
        if ($carrier instanceof Zitec_PackedShipment_Model_Carrier_Interface) {
            return $carrier->supportsCalculationOfShippingCosts();
        }

        return false;
    }

    /**
     * True is returned if the carrier address validation support (postcode, town)
     *
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     * @param string                               $countryId
     *
     * @return boolean
     */
    public function carrierSupportsAddressValidation(Mage_Shipping_Model_Carrier_Abstract $carrier = null, $countryId = 'ES')
    {
        if ($carrier instanceof Zitec_PackedShipment_Model_Carrier_Interface) {
            return $carrier->supportsAddressValidation($countryId);
        }

        return false;
    }

    /**
     * We replace the template package if the carrier supports it.
     *
     * @return string
     */
    public function changeOrderItemsTemplate()
    {
        $shipment = Mage::registry('current_shipment');
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        if ($shipment && ($this->carrierSupportsPackedShipment($shipment->getOrder()->getShippingCarrier()))) {
            return 'zitec_packedshipment/sales/order/shipment/create/items.phtml';
        } else {
            return Mage::app()->getLayout()->getBlock('order_items')->getTemplate();
        }
    }

    /**
     *
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     *
     * @return boolean
     */
    public function carrierSupportsPackedShipment(Mage_Shipping_Model_Carrier_Abstract $carrier = null)
    {
        return $carrier instanceof Zitec_PackedShipment_Model_Carrier_Interface;
    }


    public function changeAddressValidationJsTemplate()
    {
        $shipment = Mage::registry('current_shipment');
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        if ($shipment && ($this->carrierSupportsPackedShipment($shipment->getOrder()->getShippingCarrier()))) {
            return 'zitec_packedshipment/sales/order/shipment/create/address_validation_info_js.phtml';
        } else {
            return '';
        }
    }

    /**
     * @return boolean
     */
    public function useDescriptionsInsteadOfReferences()
    {
        $useDescriptionsInsteadOfReferences = Mage::getStoreConfig("zitec_packedshipment/useDescriptionsInsteadOfReferences");

        return $useDescriptionsInsteadOfReferences ? true : false;
    }
}
