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
class Zitec_TableRates_Block_Adminhtml_Tablerate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     *
     * @var array
     */
    protected $_map = null;

    public function __construct()
    {
        parent::__construct();
        $this->_map = Zitec_TableRates_Model_Mysql4_Tablerate::getLogicalDbFieldNamesMap();
        $this->setId('zitec_tablerates_tablerateGrid');
        $this->setDefaultSort($this->_map['pk']);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('zitec_tablerates/tablerate')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $store = $this->_getStore();

        $this->addColumn('tablerate_id', array(
            'header' => $this->_getHelper()->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => $this->_map['pk'],
        ));


        $this->addColumn('website', array(
            'header'       => $this->_getHelper()->__('Website'),
            'index'        => $this->_map['website_id'],
            'default'      => '',
            'type'         => 'options',
            'options'      => Mage::getSingleton('zitec_tablerates/source_website')->getWebsites(),
            'filter_index' => "main_table.{$this->_map['website_id']}"
        ));


        $this->addColumn('dest_country_id', array(
            'header'       => $this->_getHelper()->__('Destination Country'),
            'align'        => 'left',
            'index'        => $this->_map['dest_country_id'],
            'filter_index' => "main_table.{$this->_map['dest_country_id']}",
            'type'         => 'options',
            'options'      => $this->getCountryOptions(),
        ));

        $this->addColumn('dest_region', array(
            'header'       => $this->_getHelper()->__('Dest Region/State'),
            'align'        => 'left',
            'index'        => 'dest_region_name',
            'filter_index' => 'region_table.default_name',
            'filter'       => 'zitec_tablerates/adminhtml_widget_grid_column_filter_region',
            'default'      => '*',
        ));


        $this->addColumn('dest_zip', array(
            'header'  => $this->_getHelper()->__('Destination Zip/Postal Code'),
            'align'   => 'left',
            'index'   => $this->_map['dest_zip'],
            'default' => '*',
        ));

        $this->addColumn('method', array(
            'header'       => $this->_getHelper()->__('Service'),
            'align'        => 'left',
            'index'        => $this->_map['method'],
            'filter_index' => "main_table.{$this->_map['method']}",
            'type'         => 'options',
            'options'      => $this->_getHelper()->getMethodOptions(),
            'width'        => 150,
        ));

        if ($this->_getHelper()->supportsProduct()) {
            $this->addColumn('product', array(
                'header'       => $this->_getHelper()->__('Product'),
                'align'        => 'left',
                'index'        => $this->_map['product'],
                'filter_index' => "main_table.{$this->_map['product']}",
                'type'         => 'options',
                'options'      => $this->_getHelper()->getProductOptions(),
                'width'        => 150,
            ));
        }


        $this->addColumn('weight_and_above', array(
            'header'                    => $this->_getHelper()->__('Weight (and above)'),
            'index'                     => 'weight_and_above',
            'filter_condition_callback' => array($this, '_weightAndAboveFilter'),
            'default'                   => '*',
            'type'                      => 'number'
        ));

        if ($this->_getHelper()->supportsPriceVsDest()) {
            $this->addColumn('price_and_above', array(
                'header'                    => $this->_getHelper()->__('Price (and above)'),
                'align'                     => 'left',
                'index'                     => 'price_and_above',
                'filter_condition_callback' => array($this, '_priceAndAboveFilter'),
                'type'                      => 'price',
                'currency_code'             => $store->getBaseCurrency()->getCode(),
                'default'                   => '*',
            ));
        }

        $this->addColumn('is_enabled', array(
            'header'  => $this->_getHelper()->__('Enabled'),
            'align'   => 'left',
            'index'   => 'is_enabled_grid',
            'filter'  => false,
            'type'    => 'options',
            'options' => array(0 => $this->_getHelper()->__('No'), 1 => $this->_getHelper()->__('Yes')),
        ));

        $this->addColumn('shipping_price', array(
            'header'        => $this->_getHelper()->__('Shipping Price'),
            'align'         => 'left',
            'index'         => 'shipping_price_grid',
            'filter_index'  => "main_table.{$this->_map['price']}",
            'type'          => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'default'       => '',
        ));


        if ($this->_getHelper()->supportsMarkup()) {
            $this->addColumn('shipping_percentage', array(
                'header'       => $this->_getHelper()->__('Shipping Price %'),
                'align'        => 'left',
                'index'        => 'shipping_percentage_grid',
                'filter_index' => "main_table.{$this->_map['price']}",
                'type'         => 'number',
                'default'      => ''
            ));

            $this->addColumn('addition_amount', array(
                'header'        => $this->_getHelper()->__('Addition Price'),
                'align'         => 'left',
                'index'         => 'addition_amount_grid',
                'filter_index'  => "main_table.{$this->_map['price']}",
                'type'          => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ));

        }


        if ($this->_getHelper()->supportsCashOnDelivery()) {
            $this->addColumn('cod_surcharge_price', array(
                'header'        => $this->_getHelper()->__('COD Surcharge'),
                'align'         => 'left',
                'index'         => 'cod_surcharge_price',
                'filter_index'  => "main_table.{$this->_map['cashondelivery_surcharge']}",
                'type'          => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ));

            $this->addColumn('cod_surcharge_percentage', array(
                'header'       => $this->_getHelper()->__('COD Surcharge %'),
                'index'        => 'cod_surcharge_percentage',
                'filter_index' => "main_table.{$this->_map['cashondelivery_surcharge']}",
                'type'         => 'number',
            ));

            if ($this->_getHelper()->supportsCodMinSurcharge()) {
                $this->addColumn('cod_min_surcharge', array(
                    'header'        => $this->_getHelper()->__('COD Min. Surcharge'),
                    'index'         => $this->_map['cod_min_surcharge'],
                    'filter_index'  => "main_table.{$this->_map['cod_min_surcharge']}",
                    'type'          => 'price',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                ));
            }
        }


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField($this->_map['pk']);
        $this->getMassactionBlock()->setFormFieldName('tablerates');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->_getHelper()->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array("carrier" => $this->_getHelper()->getCarrierCode())),
            'confirm' => $this->_getHelper()->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true, '_query' => array("carrier" => $this->_getHelper()->getCarrierCode())));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('tablerate_id' => $row->getId(), "carrier" => $this->_getHelper()->getCarrierCode()));
    }

    /**
     * Get country options
     *
     * @return array
     */
    public function getCountryOptions()
    {
        $options   = array();
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0] = array('value' => '0', 'label' => '*',);
        }
        foreach ($countries as $country) {
            $options[$country['value']] = $country['label'];
        }

        return $options;
    }

    /**
     *
     * @param Zitec_TableRates_Model_Mysql4_Tablerate_Collection $collection
     * @param                                                    type Mage_Adminhtml_Block_Widget_Grid_Column
     *
     * @return \Zitec_TableRates_Block_Adminhtml_Tablerate_Grid
     */
    protected function _weightAndAboveFilter($collection, $column)
    {
        return $this->_weightPriceAndAboveFilter($collection, $column, false);
    }

    /**
     *
     * @param Zitec_TableRates_Model_Mysql4_Tablerate_Collection       $collection
     * @param                                                          type Mage_Adminhtml_Block_Widget_Grid_Column
     *
     * @return \Zitec_TableRates_Block_Adminhtml_Tablerate_Grid
     */
    protected function _priceAndAboveFilter($collection, $column)
    {
        return $this->_weightPriceAndAboveFilter($collection, $column, true);
    }

    /**
     *
     * @param Zitec_TableRates_Model_Mysql4_Tablerate_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column            $column
     * @param boolean                                            $priceVsDest
     *
     * @return \Zitec_TableRates_Block_Adminhtml_Tablerate_Grid
     */
    protected function _weightPriceAndAboveFilter(Zitec_TableRates_Model_Mysql4_Tablerate_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column, $priceVsDest = false)
    {

        if ($priceVsDest && !$this->_getHelper()->supportsPriceVsDest()) {
            return $this;
        }

        $value = $column->getFilter()->getValue();
        if (!is_array($value)) {
            return $this;
        }


        $from = isset($value['from']) ? $value['from'] : false;
        $to   = isset($value['to']) ? $value['to'] : false;

        if ($from === false && $to === false) {
            return $this;
        }

        $select = $collection->getSelect();
        if ($from !== false) {
            $select->where(" main_table.{$this->_map['weight_price']} >= ? ", $from);
        }


        if ($to !== false) {
            $select->where(" main_table.{$this->_map['weight_price']} <= ? ", $to);
        }

        $select->where(" main_table.{$this->_map['price_vs_dest']} = ? ", $priceVsDest ? 1 : 0);

        $selectStr = (string)$select;
        $this->_getHelper()->log($selectStr);

        return $this;
    }

    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId);
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