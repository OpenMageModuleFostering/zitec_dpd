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
class Zitec_TableRates_Block_Adminhtml_Tablerate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     *
     * @var array
     */
    public function __construct()
    {
        $this->_objectId   = 'tablerate_id';
        $this->_blockGroup = 'zitec_tablerates';
        $this->_controller = 'adminhtml_tablerate';
        $model             = Mage::registry('tablerate_data');
        /* @var $model Zitec_TableRates_Model_Tablerate */
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('zitec_tablerates')->__('Save'));
        if ($model->getId()) {
            $this->_updateButton('delete', 'label', Mage::helper('zitec_tablerates')->__('Delete'));

            $this->_addButton('duplicate', array(
                'label'   => Mage::helper('zitec_tablerates')->__('Duplicate'),
                'class'   => 'add',
                'onclick' => "zitecDpdDuplicate()",

            ));
        } else {
            $this->_removeButton('delete');
        }

        $json = Mage::helper('zitec_tablerates/directory')->getRegionJson2();

        $this->_formScripts[] = "function zitecDpdDuplicate() {
            if ($('tablerate_id') && $('edit_form')) {
                $('edit_form').action = $('edit_form').action + 'duplicate/1';
                editForm.submit();
            }
            
            
            }";
        $this->_formScripts[] = "var updater = new RegionUpdater('{$model->getMappedName('dest_country_id')}', 'none', '{$model->getMappedName('dest_region_id')}', $json, 'disable'); ";
        $this->_formScripts[] = "
