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
class Zitec_Dpd_Model_Observer_Manifest
{

    public function addManifestMassAction(Varien_Event_Observer $observer)
    {
        if (!$this->_getHelper()->moduleIsActive()) {
            return;
        }
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
            && (($block->getRequest()->getControllerName() == 'sales_shipment'))
        ) {
            $block->addItem('print_dpd_manifest', array(
                'label'   => $this->_getHelper()->__('Close DPD Manifest'),
                'url'     => Mage::helper("adminhtml")->getUrl('zitec_dpd/adminhtml_shipment/manifest'),
                'confirm' => $this->_getHelper()->__('Once the manifest is closed for the selected shipments, you will not be able to make further changes to their shipping addresses. Do you want to continue?')
            ));
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
}


