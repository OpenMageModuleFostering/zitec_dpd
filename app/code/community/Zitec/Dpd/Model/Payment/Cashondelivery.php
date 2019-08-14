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
class Zitec_Dpd_Model_Payment_Cashondelivery extends Mage_Payment_Model_Method_Abstract
{

    const CODE = 'zitec_dpd_cashondelivery';

    protected $_code = 'zitec_dpd_cashondelivery';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    protected $_surcharge = null;
    protected $_order = null;

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        $order = $this->fetchOrder();
        $title = '';
        $store = null;
        if ($order) {
            $title = $this->getConfigData('title', $order->getStoreId());
            $store = $order->getStoreId();
        } else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($quote) {
                $title = $this->getConfigData('title', $quote->getStoreId());
                $store = $quote->getStoreId();
            }
        }
        $surcharge = $this->getSurchage();
        if ($surcharge > 0) {
            $title     = $title . ' (+ ' . Mage::helper('core')->formatCurrency($surcharge, false);
            $taxConfig = Mage::getSingleton('tax/config');
            /* @var $taxConfig Mage_Tax_Model_Config */
            if ($taxConfig->shippingPriceIncludesTax($store)) {
                $title .= $this->_getHelper()->__(' incl. tax');
            } else {
                $title .= $this->_getHelper()->__(' excl. tax');
            }
            $title .= ') ';
        }

        return $title;
    }

    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $checkResult       = new StdClass;
        $isActive          = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        $shippingMethodRaw = $quote->getShippingAddress()->getShippingMethod();
        $product           = $this->_getHelper()->getDPDServiceCode($shippingMethodRaw);

        if (!$isActive ||
            !$quote ||
            !$this->_getHelper()->isShippingMethodDpd($shippingMethodRaw) ||
            !in_array($product, explode(',', $this->getConfigData('specificproducto', $quote->getStoreId()))) ||
            is_null($this->getSurchage())
        ) {
            $isActive = false;
        }

        $checkResult->isAvailable      = $isActive;
        $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
        Mage::dispatchEvent('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this,
            'quote'           => $quote,
        ));

        // disable method if it cannot implement recurring profiles management and there are recurring items in quote
        if ($checkResult->isAvailable) {
            $implementsRecurring = $this->canManageRecurringProfiles();
            // the $quote->hasRecurringItems() causes big performance impact, thus it has to be called last
            if ($quote && !$implementsRecurring && $quote->hasRecurringItems()) {
                $checkResult->isAvailable = false;
            }
        }

        return $checkResult->isAvailable;
    }

    public function getSurchage()
    {
        if ($this->_surcharge === null) {
            $order = $this->fetchOrder();
            if ($order) {
                $this->_surcharge = $order->getData('base_zitec_dpd_cashondelivery_surcharge');
            } else {
                $quote = Mage::helper('zitec_dpd/payment')->getQuote();
                /* @var $quote Mage_Sales_Model_Quote */
                if ($quote) {
                    $shippingAddress = $quote->getShippingAddress();

                    $request = new Varien_Object();

                    $request->setWebsiteId(Mage::helper('zitec_dpd/payment')->getWebsiteId());
                    $request->setDestCountryId($shippingAddress->getCountryId());
                    $request->setDestRegionId($shippingAddress->getRegionId());
                    $request->setDestPostcode($shippingAddress->getPostcode());
                    $request->setPackageWeight($shippingAddress->getWeight());
                    if ($this->_getTaxHelper()->shippingPriceIncludesTax($quote->getStoreId())) {
                        $request->setData('zitec_table_price', $shippingAddress->getBaseSubtotalInclTax());
                    } else {
                        $request->setData('zitec_table_price', $shippingAddress->getBaseSubtotal());
                    }
                    $request->setMethod(str_replace(Mage::helper('zitec_dpd')->getDPDCarrierCode() . '_', '', $shippingAddress->getShippingMethod()));
                    $tablerateSurcharge = Mage::getResourceModel('zitec_dpd/carrier_tablerate')->getCashOnDeliverySurcharge($request);

                    if (is_null($tablerateSurcharge) || (is_array($tablerateSurcharge)&& is_null($tablerateSurcharge['cashondelivery_surcharge']))) {
                        return null;
                    } elseif (!empty($tablerateSurcharge)) {
                        $this->_surcharge = $this->_getHelper()->calculateQuoteBaseCashOnDeliverySurcharge($quote, $tablerateSurcharge);
                    } else {
                        $this->_surcharge = $this->_getHelper()->returnDefaultBaseCashOnDeliverySurcharge($quote);
                    }
                }
            }
        }

        return $this->_surcharge;
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
     * @return Mage_Core_Model_Abstract|mixed|null
     */
    public function fetchOrder()
    {
        if (is_null($this->_order)) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->_order = Mage::registry('current_order');
                if (!$this->_order && Mage::app()->getRequest()->getParam('order_id')) {
                    $this->_order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
                }
            } else {
                $order_id = Mage::app()->getRequest()->getParam('order_id');
                if ($order_id) {
                    $this->_order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
                }
            }
        }

        return $this->_order;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        $allowedCountries = Zitec_Dpd_Model_Payment_Cashondelivery_Source_Country::getAllAllowedCountries();
        $canUseForCountry = parent::canUseForCountry($country) && (!$allowedCountries || in_array($country, $allowedCountries));

        return $canUseForCountry ? true : false;
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

