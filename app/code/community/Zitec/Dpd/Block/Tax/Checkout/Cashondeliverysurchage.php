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
class Zitec_Dpd_Block_Tax_Checkout_Cashondeliverysurchage extends Mage_Checkout_Block_Total_Default
{

    protected $_template = 'zitec_dpd/tax/checkout/cashondeliverysurchage.phtml';

    /**
     *
     * @return float
     */
    protected function _getCashOnDeliverySurcharge()
    {
        return $this->getTotal()->getAddress()->getData('zitec_dpd_cashondelivery_surcharge');
    }

    /**
     *
     * @return float
     */
    protected function _getCashOnDeliverySurchargeTax()
    {
        return $this->getTotal()->getAddress()->getData('zitec_dpd_cashondelivery_surcharge_tax');
    }


    /**
     * Check if we need display shipping include and exclude tax
     *
     * @return bool
     */
    public function displayBoth()
    {
        return Mage::getSingleton('tax/config')->displayCartShippingBoth($this->getStore());
    }

    /**
     * Check if we need display shipping include tax
     *
     * @return bool
     */
    public function displayIncludeTax()
    {
        return Mage::getSingleton('tax/config')->displayCartShippingInclTax($this->getStore());
    }


    /**
     * Get label for cash on delivery surcharge including tax
     *
     * @return float
     */
    public function getIncludeTaxLabel()
    {
        return $this->_getTitle() . $this->_getHelper()->__(' Incl. tax');
    }

    /**
     * Get label for cash on delivery surcharge excluding tax
     *
     * @return float
     */
    public function getExcludeTaxLabel()
    {
        return $this->_getTitle() . $this->_getHelper()->__(' Excl. tax');
    }

    /**
     *
     * @return float
     */
    public function getCashOnDeliverySurchargeExcludeTax()
    {
        return $this->_getCashOnDeliverySurcharge();
    }

    /**
     *
     * @return float
     */
    public function getCashOnDeliverySurchargeIncludeTax()
    {
        return $this->_getCashOnDeliverySurcharge() + $this->_getCashOnDeliverySurchargeTax();
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
     * @return string
     */
    protected function _getTitle()
    {
        return $this->getTotal()->getTitle();
    }


}