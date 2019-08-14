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
 * this class is used for
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * is used in checkout to extract the value of products
     * and the names
     *
     * @param $request
     *
     * @return array
     */
    public function extractInsuranceValuesByRequest($request)
    {
        $allItems    = $request->getAllItems();
        $description = '';
        $value       = 0;
        if (count($allItems)) {
            foreach ($allItems as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $description .= ' | '.$item->getName();

                $value += $item->getRowTotalInclTax();
            }
        }
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        return array(
            'goodsValue' => $value,
            'currency'   => $currencyCode,
            'content'    => $description
        );


    }

    /**
     * it is used in admin panel to process the products values and
     * products description
     *
     * @param $request
     *
     * @return array
     */
    public function extractInsuranceValuesByOrder($order)
    {
        $allItems    = $order->getAllItems();
        $description = '';
        $value       = 0;
        if (count($allItems)) {
            foreach ($allItems as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $description .= ' | '.$item->getName();

                $value += $item->getRowTotalInclTax();
            }
        }
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        return array(
            'goodsValue' => $value,
            'currency'   => $currencyCode,
            'content'    => $description
        );


    }

    /**
     * @param        $message
     * @param string $function
     * @param string $class
     * @param string $line
     *
     * @return $this
     */
    public function log($message, $function = '', $class = '', $line = '')
    {
        $location = ($class ? "$class::" : "") . $function . ($line ? " on line $line" : "");
        Mage::log($message . ($location ? " at $location" : ''), null, "zitec_dpd.log");

        return $this;
    }

    /**
     *
     * @param string $field
     * @param mixed  $store
     *
     * @return mixed
     */
    public function getConfigData($field, $store = null)
    {
        if (!$store) {
            $store = Mage::app()->getStore();
        }

        $carrierCode = $this->getDpdCarrierCode();

        return Mage::getStoreConfig("carriers/$carrierCode/$field", $store);
    }

    /**
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getCarrierName($store = null)
    {
        return $this->getConfigData("title", $store);
    }

    /**
     *
     * @param mixed $store
     *
     * @return boolean
     */
    public function moduleIsActive($store = null)
    {
        return $this->getConfigData("active", $store) ? true : false;
    }


    /**
     *
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     *
     * @return boolean
     */
    public function isCarrierDpd(Mage_Shipping_Model_Carrier_Abstract $carrier)
    {
        return $carrier instanceof Zitec_Dpd_Model_Shipping_Carrier_Dpd;
    }

    /**
     * test if a order was submited using dpd shipping carrier
     *
     * @param $order
     *
     * @return bool
     */
    public function isDpdCarrierByOrder($order)
    {
        if (!is_object($order)) {
            return false;
        }
        $carrier = $order->getShippingCarrier();

        return $this->isCarrierDpd($carrier);
    }

    /**
     * check if the postcode was marked as a valid postcode or not
     *
     * @param $order
     */
    public function isValidAutoPostcode($order)
    {
        if (!is_object($order)) {
            return false;
        }
        if (!$this->isEnabledPostcodeAutocompleteByOrder($order)) {
            //it should be valid
            return 1;
        }
        if (!$this->isDpdCarrierByOrder($order)) {
            //it should be valid
            return 1;
        }
        $_shippingAddress = $order->getShippingAddress();
        $isValid          = $_shippingAddress->getValidAutoPostcode();

        return $isValid;

    }


    public function isEnabledPostcodeAutocompleteByOrder($order)
    {
        if (!is_object($order)) {
            return false;
        }
        $_shippingAddress = $order->getShippingAddress();
        $address          = $_shippingAddress->getData();
        if (!empty($address['country_id'])) {
            $countryName        = Mage::getModel('directory/country')->loadByCode($address['country_id'])->getName();
            $address['country'] = $countryName;
        } else {
            return false;
        }

        return (Mage::helper('zitec_dpd/postcode_search')->isEnabledAutocompleteForPostcode($countryName));
    }

    /**
     * chceck the address length to be less then DPD API requirements
     *
     * @param $shippingAddress
     *
     * @return bool
     */
    public function checkAddressStreetLength($shippingAddress)
    {

        $shippingAddressStreetArray = $shippingAddress->getStreet();
        if (is_array($shippingAddressStreetArray)) {
            $shippingAddressStreet = '';
            foreach ($shippingAddressStreetArray as $street) {
                $shippingAddressStreet .= '' . $street;
            }
            $shippingAddressStreet = trim($shippingAddressStreet);
        } else {
            $shippingAddressStreet = $shippingAddressStreetArray;
        }

        return (strlen($shippingAddressStreet) <= Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH);
    }


    /**
     *
     * @param string|Mage_Sales_Model_Order_Shipment|Mage_Sales_Model_Order $shippingMethod
     *
     * @return boolean
     */
    public function isShippingMethodDpd($shippingMethod)
    {
        if ($shippingMethod instanceof Mage_Sales_Model_Order_Shipment) {
            $shippingMethod = $shippingMethod->getOrder()->getShippingMethod();
        } elseif ($shippingMethod instanceof Mage_Sales_Model_Order) {
            $shippingMethod = $shippingMethod->getShippingMethod();
        }

        return is_string($shippingMethod) && (strpos($shippingMethod, $this->getDpdCarrierCode()) !== false);
    }


    /**
     * Returns true if the magento version is greater or equal to the version passed.
     *
     * @param int $major
     * @param int $minor
     * @param int $revision
     * @param int $patch
     *
     * @return boolean
     */
    public function isMagentoVersionGreaterOrEqualTo($major, $minor, $revision, $patch)
    {
        $versionInfo = Mage::getVersionInfo();
        if ((int)$major > (int)$versionInfo['major']) {
            return false;
        } elseif ((int)$minor > (int)$versionInfo['minor']) {
            return false;
        } elseif ((int)$revision > (int)$versionInfo['revision']) {
            return false;
        } elseif ((int)$patch > (int)$versionInfo['patch']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @param string $message
     */
    public function addNotice($message)
    {
        Mage::getSingleton('core/session')->addNotice($message);
    }

    /**
     *
     * @param string $message
     */
    public function addError($message)
    {
        Mage::getSingleton('core/session')->addError($message);
    }

    /**
     *
     * @param string $message
     */
    public function addSuccess($message)
    {
        Mage::getSingleton('core/session')->addSuccess($message);
    }

    /**
     *
     * @param boolean $success
     * @param string  $message
     *
     * @return \Zitec_Dpd_Helper_Data
     */
    public function addSuccessError($success, $message)
    {
        if ($success) {
            $this->addSuccess($message);
        } else {
            $this->addError($message);
        }

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }

    /**
     *
     * @param string $shipmentId
     *
     * @return boolean
     * @deprecated
     */
    public function isManifestClosed($shipmentId)
    {
        $shipsCollection = Mage::getResourceModel('zitec_dpd/dpd_ship_collection');
        /* @var $shipsCollection Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection */
        $ships = $shipsCollection->getByShipmentId($shipmentId);

        /* @var $ships Zitec_Dpd_Model_Dpd_Ship */

        return $ships->getManifest() ? true : false;

    }

    /**
     *
     * @param Mage_Sales_Model_Order_Shipment_Track $track
     *
     * @return boolean
     */
    public function isDpdTrack(Mage_Sales_Model_Order_Shipment_Track $track)
    {
        return strpos($track->getCarrierCode(), $this->getDpdCarrierCode()) !== false;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     */
    public function isCancelledWithDpd(Mage_Sales_Model_Order_Shipment $shipment)
    {
        return $this->isShippingMethodDpd($shipment->getOrder()->getShippingMethod()) && !$shipment->getShippingLabel();
    }


    /**
     *
     * @param string $shippingMethod
     *
     * @return string|boolean
     */
    public function getDPDServiceCode($shippingMethod)
    {
        $parts = explode('_', $shippingMethod);
        if (count($parts) == 2) {
            return $parts[1];
        } else {
            return false;
        }
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return boolean
     */
    public function isOrderCashOnDelivery(Mage_Sales_Model_Order $order)
    {
        return $order->getPayment()->getMethod() == Mage::helper('zitec_dpd')->getDpdPaymentCode() ? true : false;
    }

    /**
     * return default surcharge in checkout if no table rates defined
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $shippingAmount
     *
     * @return mixed
     */
    public function returnDefaultBaseCashOnDeliverySurcharge(Mage_Sales_Model_Quote $quote)
    {
        $amountType = Mage::getStoreConfig('payment/zitec_dpd_cashondelivery/payment_amount_type', $quote->getStoreId());
        $amount     = Mage::getStoreConfig('payment/zitec_dpd_cashondelivery/payment_amount', $quote->getStoreId());
        if ($amountType == Zitec_Dpd_Api_Configs::PAYMENT_AMOUNT_TYPE_FIXED) {
            return $amount;
        } else {
            $address   = $quote->getShippingAddress();
            $taxConfig = Mage::getSingleton('tax/config');
            /* @var $taxConfig Mage_Tax_Model_Config */

            $amount = $amount / 100;

            return $amount * ($this->getBaseValueOfShippableGoods($quote));
        }

    }

    /**
     * here is calculated the price of the quote payment method: cash on delivery using DPD
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $surcharge
     *
     * @return float
     */
    public function calculateQuoteBaseCashOnDeliverySurcharge(Mage_Sales_Model_Quote $quote, $surcharge)
    {
        $address   = $quote->getShippingAddress();
        $taxConfig = Mage::getSingleton('tax/config');
        /* @var $taxConfig Mage_Tax_Model_Config */

        if (!$surcharge || !is_array($surcharge) || !isset($surcharge['cashondelivery_surcharge'])) {
            return 0;
        }
        $baseCashondeliverySurchargePercent = $this->parsePercentageValueAsFraction($surcharge['cashondelivery_surcharge']);
        if ($baseCashondeliverySurchargePercent !== false) {

            $baseCashondeliverySurcharge = $baseCashondeliverySurchargePercent * ($this->getBaseValueOfShippableGoods($quote));
            if (isset($surcharge['cod_min_surcharge'])) {
                $baseCashondeliverySurcharge = max(array((float)$surcharge['cod_min_surcharge'], $baseCashondeliverySurcharge));
            }
        } else {
            $baseCashondeliverySurcharge = (float)$surcharge['cashondelivery_surcharge'];
        }

        return $baseCashondeliverySurcharge;
    }

    /**
     * Parse a string of the form nn.nn% and returns the percent as a fraction.
     * It returns false if the string does not have the correct form.
     *
     * @param type $value
     *
     * @return boolean
     */
    public function parsePercentageValueAsFraction($value)
    {
        if (!is_string($value)) {
            return false;
        }
        $value = trim($value);
        if (strlen($value) < 2 || substr($value, -1) != '%') {
            return false;
        }
        $percentage = $this->parseDecimalValue(substr($value, 0, strlen($value) - 1));
        if ($percentage === false) {
            return false;
        }

        return $percentage / 100;
    }


    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     *
     * @return bool|float
     */
    public function parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (float)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }

        return $value;
    }


    /**
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return float
     */
    public function getBaseValueOfShippableGoods(Mage_Sales_Model_Quote $quote)
    {
        $baseTotalPrice = 0.0;
        $taxConfig      = Mage::getSingleton('tax/config');
        /* @var $taxConfig Mage_Tax_Model_Config */
        if ($quote->getAllItems()) {
            foreach ($quote->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Quote_Item */

                if ($item->getProduct()->isVirtual() || $item->getParentItemId()) {
                    continue;
                }

                $baseTotalPrice += $taxConfig->shippingPriceIncludesTax($quote->getStore()) ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal();
            }
        }

        return $baseTotalPrice;
    }


    /**
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getCodPaymentType(Mage_Sales_Model_Order $order)
    {
        return $order->getPayment()->getMethodInstance()->getConfigData("cod_payment_type", $order->getStoreId());
    }


    /**
     *
     * @param int $manifestId
     *
     * @return string
     */
    public function getDownloadManifestUrl($manifestId)
    {
        $helper = Mage::helper('adminhtml');

        /* @var $helper Mage_Adminhtml_Helper_Data */

        return $helper->getUrl("zitec_dpd/adminhtml_shipment/downloadmanifest", array("manifest_id" => $manifestId));
    }


    /**
     * @return string
     */
    public function getDpdCarrierCode()
    {
        return Zitec_Dpd_Model_Shipping_Carrier_Dpd::CARRIER_CODE;
    }


    /**
     * @return string
     */
    public function getDpdPaymentCode()
    {
        return Zitec_Dpd_Model_Payment_Cashondelivery::CODE;
    }


}