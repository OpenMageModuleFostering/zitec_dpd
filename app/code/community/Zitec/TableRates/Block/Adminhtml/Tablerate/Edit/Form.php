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
class Zitec_TableRates_Block_Adminhtml_Tablerate_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
            'action'  => $this->getUrl('*/*/save', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), 'carrier' => $this->_getHelper()->getCarrierCode())),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);
        $model = Mage::registry('tablerate_data');
        /* @var $model Zitec_TableRates_Model_Tablerate */

        $fieldset = $form->addFieldset('base_fieldset', array());

        if ($model->getId()) {
            $fieldset->addField('tablerate_id', 'hidden', array(
                'name'  => 'pk',
                'value' => $model->getMappedData('pk'),
            ));
        }

        $fieldset->addField('website_id', 'select', array(
            'name'     => 'website_id',
            'label'    => $this->_getHelper()->__('Website'),
            'required' => true,
            'value'    => $model->getMappedData('website_id'),
            'values'   => Mage::getSingleton('zitec_tablerates/source_website')->toOptionArray()
        ));

        $destCountryId = $model->getMappedData('dest_country_id');
        $fieldset->addField('dest_country_id', 'select', array(
            'name'     => 'dest_country_id',
            'label'    => $this->_getHelper()->__('Dest Country'),
            'required' => false,
            'value'    => $destCountryId,
            'values'   => $this->getCountryValues()
        ));


        $fieldset->addField('dest_zip', 'text', array(
            'name'     => 'dest_zip',
            'label'    => $this->_getHelper()->__('Dest Zip/Postal Code'),
            'note'     => $this->_getHelper()->__('* or blank - matches any'),
            'required' => false,
            'value'    => $model->getMappedData('dest_zip')
        ));

        $fieldset->addField('method', 'select', array(
            'name'     => 'method',
            'label'    => $this->_getHelper()->__('Service'),
            'required' => true,
            'value'    => $model->getMappedData('method'),
            'values'   => $this->_getHelper()->getMethodOptions(),
            'note'     => $this->_getHelper()->__('For rates with this service to be available, you must enable the service from the configuration panel of the shipping method in System->Configuration->Shipping Methods')
        ));

        if ($this->_getHelper()->supportsProduct()) {
            $fieldset->addField('product', 'select', array(
                'name'     => 'product',
                'label'    => $this->_getHelper()->__('Product'),
                'required' => true,
                'value'    => $model->getMappedData('product'),
                'values'   => $this->_getHelper()->getProductOptions(),
                'note'     => $this->_getHelper()->__('For rates with this product to be available, you must enable the product from the configuration panel of the shipping method in System->Configuration->Shipping Methods')
            ));
        }

        if ($this->_getHelper()->supportsPriceVsDest()) {
            $fieldset->addField('price_vs_dest', 'select', array(
                'name'     => 'price_vs_dest',
                'label'    => $this->_getHelper()->__('Condition'),
                'required' => true,
                'value'    => $model->getMappedData('price_vs_dest'),
                'values'   => array('0' => $this->_getHelper()->__("Weight vs. Destination"), '1' => $this->_getHelper()->__('Price vs. Destination')),
            ));
        }


        $fieldset->addField('weight_price', 'text', array(
            'name'     => 'weight_price',
            'label'    => $this->_getHelper()->__('Weight (and above)'),
            'required' => true,
            'class'    => 'validate-number',
            'value'    => $model->getMappedData('weight_price'),
            'note'     => $this->_getHelper()->__("Enter the starting weight in kg for this rate.")
        ));

        $fieldset->addField('shipping_method_enabled', 'select', array(
            'name'     => 'shipping_method_enabled',
            'label'    => $this->_getHelper()->__('Enable Shipping Method'),
            'required' => true,
            'value'    => ($model->getMappedData('price') >= 0 ? '1' : '0'),
            'values'   => array('0' => $this->_getHelper()->__('Disabled'), '1' => $this->_getHelper()->__('Enabled')),
            'note'     => $this->_getHelper()->__('Disable the shipping method if you would like it to be unavailable for orders whose price or weight is greater or equal to the value you have indicated.')
        ));

        if ($this->_getHelper()->supportsMarkup()) {
            $fieldset->addField('markup_type', 'select', array(
                'name'     => 'markup_type',
                'label'    => $this->_getHelper()->__('Shipping Price Calculation'),
                'required' => true,
                'value'    => $model->getMappedData('markup_type'),
                'values'   => array('0' => $this->_getHelper()->__("Fixed Price"), '1' => $this->_getHelper()->__('Add Percentage'), '2' => $this->_getHelper()->__('Add Fixed amount')),
                'note'     => $this->_getHelper()->__("Use 'Add Percentage' if you want to calculate the shipping price by adding a percentage to price charged by the shipping carrier.")
            ));
        }

        $fieldset->addField('price', 'text', array(
            'name'     => 'price',
            'label'    => $this->_getHelper()->__('Shipping Price'),
            'required' => true,
            'value'    => $model->getMappedData('price'),
            'class'    => 'validate-number'
        ));

        if ($this->_getHelper()->supportsCashOnDelivery()) {
            $codOption = $model->getCashOnDeliverySurchargeOption();
            $fieldset->addField('cod_option', 'select', array(
                'name'     => 'cod_option',
                'label'    => $this->_getHelper()->__('Cash On Delivery Surcharge Calculation'),
                'required' => true,
                'value'    => $codOption,
                'values'   => array(
                    Zitec_TableRates_Model_Tablerate::COD_NOT_AVAILABLE        => $this->_getHelper()->__("Cash On Delivery Not Available"),
                    Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_ZERO       => $this->_getHelper()->__("Zero Surcharge"),
                    Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_FIXED      => $this->_getHelper()->__('Fixed Surcharge'),
                    Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_PERCENTAGE => $this->_getHelper()->__('Percentage Surcharge')
                ),
            ));


            $fieldset->addField('cashondelivery_surcharge', 'text', array(
                'name'     => 'cashondelivery_surcharge',
                'label'    => $this->_getHelper()->__('Fixed Cash On Delivery Surcharge'),
                'required' => true,
                'value'    => $codOption == Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_PERCENTAGE ? $model->getData('cod_surcharge_percentage') : $model->getData('cod_surcharge_price'),
                'class'    => 'validate-number',
            ));

            if ($this->_getHelper()->supportsCodMinSurcharge()) {
                $fieldset->addField('cod_min_surcharge', 'text', array(
                    'name'     => 'cod_min_surcharge',
                    'label'    => $this->_getHelper()->__('Minimum COD Surcharge'),
                    'required' => false,
                    'value'    => $model->getMappedData('cod_min_surcharge'),
                    'class'    => 'validate-number',
                    'note'     => $this->_getHelper()->__('Optionally specify the minimum COD surcharge.')
                ));
            }
        }


        $sessionData = $this->_getSessionFormData();
        if (is_array($sessionData)) {
            $form->setValues($sessionData);
            $destRegionId  = array_key_exists('dest_region_id', $sessionData) ? $sessionData['dest_region_id'] : null;
            $destCountryId = array_key_exists('dest_country_id', $sessionData) ? $sessionData['dest_country_id'] : null;
            $this->_clearSessionFormData();
        } else {
            $destRegionId = $model->getMappedData('dest_region_id');
        }

        $fieldset->addField('dest_region_id', 'select', array(
            'name'     => 'dest_region_id',
            'label'    => $this->_getHelper()->__('Dest Region/State'),
            'required' => false,
            'value'    => $destRegionId,
            'values'   => $this->getRegionValues($destCountryId),
        ), 'dest_country_id');

        $form->setUseContainer(true);

        return parent::_prepareForm();
    }

    /**
     *
     * @return array
     */
    protected function _getSessionFormData()
    {
        return Mage::getSingleton('adminhtml/session')->getTablerateData();
    }

    /**
     *
     * @return \Zitec_TableRates_Block_Adminhtml_Tablerate_Edit_Form
     */
    protected function _clearSessionFormData()
    {
        Mage::getSingleton('adminhtml/session')->setTablerateData(null);

        return $this;
    }

    /**
     * Get country values
     *
     * @return array
     */
    protected function getCountryValues()
    {
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0] = array('label' => '*', 'value' => 0);
        }

        return $countries;
    }

    /**
     * Get region values
     *
     * @return array
     */
    protected function getRegionValues($destCountryId = null)
    {
        $regions       = array(array('value' => '', 'label' => '*'));
        $model         = Mage::registry('tablerate_data');
        $destCountryId = isset($destCountryId) ? $destCountryId : $model->getDestCountryId();
        if ($destCountryId) {
            $regionCollection = Mage::getModel('directory/region')
                ->getCollection()
                ->addCountryFilter($destCountryId);
            $regions          = $regionCollection->toOptionArray();
            if (isset($regions[0])) {
                $regions[0]['label'] = '*';
            }
        }

        return $regions;
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