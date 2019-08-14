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
class Zitec_Dpd_Model_Sales_Order_Invoice_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setData('zitec_dpd_cashondelivery_surcharge', 0);
        $invoice->setData('base_zitec_dpd_cashondelivery_surcharge', 0);
        $invoice->setData('zitec_dpd_cashondelivery_surcharge_tax', 0);
        $invoice->setData('base_zitec_dpd_cashondelivery_surcharge_tax', 0);

        $order  = $invoice->getOrder();
        $amount = $order->getData('zitec_dpd_cashondelivery_surcharge');
        if ($amount) {
            // We look at the bills to see if it has already claimed the COD surcharge.
            foreach ($order->getInvoiceCollection() as $previousInvoice) {
                /* @var $previousInvoice Mage_Sales_Model_Order_Invoice */
                if (!$previousInvoice->isCanceled() && $previousInvoice->getData('base_zitec_dpd_cashondelivery_surcharge')) {
                    return $this;
                }
            }
            $invoice->setData('zitec_dpd_cashondelivery_surcharge', $amount);

            $baseAmount = $order->getData('base_zitec_dpd_cashondelivery_surcharge');
            $invoice->setData('base_zitec_dpd_cashondelivery_surcharge', $baseAmount);


            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $amount);

            // NB. We do not add taxes grand total here.
            // Are added Zitec_Dpd_Model_Sales_Order_Invoice_Total_Tax.

        }

        return $this;
    }

}