(function() {
    var WEIGHT_AND_ABOVE_LABEL = '{$this->_getHelper()->__("Weight (and above)")}';
    var PRICE_AND_ABOVE_LABEL = '{$this->_getHelper()->__("Price (and above)")}';
    
    var SHIPPING_PRICE_LABEL = '{$this->_getHelper()->__("Shipping Price")}';
    var SHIPPING_PERCENTAGE_LABEL = '{$this->_getHelper()->__("Shipping Percentage")}';
    var SHIPPING_FIXED_AMOUNT_LABEL = '{$this->_getHelper()->__("Add fixed amount to price")}';

    var COD_SURCHARGE_FIXED_LABEL = '{$this->_getHelper()->__("Fixed Cash On Delivery Surcharge Amount")}';
    var COD_SURCHARGE_PERCENTAGE_LABEL = '{$this->_getHelper()->__("Cash On Delivery Surcharge Percentage")}';
    
    var COD_MIN_SURCHARGE_LABEL = '{$this->_getHelper()->__("Minimum COD Surcharge")}';
    
    var PRICE_AND_ABOVE_NOTE = '{$this->_getHelper()->__("Enter the starting price for this rate in the base currency of website. This rate will apply to orders whose subtotal (excluding shipping) is greater or equal to this price. Only include the sales tax/VAT in this price if you have configured shipping prices to include it (see System->Configuration->Sales->Tax->Calulation Settings->Shipping Prices).")}';
    var WEIGHT_AND_ABOVE_NOTE = '{$this->_getHelper()->__("Enter the starting weight in kg for this rate.")}';
    
    var selectorEventHandlers = [];
    
    return {
        
        
        init: function() {
            document.observe('dom:loaded', function() {
                this.setLabelDependingOnSelect({
                    selector: 'price_vs_dest',
                    labelFor: 'weight_price',
                    values: {
                        '0': WEIGHT_AND_ABOVE_LABEL,
                        '1': PRICE_AND_ABOVE_LABEL
                    },
                    notes: {
                        '0': WEIGHT_AND_ABOVE_NOTE,
                        '1': PRICE_AND_ABOVE_NOTE
                    },
                    isRequired: true,
                    validation: ['validate-number']
                });
    
                this.setLabelDependingOnSelect({
                    selector: 'markup_type',
                    labelFor: 'price',
                    values: {
                        '0': SHIPPING_PRICE_LABEL,
                        '1': SHIPPING_PERCENTAGE_LABEL,
                        '2': SHIPPING_FIXED_AMOUNT_LABEL
                    },
                    isRequired: true,
                    validation: ['validate-number']
                });

                this.setLabelDependingOnSelect({
                    selector: 'cod_option',
                    labelFor: 'cashondelivery_surcharge',
                    values: {
                        '0': null,
                        '1': null,
                        '2': COD_SURCHARGE_FIXED_LABEL,
                        '3': COD_SURCHARGE_PERCENTAGE_LABEL
                    },
                    isRequired: true,
                    validation: ['validate-number']
                });
    
                this.setLabelDependingOnSelect({
                    selector: 'cod_option',
                    labelFor: 'cod_min_surcharge',
                    values: {
                        '0': null,
                        '1': null,
                        '2': null,
                        '3': COD_MIN_SURCHARGE_LABEL
                    },
                    isRequired: false,
                    validation: ['validate-number']
                });    
                
                this.hideFieldsDependingOnSelect('shipping_method_enabled', 
                    ['0'], 
                    [ { 'id': 'markup_type', 'isRequired' : true, validation: []},
                      { 'id': 'price', 'isRequired' : true, validation: ['validate-number'], 'onShow': this.blankPricePercentage},
                      { 'id': 'cod_option', 'isRequired' : true, validation: []},
                      { 'id': 'cashondelivery_surcharge', 'isRequired' : true, validation: ['validate-number']},
                      { 'id': 'cod_min_surcharge', 'isRequired' : false, validation: ['validate-number']}
    
                    ]

                );
            }.bind(this));
            
        },
   
        blankPricePercentage: function() {
            var value = $('price').getValue();
            if (!isNaN(parseFloat(value)) && isFinite(value) && value < 0) {
                $('price').setValue('');
            }
        },
    
        setLabelDependingOnSelect: function(options) {
            var element = $(options.labelFor),
                elementRow = this.getElementRow(options.labelFor),
                label = this.getLabelForId(options.labelFor),
                select = $(options.selector),
                note = this.getNoteForId(options.labelFor),
                labelText = null,
                handler = null;
    
            if (!element || !label || !select) {
                return false;
            }
            
            handler = function() {
                var i = 0,
                    validatorsCount = options.validation.length;
    
                labelText = options.values[select.getValue()];
                if (labelText) {
                    if (options.isRequired) {
                        labelText += ' <span class=\"required\">*</span>';
                        element.addClassName('required-entry');
                    }
                    for (i = 0; i < validatorsCount; i += 1) {
                        element.addClassName(options.validation[i]);
                    }    
                    label.innerHTML = labelText;
                    if (note && options.notes && options.notes[select.getValue()]) {
                        note.innerHTML = options.notes[select.getValue()];
                    }
                    if (elementRow) {
                        elementRow.show();
                    }
                    element.show();
                    label.show();
                } else  {
                    if (elementRow) {
                        elementRow.hide();
                    }
                    element.hide();
                    label.hide();
                    element.removeClassName('required-entry');
                    for (i = 0; i < validatorsCount; i += 1) {
                        element.removeClassName(options.validation[i]);
                    }
                    element.setValue('');
                }
                
            };
    
            selectorEventHandlers.push(handler);
            
            handler();
            
            select.observe('change', handler);
            
            return true;
        },
    
        hideFieldsDependingOnSelect: function(selectId, selectHideValues, hiddenFields) {
            var select = $(selectId),
                that = this,
                handler = null;
    
            if (!select) {
                return false;
            }   
    
            
            handler = function() {
                var i = 0, 
                    j = 0,
                    hiddenFieldsLength = hiddenFields.length,
                    hide = false,
                    field = null,
                    fieldRow = null,
                    validatorsCount = null;
    
                hide = selectHideValues.indexOf(select.getValue()) >= 0;
                for (i = 0; i < hiddenFieldsLength; i += 1) {
                    field = $(hiddenFields[i].id);
                    if (!field) {
                        continue;
                    }
    
                    fieldRow = that.getElementRow(hiddenFields[i].id);
                    if (!fieldRow) {
                        continue;
                    }
    
                    if (hide) {
                        fieldRow.hide();
                        field.removeClassName('required-entry');
                        for (j = 0, validatorsCount = hiddenFields[i].validation.length; j < validatorsCount; j += 1) {
                            field.removeClassName(hiddenFields[i].validation[j]);
                        }
                    } else {
                        fieldRow.show();
                        if (hiddenFields[i].isRequired) {
                            field.addClassName('required-entry');
                        }
                        for (j = 0, validatorsCount = hiddenFields[i].validation.length; j < validatorsCount; j += 1) {
                            field.addClassName(hiddenFields[i].validation[j]);
                        }
                        if (hiddenFields[i].onShow) {
                            hiddenFields[i].onShow();
                        }
                        that.executeSelectorEventHandlers(); 
                    }
                }
                               
            }
    
    
            handler();
    
            select.observe('change', handler);
            
            return true;
            
        },
    
        executeSelectorEventHandlers: function() {
            var i = 0,
                count = selectorEventHandlers.length;
            for (i = 0; i < count; i += 1) {
                selectorEventHandlers[i]();
            }
        },
    
        getLabelForId: function (id) {
            var labels = $$('label[for=\"' + id +'\"]');
            if (labels.length > 0) {
                return labels[0];
            } else {
                return false;
            }
        },
    
        getElementRow: function(id) {
            if ($(id)) {
                return $(id).up('tr');
            } else {
                return false;
            }
        },
    
        getNoteForId: function(id) {
            var row = this.getElementRow(id);
            if (!row) {
                return false;
            }
            note = row.down('p.note span');
            return note ? note : false;
        }
        
        
        
        
    }
})().init();";

    }

    public function getHeaderText()
    {
        $tablerate = Mage::registry('tablerate_data');
        if ($tablerate && $tablerate->getId()) {
            return Mage::helper('zitec_tablerates')->__('Edit Rate');
        } else {
            return Mage::helper('zitec_tablerates')->__('New Rate');
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId), "carrier" => $this->_getHelper()->getCarrierCode()));
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