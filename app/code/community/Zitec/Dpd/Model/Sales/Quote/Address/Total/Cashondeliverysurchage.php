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
class Zitec_Dpd_Model_Sales_Quote_Address_Total_Cashondeliverysurchage extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('zitec_dpd_cashondelivery_surcharge');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setData('zitec_dpd_cashondelivery_surcharge', 0);
        $address->setData('base_zitec_dpd_cashondelivery_surcharge', 0);
        $address->setData('zitec_dpd_cashondelivery_surcharge_tax', 0);
        $address->setData('base_zitec_dpd_cashondelivery_surcharge_tax', 0);

        $paymentMethod = $address->getQuote()->getPayment()->getMethod();

        if ($paymentMethod == Mage::helper('zitec_dpd')->getDpdPaymentCode() && $address->getAddressType() == 'shipping') {
            $quote = $address->getQuote();
            /* @var $quote Mage_Sales_Model_Quote */
            $shippingAddress = $quote->getShippingAddress();

            $request = new Varien_Object();
            $request->setWebsiteId(Mage::helper('zitec_dpd/payment')->getWebsiteId());
            $request->setDestCountryId($shippingAddress->getCountryId());
            $request->setDestRegionId($shippingAddress->getRegionId());
            $request->setDestPostcode($shippingAddress->getPostcode());
            $request->setPackageWeight($shippingAddress->getWeight());
            if ($this->_getTaxHelper()->shippingPriceIncludesTax($address->getQuote()->getStoreId())) {
                $request->setData('zitec_table_price', $shippingAddress->getBaseSubtotalInclTax());
            } else {
                $request->setData('zitec_table_price', $shippingAddress->getBaseSubtotal());
            }
            $request->setMethod(str_replace(Mage::helper('zitec_dpd')->getDPDCarrierCode() . '_', '', $shippingAddress->getShippingMethod()));
            $tablerateSurcharge = Mage::getResourceModel('zitec_dpd/carrier_tablerate')->getCashOnDeliverySurcharge($request);

            if (is_null($tablerateSurcharge)) {
                return $this;
            } elseif (!empty($tablerateSurcharge)) {
                $baseCashondeliverySurcharge = $this->_getHelper()->calculateQuoteBaseCashOnDeliverySurcharge($quote, $tablerateSurcharge);
            } else {
                $baseCashondeliverySurcharge = $this->_getHelper()->returnDefaultBaseCashOnDeliverySurcharge($quote);
            }

            if (!isset($baseCashondeliverySurcharge)) {
                return $this;
            }

            $baseCurrencyCode        = $quote->getStore()->getBaseCurrencyCode();
            $currentCurrencyCode     = $quote->getStore()->getCurrentCurrencyCode();
            $cashondeliverySurcharge = Mage::helper('directory')->currencyConvert($baseCashondeliverySurcharge, $baseCurrencyCode, $currentCurrencyCode);
            $address->setData('zitec_dpd_cashondelivery_surcharge', $cashondeliverySurcharge);
            $address->setData('base_zitec_dpd_cashondelivery_surcharge', $baseCashondeliverySurcharge);
            $this->_calculateSurchargeSalesTax($address);
            $quote->save();
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getData('zitec_dpd_cashondelivery_surcharge'));
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getData('base_zitec_dpd_cashondelivery_surcharge'));

        return $this;
    }

    /**
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return \Zitec_Dpd_Model_Sales_Quote_Address_Total_Cashondeliverysurchage
     */
    protected function _calculateSurchargeSalesTax(Mage_Sales_Model_Quote_Address $address)
    {

        $taxCalculator = Mage::getSingleton('tax/calculation');
        /* @var $taxCalculator Mage_Tax_Model_Calculation */
        $customer = $address->getQuote()->getCustomer();
        if ($customer) {
            $taxCalculator->setCustomer($customer);
        }

        $store     = $address->getQuote()->getStore();
        $request   = $taxCalculator->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );
        $taxConfig = Mage::getSingleton('tax/config');
        /* @var $taxConfig Mage_Tax_Model_Config */
        $request->setProductClassId($taxConfig->getShippingTaxClass($store));

        $rate          = $taxCalculator->getRate($request);
        $inclTax       = $taxConfig->shippingPriceIncludesTax($store);
        $surcharge     = $address->getData('zitec_dpd_cashondelivery_surcharge');
        $baseSurcharge = $address->getData('base_zitec_dpd_cashondelivery_surcharge');

        // NOTA: Mira el comentario de 25 abr 2013 10:45 en #43 de collab.
        $surchargeTax     = $taxCalculator->calcTaxAmount($surcharge, $rate, $inclTax, true);
        $baseSurchargeTax = $taxCalculator->calcTaxAmount($baseSurcharge, $rate, $inclTax, true);

        $address->setExtraTaxAmount($address->getExtraTaxAmount() + $surchargeTax);
        $address->setBaseExtraTaxAmount($address->getBaseExtraTaxAmount() + $baseSurchargeTax);

        $address->setData('zitec_dpd_cashondelivery_surcharge_tax', $surchargeTax);
        $address->setData('base_zitec_dpd_cashondelivery_surcharge_tax', $baseSurchargeTax);

        if ($inclTax) {
            $address->setData('zitec_dpd_cashondelivery_surcharge', $surcharge - $surchargeTax);
            $address->setData('base_zitec_dpd_cashondelivery_surcharge', $baseSurcharge - $baseSurchargeTax);
        }

        return $this;
    }

    /**
     *
     * @return Mage_Tax_Helper_Data
     */
    protected function _getTaxHelper()
    {
        return Mage::helper('tax');
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getData('zitec_dpd_cashondelivery_surcharge');

        if ($amount != 0 && $address->getAddressType() == 'shipping') {
            $title = Mage::getStoreConfig('payment/zitec_dpd_cashondelivery/total_title', $address->getQuote()->getStore());

            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        $title = Mage::getStoreConfig('payment/zitec_dpd_cashondelivery/total_title');

        return $title;
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