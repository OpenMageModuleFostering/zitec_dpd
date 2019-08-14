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
class Zitec_Dpd_Block_Order_Invoice_Totals_Cashondeliverysurcharge extends Zitec_Dpd_Block_Order_Totals_Cashondeliverysurchage
{
    /**
     *
     * @return float
     */
    protected function _getAmount()
    {
        return $this->_getInvoice()->getData('zitec_dpd_cashondelivery_surcharge');
    }

    /**
     *
     * @return float
     */
    protected function _getBaseAmount()
    {
        return $this->_getInvoice()->getData('base_zitec_dpd_cashondelivery_surcharge');
    }

    /**
     *
     * @return type
     */
    protected function _getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     *
     * @return float
     */
    protected function _getTax()
    {
        return $this->_getInvoice()->getData('zitec_dpd_cashondelivery_surcharge_tax');
    }

    /**
     *
     * @return float
     */
    protected function _getBaseTax()
    {
        return $this->_getInvoice()->getData('base_zitec_dpd_cashondelivery_surcharge_tax');
    }
}


