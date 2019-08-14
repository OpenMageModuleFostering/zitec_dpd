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
class Zitec_Dpd_Block_Adminhtml_Postcode_UpdateForm_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * prepare form fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws Exception
     */
    protected function _prepareForm()
    {
        $form     = new Varien_Data_Form(array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/adminhtml_postcode/import', array('id' => $this->getRequest()->getParam('id'))),
                'enctype' => 'multipart/form-data',
                'method'  => 'post',
            )
        );

        $fieldset = $form->addFieldset('csv_upload', array('legend' => 'Upload a CSV to update the postcode database - DPD'));
        $fieldset->addField('csv', 'file',
            array(
                'label' => 'CSV file received from DPD',
                'name'  => 'csv'
            ));

        $fieldset = $form->addFieldset('csv_file_path', array('legend' => ' Import an existing file'));
        $fieldset->addField('path_to_csv', 'text',
            array(
                'label' => 'File name of the CSV found in media/dpd/postcode_update',
                'name'  => 'path_to_csv'
            ));


        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}


