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
class Zitec_Dpd_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Tax
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $result = parent::collect($invoice);
        // We added taxes on delivery surcharge

        $invoice->setData("zitec_dpd_cashondelivery_surcharge_tax", 0);
        $invoice->setData("base_zitec_dpd_cashondelivery_surcharge_tax", 0);
        if ($invoice->getData("zitec_dpd_cashondelivery_surcharge")) {
            $baseTax = $invoice->getOrder()->getData("base_zitec_dpd_cashondelivery_surcharge_tax");
            $invoice->setData("base_zitec_dpd_cashondelivery_surcharge_tax", $baseTax);

            $tax = $invoice->getOrder()->getData("zitec_dpd_cashondelivery_surcharge_tax");
            $invoice->setData("zitec_dpd_cashondelivery_surcharge_tax", $tax);

            // According to the calculations carried out in the parent class, if returned
            // True for "isLast" we will already added taxes
            // (Including the COD to the total tax and the grand total
            // Order). So just in case you return false from
            // The call to "isLast" add COD taxes to total.
            if (!$invoice->isLast()) {
                $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTax);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTax);

                $invoice->setTaxAmount($invoice->getTaxAmount() + $tax);
                $invoice->setGrandTotal($invoice->getGrandTotal() + $tax);
            }
        }

        return $result;

    }
}


