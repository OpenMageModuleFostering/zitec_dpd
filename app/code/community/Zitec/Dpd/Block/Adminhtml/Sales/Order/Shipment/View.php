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
class Zitec_Dpd_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{


    public function __construct()
    {

        parent::__construct();

        if (!$this->_getHelper()->moduleIsActive()) {
            return;
        }

        if (!$this->_getHelper()->isShippingMethodDpd($this->getShipment()->getOrder()->getShippingMethod()) || $this->_getHelper()->isCancelledWithDpd($this->getShipment())) {
            return;
        }


        $isManifestClosed = $this->_getHelper()->isManifestClosed($this->getShipment()->getId());
        if ($isManifestClosed) {
            $onClick = 'setLocation(\'' . $this->_getManifestUrl() . '\')';
        } else {
            $onClick = "deleteConfirm('"
                . $this->_getHelper()->__('Once the manifest is closed, you will not be able to make further changes to the shipping address. Do you want to continue?')
                . "', '" . $this->_getManifestUrl() . "')";
        }

        $this->_addButton('closemanifest', array(
            'label'      => $isManifestClosed ? $this->_getHelper()->__('Print Manifest') : $this->_getHelper()->__('Close Manifest'),
            'class'      => 'save',
            'onclick'    => $onClick,
            'sort_order' => -10
        ));
    }


    protected function _getManifestUrl()
    {
        return Mage::helper('adminhtml')->getUrl("zitec_dpd/adminhtml_shipment/manifest", array("shipment_ids" => $this->getShipment()->getId()));
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