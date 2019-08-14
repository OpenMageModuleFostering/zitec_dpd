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
class Zitec_Dpd_Block_Adminhtml_Tablerate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;


    /**
     * Define grid properties
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('zitec_dpd_shippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }


    /**
     * Set current website
     *
     * @param int $websiteId
     *
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = Mage::app()->getWebsite($websiteId)->getId();

        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if (is_null($this->_websiteId)) {
            $this->_websiteId = Mage::app()->getWebsite()->getId();
        }

        return $this->_websiteId;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection */
        $collection = Mage::getResourceModel('zitec_dpd/carrier_tablerate_collection');
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {

        // 'website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'weight', 'price', 'method'
        $this->addColumn('dest_country', array(
            'header'  => Mage::helper('adminhtml')->__('Country'),
            'index'   => 'dest_country',
            'default' => '*',
        ));

        $this->addColumn('dest_region', array(
            'header'  => Mage::helper('adminhtml')->__('Region/State'),
            'index'   => 'dest_region',
            'default' => '*',
        ));

        $this->addColumn('dest_zip', array(
            'header'  => Mage::helper('adminhtml')->__('Zip/Postal Code'),
            'index'   => 'dest_zip',
            'default' => '*',
        ));

        $this->addColumn('weight', array(
            'header' => 'Weight / Price (and above)',
            'index'  => 'weight',
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('adminhtml')->__('Shipping Price/Percentage/Addition'),
            'index'  => 'shipping_price',
        ));

        $this->addColumn('Method', array(
            'header' => 'Method',
            'index'  => 'method',
        ));

        $this->addColumn('cashondelivery_surcharge', array(
            'header'  => 'Cash On Delivery Surcharge',
            'index'   => 'cashondelivery_surcharge',
            'default' => ''
        ));

        $this->addColumn('cod_min_surcharge', array(
            'header'  => 'Minimum COD Surcharge',
            'index'   => 'cod_min_surcharge',
            'default' => ''
        ));

        $this->addColumn('price_vs_dest', array(
            'header'  => 'Price vs Dest',
            'index'   => 'price_vs_dest',
            'default' => '0'
        ));


        return parent::_prepareColumns();
    }
}
