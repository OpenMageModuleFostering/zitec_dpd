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
class Zitec_TableRates_Block_Adminhtml_Tablerate_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId   = 'tablerate_id';
        $this->_blockGroup = 'zitec_tablerates';
        $this->_controller = 'adminhtml_tablerate';
        $this->_mode       = 'import';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('zitec_tablerates')->__('Import'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        return $this->_getHelper()->__('Import') . ' ' . $this->_getHelper()->getGridTitle();
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }

    /**
     *
     * @return Zitec_TableRates_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_tablerates');
    }


}

