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
 *    Can be used as a class parent reports showing individual rows
 *    The DB (eg a report on individual orders).
 *    Is copied / modified Grid reports Magento.
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_ReportsCommon_Block_RecordsReport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_from;

    protected $_to;

    protected $_storeSwitcherVisibility = true;

    protected $_dateFilterVisibility = true;

    protected $_exportVisibility = true;

    protected $_subtotalVisibility = false;

    protected $_filters = array();

    protected $_defaultFilters = array(
        'report_from' => '',
        'report_to'   => ''
    );

    protected $_subReportSize = 5;

    protected $_grandTotals;

    protected $_errors = array();

    /**
     * stores current currency code
     */
    protected $_currentCurrencyCode = null;

    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setTemplate('zitec_reportscommon/records_report_grid.phtml');
        $this->setUseAjax(false);
        $this->setCountTotals(true);
    }


    protected function _prepareLayout()
    {
        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store' => null)))
                ->setTemplate('report/store/switcher.phtml')
        );

        $this->setChild('refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('adminhtml')->__('Refresh'),
                    'onclick' => $this->getRefreshButtonCallback(),
                    'class'   => 'task'
                ))
        );
        parent::_prepareLayout();

        return $this;
    }

    protected function _prepareColumns()
    {
        foreach ($this->_columns as $_column) {
            $_column->setSortable(false);
        }

        parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data   = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
            $data['report_from'] = Mage::helper('core')->htmlEscape($data['report_from']);
            $data['report_to'] = Mage::helper('core')->htmlEscape($data['report_to']);
            if (!isset($data['report_from'])) {
                // getting all reports from 2001 year
                $date                = new Zend_Date(mktime(0, 0, 0, 1, 1, 2001));
                $data['report_from'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            if (!isset($data['report_to'])) {
                // getting all reports from 2001 year
                $date              = new Zend_Date();
                $data['report_to'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            $this->_setFilterValues($data);
        } else if ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } else if (0 !== sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }

        $collection = $this->getCollection();

        if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
            /**
             * Validate from and to date
             */
            try {
                $from = $this->getLocale()->date($this->getFilter('report_from'), Zend_Date::DATE_SHORT, null, false);
                $to   = $this->getLocale()->date($this->getFilter('report_to'), Zend_Date::DATE_SHORT, null, false);
                $to->set('23:59:59', Zend_Date::TIMES);


                $collection->setDateRange($from, $to)
                    ->setPageSize(false)
                    ->setStoreIds($this->getStoreIds());
            } catch (Exception $e) {
                $collection->getSelect()->where('FALSE');
            }
        } else {
            // No se muestra nada si no se han eligido las fechas.
            $collection->getSelect()->where('FALSE');
        }

        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = array();
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }

        if ($storeIds) {
            $collection->setStoreIds($storeIds);
        }

        if ($this->getSubReportSize() !== null) {
            $collection->setPageSize($this->getSubReportSize());
        }

        $debug = (string)$collection->getSelect();
        // echo ($debug);

        $this->setCollection($collection);

        $this->_computeTotals();

        Mage::dispatchEvent('adminhtml_widget_grid_filter_collection',
            array('collection' => $this->getCollection(), 'filter_values' => $this->_filterValues)
        );
    }

    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            //if (isset($data[$name])) {
            $this->setFilter($name, $data[$name]);
            //}
        }

        return $this;
    }

    /**
     * Set visibility of store switcher
     *
     * @param boolean $visible
     */
    public function setStoreSwitcherVisibility($visible = true)
    {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return visibility of store switcher
     *
     * @return boolean
     */
    public function getStoreSwitcherVisibility()
    {
        return $this->_storeSwitcherVisibility;
    }

    /**
     * Return store switcher html
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Set visibility of date filter
     *
     * @param boolean $visible
     */
    public function setDateFilterVisibility($visible = true)
    {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return visibility of date filter
     *
     * @return boolean
     */
    public function getDateFilterVisibility()
    {
        return $this->_dateFilterVisibility;
    }

    /**
     * Set visibility of export action
     *
     * @param boolean $visible
     */
    public function setExportVisibility($visible = true)
    {
        $this->_exportVisibility = $visible;
    }

    /**
     * Return visibility of export action
     *
     * @return boolean
     */
    public function getExportVisibility()
    {
        return $this->_exportVisibility;
    }

    /**
     * Set visibility of subtotals
     *
     * @param boolean $visible
     */
    public function setSubtotalVisibility($visible = true)
    {
        $this->_subtotalVisibility = $visible;
    }

    /**
     * Return visibility of subtotals
     *
     * @return boolean
     */
    public function getSubtotalVisibility()
    {
        return $this->_subtotalVisibility;
    }

    public function getDateFormat()
    {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Return refresh button html
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return ($this->getRequest()->getParam($name))
                ? htmlspecialchars($this->getRequest()->getParam($name)) : '';
        }
    }

    public function setSubReportSize($size)
    {
        $this->_subReportSize = $size;
    }

    public function getSubReportSize()
    {
        return $this->_subReportSize;
    }

    /**
     * Retrieve locale
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }

        return $this->_locale;
    }

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     *
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl($url,
                    array(
                        '_current' => true,
                        'filter'   => $this->getParam($this->getVarNameFilter(), null)
                    )
                ),
                'label' => $label
            )
        );

        return $this;
    }


    public function getReport($from, $to)
    {
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        $totalObj = Mage::getModel('reports/totals');
        $this->setTotals($totalObj->countTotals($this, $from, $to));
        $this->addGrandTotals($this->getTotals());

        return $this->getCollection()->setDateRange($from, $to)
            ->setPageSize(false)
            ->setStoreIds($this->getStoreIds());
    }

    public function addGrandTotals($total)
    {
        $totalData = $total->getData();
        foreach ($totalData as $key => $value) {
            $_column = $this->getColumn($key);
            if ($_column->getTotal() != '') {
                $this->getGrandTotals()->setData($key, $this->getGrandTotals()->getData($key) + $value);
            }
        }
        /*
         * recalc totals if we have average
         */
        foreach ($this->getColumns() as $key => $_column) {
            if (strpos($_column->getTotal(), '/') !== false) {
                list($t1, $t2) = explode('/', $_column->getTotal());
                if ($this->getGrandTotals()->getData($t2) != 0) {
                    $this->getGrandTotals()->setData(
                        $key,
                        (float)$this->getGrandTotals()->getData($t1) / $this->getGrandTotals()->getData($t2)
                    );
                }
            }
        }
    }

    public function getGrandTotals()
    {
        if (!$this->_grandTotals) {
            $this->_grandTotals = new Varien_Object();
        }

        return $this->_grandTotals;
    }

    /**
     * Retrieve grid as CSV
     *
     * @return unknown
     */
    public function getCsv()
    {
        $csv             = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($item)) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = array();
            $j    = 0;
            foreach ($this->_columns as $column) {
                if ($j == 0) {
                    $data[] = '"' . $this->getTotalsText() . '"';
                } else {
                    if (!$column->getIsSystem()) {
                        //$data[] = '"'.str_replace('"', '""', $column->getRowField($this->getTotals())).'"';
                        $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($this->getTotals())) . '"';
                    }
                }
                $j++;
            }
            $csv .= implode(',', $data) . "\n";
        }

        return $csv;

    }

    /**
     * Retrieve grid as Excel Xml
     *
     * @return unknown
     */
    public function getExcel($filename = '')
    {

        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $headers = array();
        $data    = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = $column->getHeader();
            }
        }
        $data[] = $headers;

        foreach ($this->getCollection() as $item) {
            $row = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($item);
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals()) {

            $j   = 0;
            $row = array();
            foreach ($this->_columns as $column) {
                if ($j == 0) {
                    $row[] = $this->__($this->getTotalText());
                } elseif (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getTotals());
                }
                $j++;
            }
            $data[] = $row;
        }

        $xmlObj = new Varien_Convert_Parser_Xml_Excel();
        $xmlObj->setVar('single_sheet', $filename);
        $xmlObj->setData($data);
        $xmlObj->unparse();

        return $xmlObj->getData();

    }

    public function getSubtotalText()
    {
        return $this->__('Subtotal');
    }

    public function getTotalText()
    {
        return $this->__('Totals');
    }

    public function getEmptyText()
    {
        return $this->__('No records found for this period.');
    }


    /**
     * onlick event for refresh button to show alert if fields are empty
     *
     * @return string
     */
    public function getRefreshButtonCallback()
    {
        return "{$this->getJsObjectName()}.doFilter();";

        return "if ($('period_date_to').value == '' && $('period_date_from').value == '') {alert('" . $this->__('Please specify at least start or end date.') . "'); return false;}else {$this->getJsObjectName()}.doFilter();";
    }

    /**
     * Retrieve errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Retrieve correct currency code for select website, store, group
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $this->_currentCurrencyCode = Mage::app()->getStore($this->getRequest()->getParam('store'))->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('website')) {
                $this->_currentCurrencyCode = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('group')) {
                $this->_currentCurrencyCode = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getWebsite()->getBaseCurrencyCode();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            }
        }

        return $this->_currentCurrencyCode;
    }

    protected function _computeTotals()
    {
        $totals = new Varien_Object();

        foreach ($this->getCollection() as $item) {
            foreach ($this->getColumns() as $col) {
                if ($col->getTotal()) {
                    $fieldName = $col->getIndex();
                    $subTotal  = $totals->getData($fieldName);
                    $subTotal  = $subTotal ? $subTotal : 0;
                    $subTotal += $item->getData($fieldName);
                    $totals->setData($fieldName, $subTotal);
                }
            }
        }

        $collectionSize = $this->getCollection()->getSize();
        if ($collectionSize) {
            foreach ($this->getColumns() as $col) {
                $fieldName = $col->getIndex();
                if ($col->getTotal() == 'avg') {
                    $avg = $totals->getData($fieldName) / $collectionSize;
                    $totals->setData($fieldName, $avg);
                }
                if ($col->getType() == 'currency') {
                    $total = Mage::helper('core')->currency($totals->getData($fieldName), true, false);
                    $totals->setData($fieldName, $total);
                }

            }
        }

        $this->setTotals($totals);

    }

    public function getRowUrl($row)
    {
        return false;
    }
}
