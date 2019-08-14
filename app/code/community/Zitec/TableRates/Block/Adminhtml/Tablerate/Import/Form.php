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
class Zitec_TableRates_Block_Adminhtml_Tablerate_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var array
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/importrates', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), 'carrier' => $this->_getHelper()->getCarrierCode())),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);


        $fieldset = $form->addFieldset('base_fieldset', array());

        $fieldset->addField('website_id', 'select', array(
            'name'     => 'website_id',
            'label'    => $this->_getHelper()->__('Website'),
            'values'   => Mage::getSingleton('zitec_tablerates/source_website')->toOptionArray(),
            'required' => true
        ));

        $fieldset->addField('import', 'file', array(
            'name'     => 'import',
            'label'    => $this->_getHelper()->__('Import Rates'),
            'required' => true,

        ));


        $form->setUseContainer(true);

        return parent::_prepareForm();
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

