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
class Zitec_ShippingReports_Block_Adminhtml_Profitability_Grid extends Zitec_ReportsCommon_Block_RecordsReport_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('zitec_ShippingReportsProfitabilityGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setSubReportSize(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('zitec_shippingreports/profitability_collection');
        $collection->addReportFields();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_ref', array(
            'header'   => Mage::helper('zitec_shippingreports')->__('Order #'),
            'align'    => 'right',
            'index'    => 'increment_id',
            'renderer' => 'Zitec_ShippingReports_Block_Adminhtml_Profitability_Renderer_Orderref'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('zitec_shippingreports')->__('Purchased On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('zitec_shippingreports')->__('Ship to Name'),
            'index'  => 'zitec_shipping_name',
        ));

        $this->addColumn('shipping_postcode', array(
            'header' => Mage::helper('zitec_shippingreports')->__('Postcode'),
            'index'  => 'zitec_shipping_postcode',
        ));

        $this->addColumn('shipping_region', array(
            'header' => Mage::helper('zitec_shippingreports')->__('Region'),
            'index'  => 'zitec_shipping_region',
        ));

        $this->addColumn('zitec_shippingreports', array(
            'header' => Mage::helper('zitec_shippingreports')->__('Country'),
            'index'  => 'zitec_shipping_country_id',
            'type'   => 'country'
        ));

        $this->addColumn('grand_total', array(
            'header'   => Mage::helper('zitec_shippingreports')->__('Order total'),
            'index'    => 'base_grand_total',
            'type'     => 'currency',
            'total'    => 'sum',
            'currency' => 'base_currency_code',

        ));


        $this->addColumn('shipping_amount', array(
            'header'   => Mage::helper('zitec_shippingreports')->__('Shipping Price'),
            'index'    => 'base_shipping_amount',
            'type'     => 'currency',
            'total'    => 'sum',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('total_shipping_cost', array(
            'header'   => Mage::helper('zitec_shippingreports')->__('Total Shipping Cost'),
            'index'    => 'zitec_total_shipping_cost',
            'type'     => 'currency',
            'total'    => 'sum',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('shipping_profit', array(
            'header'   => Mage::helper('zitec_shippingreports')->__('Shipping Profit/Loss'),
            'index'    => 'zitec_shipping_profit',
            'total'    => 'sum',
            'align'    => 'right',
            'renderer' => 'Zitec_ReportsCommon_Block_Renderer_Posnegcurrency',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('zitec_shippingreports')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('zitec_shippingreports')->__('Excel XML'));

        return parent::_prepareColumns();
    }


}

