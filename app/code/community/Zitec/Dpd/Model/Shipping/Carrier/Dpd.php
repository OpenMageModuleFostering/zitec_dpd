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
class Zitec_Dpd_Model_Shipping_Carrier_Dpd extends Zitec_Dpd_Model_Shipping_Carrier_Abstract implements Zitec_PackedShipment_Model_Carrier_Interface
{
    const CARRIER_CODE = 'zitecDpd';
    protected $_code = 'zitecDpd';

    public function getAllowedMethods()
    {
        $optionsMethods = Mage::getSingleton('zitec_dpd/config_source_service')->toOptionArray(true);

        $result = array();
        foreach ($optionsMethods as $method) {
            $result[$method['value']] = $method['label'];
        }

        return $result;
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->_canCollectRates($request)) {
            return false;
        }

        // Recalculate the package value excluding any virtual products.
        if (!$this->getConfigFlag('include_virtual_price')) {
            $request->setPackageValue($request->getPackagePhysicalValue());
        }

        // Free shipping by qty
        $freeQty           = 0;
        $totalPriceInclTax = 0;
        $totalPriceExclTax = 0;
        foreach ($request->getAllItems() as $item) {
            $totalPriceInclTax += $item->getBaseRowTotalInclTax();
            $totalPriceExclTax += $item->getBaseRowTotal();

            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                        $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                    }
                }
            } elseif ($item->getFreeShipping()) {
                $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
            }
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty    = $request->getPackageQty();
        $oldPrice  = $request->getPackageValue();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);
        $request->setPackageValue($totalPriceInclTax);

        $this->_updateFreeMethodQuote($request);

        // The shipping price calculations for price vs destination is included.
        if ($this->_getTaxHelper()->shippingPriceIncludesTax($request->getStoreId())) {
            $request->setData('zitec_table_price', $totalPriceInclTax);
        } else {
            $request->setData('zitec_table_price', $totalPriceExclTax);
        }

        $rate = $this->getRate($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);
        $request->setPackageValue($oldPrice);

        $isFree               = false;
        $freeShippingPrice    = ($this->getConfigFlag('free_shipping_subtotal_tax_incl')) ? $totalPriceInclTax : $oldPrice;
        $freeShippingSubtotal = $this->getConfigData('free_shipping_subtotal');
        $freeShippingEnabled  = $this->getConfigFlag('free_shipping_enable');
        $freeShipping         = ($freeShippingEnabled && $freeShippingPrice >= $freeShippingSubtotal) ? true : false;
        if ($request->getFreeShipping() === true || $freeShipping) {
            $isFree = true;
        }

        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        if (count($rate) == 0) {
            return false;
        }

        $methods = array();
        foreach ($rate as $r) {
            // Before adding the rate, we check that it is active in the admin configuration.
            if (!$this->_isRateAllowedByAdminConfiguration($r)) {
                continue;
            }

            //There can be multiple rate the same method, but is first applicable.
            //If we have already considered a rate of this method, we again evaluate
            //(the other will be for weights / lower prices) or for more general conditions.

            $dpdMethod = $r['method'];
            if (in_array($dpdMethod, $methods)) {
                continue;
            }
            $methods[] = $dpdMethod;

            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $price = $this->_calculateDPDPrice($r, $request);
            if ($price === false) {
                continue;
            }
            $method->setPrice($price);
            $method->setCost($price);
            $method->setMethod($dpdMethod);
            $methodDescriptions = Mage::getSingleton('zitec_dpd/config_source_service')->toOptionArray();
            $methodTitle        = $methodDescriptions[$dpdMethod];
            $method->setMethodTitle($methodTitle);

            $result->append($method);
        }

        if ($isFree) {
            $cheapest = $result->getCheapestRate();
            if (!empty($cheapest)) {
                $cheapest->setPrice('0.00');
                $title = $cheapest->getMethodTitle() . ' (' . Mage::helper('shipping')->__('Free') . ')';
                $cheapest->setMethodTitle($title);
                $result->reset();
                $result->append($cheapest);
            }
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool
     */
    protected function _canCollectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // Exclude empty carts
        if (!$request->getAllItems()) {
            return false;
        }

        // Exclude carts containing products with no defined weight or where the
        // total weight of the cart is zero (virtual products only).
        if ($this->_cartContainsProductsOfZeroWeightOrWeighsNothing($request->getAllItems())) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return Mage_Tax_Helper_Data
     */
    protected function _getTaxHelper()
    {
        return Mage::helper('tax');
    }

    /**
     * Returns true if the cart contains items with no defined weight or the
     *  whole cart weighs nothing.
     *
     * @param array $itemsInCart
     *
     * @return boolean
     */
    protected function _cartContainsProductsOfZeroWeightOrWeighsNothing($itemsInCart)
    {

        if (!$itemsInCart) {
            return true;
        }

        $cartContainsNonVirtualItems = false;

        foreach ($itemsInCart as $item) {
            if ($item->getParentItem()) {
                continue;
            }


            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->getProduct()->isVirtual()) {
                        if (!$child->getWeight() || ($child->getWeight() == 0)) {
                            return true;
                        }
                        $cartContainsNonVirtualItems = true;
                    }
                }
            } elseif (!$item->getProduct()->isVirtual()) {
                if (!$item->getWeight() || ($item->getWeight() == 0)) {
                    return true;
                }
                $cartContainsNonVirtualItems = true;
            }
        }

        if ($cartContainsNonVirtualItems) {
            return false;
        } else {
            return true; // All items in cart are virtual
        }
    }

    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $rates = Mage::getResourceModel('zitec_dpd/carrier_tablerate')->getRate($request);

        return $rates;
    }

    /**
     *
     * @param array $shippingRate
     *
     * @return boolean
     */
    protected function _isRateAllowedByAdminConfiguration($shippingRate)
    {

        $availableMethods = explode(',', $this->getconfigData('services'));

        return in_array($shippingRate['method'], $availableMethods);
    }

    /**
     * We calculate the shipping price based on the price / rate mentioned in
     * the rates table. If a "markup_type" (percent) indicated we travel to DPD WS
     * to calculate the final price based on the shipping cost with
     * his ws. If the price / percentage is less than zero indicates that the rate is not available.
     *
     * @param array                            $rate
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array|boolean
     */
    protected function _calculateDPDPrice(array $rate, Mage_Shipping_Model_Rate_Request $request)
    {

        if (!$rate['markup_type']) {
            if ($rate['price'] >= 0) {
                return $rate['price'];
            } else {
                return false;
            }
        }

        $apiParams           = $this->_getWsHelper()->getShipmentParams($request->getStoreId());
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_CALCULATE_PRICE;

        try {
            $dpdApi         = new Zitec_Dpd_Api($apiParams);
            $calculatePrice = $dpdApi->getApiMethodObject();

            $postCode = Mage::helper('zitec_dpd/postcode_search')->extractPostCodeForShippingRequest($request);

            $calculatePrice->setReceiverAddress($request->getDestStreet(), $request->getDestCity(), $postCode, $request->getDestCountryId())
                ->addParcel($request->getPackageWeight())
                ->setShipmentServiceCode($rate['method']);

            $insurance      = Mage::helper('zitec_dpd')->extractInsuranceValuesByRequest($request);
            $calculatePrice = $calculatePrice->setAdditionalHighInsurance($insurance['goodsValue'], $insurance['currency'], $insurance['content']);

            $calculatePrice->execute();
        } catch (Exception $e) {
            $this->_getHelper()->log("An error occurred whilst calculating the DPD price for the shipment {$e->getMessage()}");

            return false;
        }

        $response = $calculatePrice->getCalculatePriceResponse();
        if ($response->hasError()) {
            $this->_getHelper()->log("DPD returned the following error whilst attempting to calculate the price of a shipment: {$response->getErrorText()}");

            return false;
        }


        if ($request->getBaseCurrency()->getCode() == $response->getCurrency()) {
            $dpdPrice = $response->getAmount();
        } else if ($request->getBaseCurrency()->getCode() == $response->getCurrencyLocal()) {
            $dpdPrice = $response->getAmountLocal();
        } else {
            $this->_getHelper()->log("An error occurred whilst calculating the price of a shipment. The currency of the shipment ({$request->getBaseCurrency()->getCode()}) does not correspond to the currency ({$response->getCurrency()}) or the local currency ({$response->getCurrencyLocal()})  used by DPD. ");

            return false;
        }
        if ($rate['markup_type'] == 1) {
            return $dpdPrice * (1 + ($rate['price'] / 100));
        } else {
            return $dpdPrice + round(floatval($rate['price']), 2);
        }
    }

    public function getCitiesForPostcode($postcode, &$errorMsg)
    {

    }

    public function getPostcodesForCity($city, &$errorMsg)
    {

    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string                 $city
     * @param string                 $postcode
     * @param array                  $weightsPackages
     * @param string                 $errorStr
     *
     * @return double
     */
    public function getShippingCost(Mage_Sales_Model_Order $order, $city, $postcode, $weightsPackages, &$errorStr)
    {
        $shippingAddress = $order->getShippingAddress();
        $city            = $city ? $city : $shippingAddress->getCity();
        $postcode        = $postcode ? $postcode : $shippingAddress->getPostcode();
        $serviceCode     = $this->_getHelper()->getDPDServiceCode($order->getShippingMethod());
        $street          = is_array($shippingAddress->getStreetFull()) ? implode("\n", $shippingAddress->getStreetFull()) : $shippingAddress->getStreetFull();

        $apiParams           = $this->_getWsHelper()->getShipmentParams($order->getStore());
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_CALCULATE_PRICE;


        try {
            $dpdApi         = new Zitec_Dpd_Api($apiParams);
            $calculatePrice = $dpdApi->getApiMethodObject();


            $calculatePrice->setReceiverAddress($street, $city, $postcode, $shippingAddress->getCountryId())
                ->setShipmentServiceCode($serviceCode);

            foreach ($weightsPackages as $parcelWeight) {
                $calculatePrice->addParcel($parcelWeight);
            }

            $insurance      = Mage::helper('zitec_dpd')->extractInsuranceValuesByOrder($order);
            $calculatePrice = $calculatePrice->setAdditionalHighInsurance($insurance['goodsValue'], $insurance['currency'], $insurance['content']);

            $calculatePrice->execute();
        } catch (Exception $e) {
            $errorStr = $this->_getHelper()->__("Error obtaining shipping price: %s", $e->getMessage());
            $this->_getHelper()->log("An error occurred whilst calculating the DPD price for the shipment {$e->getMessage()}");

            return 0;
        }

        $response = $calculatePrice->getCalculatePriceResponse();
        if ($response->hasError()) {
            $errorStr = $this->_getHelper()->__("DPD error: %s", $response->getErrorText());
            $this->_getHelper()->log("DPD returned the following error whilst attempting to calculate the price of a shipment: {$response->getErrorText()}");

            return 0;
        }


        if ($order->getBaseCurrencyCode() == $response->getCurrency()) {
            return $response->getAmount();
        } else if ($order->getBaseCurrencyCode() == $response->getCurrencyLocal()) {
            return $response->getAmountLocal();
        } else {
            $errorStr = $this->_getHelper()->__("Shipping price not available in order currency");
            $this->_getHelper()->log("An error occurred whilst calculating the price of a shipment. The base currency of the shipment ({$order->getBaseCurrencyCode()}) does not correspond to the currency ({$response->getCurrency()}) or the local currency ({$response->getCurrencyLocal()})  used by DPD.");

            return 0;
        }
    }

    /**
     *
     * @return boolean
     */
    public function supportsCalculationOfShippingCosts()
    {
        return true;
    }

    public function getTrackingInfo($trackingNumber)
    {


        $trackingCollection = Mage::getResourceModel('sales/order_shipment_track_collection');
        /* @var $trackingCollection Mage_Sales_Model_Mysql4_Order_Shipment_Track_Collection */
        $trackingCollection->addFieldToFilter('track_number', $trackingNumber);
        $track = $trackingCollection->getFirstItem();
        /* @var $track Mage_Sales_Model_Order_Shipment_Track */
        if (!$track->getId()) {
            $result = array("title" => $this->getConfigData("title"), "number" => $trackingNumber);

            return $result;
        }

        $shipment     = Mage::getModel('sales/order_shipment')/* @var $shipment Mage_Sales_Model_Order_Shipment */
        ->load($track->getParentId());
        $carrierTitle = $this->getConfigData("title", $shipment->getStore());

        $ships = Mage::getResourceModel('zitec_dpd/dpd_ship_collection');
        /* @var $ships Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection */
        $ship = $ships->getByShipmentId($track->getParentId());
        /* @var $ship Zitec_Dpd_Model_Dpd_Ship */
        if (!$ship) {
            $errorMessage = $this->_getHelper()->__("Could not load the stored tracking information for track %s", $trackingNumber);
            $this->_getHelper()->log($errorMessage);
            $result = $this->_getTrackingInfoObject($trackingNumber, $carrierTitle, $errorMessage);

            return $result;
        }

        $response = @unserialize($ship->getSaveShipmentResponse());
        /* @var $response Zitec_Dpd_Api_Shipment_Save_Response */
        if (!$response) {
            $errorMessage = $this->_getHelper()->__("Error loading stored tracking information for track %s", $trackingNumber);
            $this->_getHelper()->log($errorMessage);
            $result = $this->_getTrackingInfoObject($trackingNumber, $carrierTitle, $errorMessage);

            return $result;
        }

        try {

            $statusResponse = $this->_getWsHelper()->getShipmentStatus($response);

        } catch (Exception $e) {
            $errorMessage = $this->_getHelper()->__("Error calling DPD for track %s", $trackingNumber);
            $this->_getHelper()->log($errorMessage);
            $this->_getHelper()->log($e->getMessage());
            $result = $this->_getTrackingInfoObject($trackingNumber, $carrierTitle, $errorMessage);

            return $result;
        }

        if ($statusResponse->hasError()) {
            $errorMessage = $this->_getHelper()->__('Error calling DPD for track %s: %s ', $trackingNumber, $statusResponse->getErrorText());
            $this->_getHelper()->log($errorMessage);
            $result = $this->_getTrackingInfoObject($trackingNumber, $carrierTitle, $errorMessage);

            return $result;
        }

        $result = $this->_getTrackingInfoObject($trackingNumber, $carrierTitle, "", $statusResponse);

        return $result;
    }

    /**
     *
     * @param string                                            $trackingNumber
     * @param string                                            $carrierTitle
     * @param string                                            $errorMessage
     * @param Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response $response
     *
     * @return \Varien_Object
     */
    protected function _getTrackingInfoObject($trackingNumber, $carrierTitle, $errorMessage, Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response $response = null)
    {
        $result = $result = new Varien_Object();
        $result->setTracking($trackingNumber);
        $result->setCarrierTitle($carrierTitle);
        $result->setErrorMessage($errorMessage);
        if ($response) {
            $result->setUrl($response->getTrackingUrl());
            $result->setDeliverydate($response->getDeliveryDate());
            $result->setDeliverytime($response->getDeliveryTime());
            $result->setShippedDate($response->getShipDate());
            $result->setService($response->getServiceDescription());
            $result->setWeight($response->getWeight());
        }

        return $result;
    }

    public function isValidCityPostcode($city, $postcode, &$errorMsg)
    {

    }

    public function shippingMethodRequiresShipmentsOfOnlyOneParcel($shippingMethod)
    {

    }

    public function supportsAddressValidation($countryId)
    {

    }

}