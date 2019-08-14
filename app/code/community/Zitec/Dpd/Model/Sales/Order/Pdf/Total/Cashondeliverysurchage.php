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
class Zitec_Dpd_Model_Sales_Order_Pdf_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Pdf_Total_Default
{

    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->getData('zitec_dpd_cashondelivery_surcharge');
        if (floatval($amount)) {
            if ($this->getAmountPrefix()) {
                $discount = $this->getAmountPrefix() . $discount;
            }

            $title    = Mage::getStoreConfig('payment/zitec_dpd_cashondelivery/total_title', $this->getOrder()->getStoreId());
            $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

            $totals = array();
            if ($this->_displayBoth() || $this->_displayExcludingTax()) {
                $totals[] = array(
                    'label'     => $title . ($this->_displayBoth() ? $this->_getHelper()->__(' (Excl. Tax)') : '') . ':',
                    'amount'    => $this->getOrder()->formatPriceTxt($amount),
                    'font_size' => $fontSize,
                );
            }

            if ($this->_displayBoth() || $this->_displayIncludingTax()) {
                $amount += $this->getOrder()->getData('zitec_dpd_cashondelivery_surcharge_tax');
                $totals[] = array(
                    'label'     => $title . ($this->_displayBoth() ? $this->_getHelper()->__(' (Incl. Tax)') : '') . ':',
                    'amount'    => $this->getOrder()->formatPriceTxt($amount),
                    'font_size' => $fontSize,
                );
            }

            return $totals;
        }
    }

    /**
     *
     * @return boolean
     */
    protected function _displayBoth()
    {
        return $this->_getConfig()->displaySalesShippingBoth($this->_getStore());
    }

    /**
     *
     * @return boolean
     */
    protected function _displayIncludingTax()
    {
        return $this->_getConfig()->displaySalesShippingInclTax($this->_getStore());
    }

    /**
     *
     * @return boolean
     */
    protected function _displayExcludingTax()
    {
        return $this->_getConfig()->displaySalesShippingExclTax($this->_getStore());
    }

    /**
     *
     * @return Mage_Tax_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('tax/config');
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
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return $this->getOrder()->getStore();
    }

}