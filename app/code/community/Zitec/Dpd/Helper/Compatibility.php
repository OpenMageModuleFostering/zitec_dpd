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
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Helper_Compatibility extends Mage_Core_Helper_Abstract
{


    /**
     * Checks the possibility of creating shipping label by current carrier
     *
     * compatibility with magento less then 1.6.2
     * @param $shipment
     */
    public function canCreateShippingLabel($shipment){
        $shippingCarrier = $this->getOrder($shipment)->getShippingCarrier();
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }


    /**
     * checko compatibility for print shipping label pdf in Mage_Adminhtml_Sales_Order_ShipmentController
     * compatibility with magento less then 1.6.2
     *
     * @return bool
     */
    public function checkMassPrintShippingLabelExists(){
        if($this->versionIsAtLeast16()){
            return true;
        }

        return false;
    }


    public function versionIsAtLeast16()
    {
        $versionInfo = Mage::getVersionInfo();
        if ($versionInfo['major'] >= 1) {
            if ($versionInfo['minor'] >= 6) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve invoice order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($shipment)
    {
        return $shipment->getOrder();
    }

}