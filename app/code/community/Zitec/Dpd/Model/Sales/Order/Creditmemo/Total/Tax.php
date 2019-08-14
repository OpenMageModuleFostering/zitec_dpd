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
class Zitec_Dpd_Model_Sales_Order_Creditmemo_Total_Tax extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

        $result = parent::collect($creditmemo);

        // We added taxes on delivery surcharge
        $creditmemo->setData("zitec_dpd_cashondelivery_surcharge_tax", 0);
        $creditmemo->setData("base_zitec_dpd_cashondelivery_surcharge_tax", 0);
        if ($creditmemo->getData("zitec_dpd_cashondelivery_surcharge")) {
            $baseTax = $creditmemo->getOrder()->getData("base_zitec_dpd_cashondelivery_surcharge_tax");
            $creditmemo->setData("base_zitec_dpd_cashondelivery_surcharge_tax", $baseTax);
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTax);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTax);

            $tax = $creditmemo->getOrder()->getData("zitec_dpd_cashondelivery_surcharge_tax");
            $creditmemo->setData("zitec_dpd_cashondelivery_surcharge_tax", $tax);
            $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $tax);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $tax);
        }

        return $result;
    }
}


