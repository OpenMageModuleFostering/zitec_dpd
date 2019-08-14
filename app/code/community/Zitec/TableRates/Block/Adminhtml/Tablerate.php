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
class Zitec_TableRates_Block_Adminhtml_Tablerate extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller     = 'adminhtml_tablerate';
        $this->_blockGroup     = 'zitec_tablerates';
        $this->_headerText     = $this->_getHelper()->getGridTitle();
        $this->_addButtonLabel = Mage::helper('zitec_tablerates')->__('Add Rate');

        $this->_addButton('zitec_import',
            array(
                'label'   => $this->_getHelper()->__('Import Rates'),
                'onclick' => "setLocation('{$this->getImportUrl()}')"
            ));

        $this->_addButton('zitec_export',
            array(
                'label'   => $this->_getHelper()->__('Export Rates'),
                'onclick' => "setLocation('{$this->getExportUrl()}')"
            ));
        parent::__construct();


    }

    /**
     *
     * @return Zitec_TableRates_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_tablerates');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }

    public function getImportUrl()
    {
        return $this->getUrl('*/*/import', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }

    public function getExportUrl()
    {
        return $this->getUrl('*/*/export', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }


}
