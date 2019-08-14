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
class Zitec_Dpd_Block_Adminhtml_Sales_Order_Shipment_View_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
{

    /**
     * check if the carrier code is one of DPD
     *
     * @return boolean
     */
    public function isDPD()
    {
        $shippingMethod = $this->getShipment()->getOrder()->getShippingMethod();

        return $this->_getHelper()->isShippingMethodDpd($shippingMethod);
    }


    public function getShipInfo()
    {
        return '';
    }


    /**
     *
     * @param type $track
     *
     * @return string
     */
    public function getRemoveUrl($track)
    {
        if ($this->isDpdTrack($track)) {
            Mage::helper('adminhtml')->getUrl("zitec_dpd/adminhtml_shipment/manifest", array("shipment_ids" => $this->getShipment()->getId()));

            return $this->getUrl('zitec_dpd/adminhtml_shipment/delete/', array(
                'shipment_id' => $this->getShipment()->getId(),
                'track_id'    => $track->getId()
            ));
        } else {
            return parent::getRemoveUrl($track);
        }
    }


    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }

    public function isDpdTrack(Mage_Sales_Model_Order_Shipment_Track $track)
    {
        return $this->_getHelper()->isDpdTrack($track);
    }

}