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
class Zitec_Dpd_Block_Adminhtml_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{


    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if ($this->_getHelper()->moduleIsActive()) {
            $this->addColumn('zitec_dpd_manifest', array(
                'header'                    => $this->_getHelper()->__('Manifest'),
                'index'                     => 'zitec_manifest_id',
                'type'                      => 'text',
                'renderer'                  => 'Zitec_Dpd_Block_Adminhtml_Sales_Shipment_Grid_Renderer_Manifest',
                'filter_condition_callback' => array($this, '_filterManifesto'),
            ));

            $this->addColumnsOrder('zitec_dpd_manifest_closed', 'total_qty');

            /*
             * removed column from frontend temporary
             *
            $this->addColumn('zitec_dpd_pickup_time', array(
                'header' => $this->_getHelper()->__('DPD Pickup'),
                'index'  => 'zitec_dpd_pickup_time',
                'type'   => 'text',
            ));
            $this->addColumnsOrder('zitec_dpd_pickup_time', 'zitec_dpd_manifest');
            */

        }

        return parent::_prepareColumns();


    }

    /**
     *
     * @return Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection
     */
    public function getCollection()
    {
        $collection = parent::getCollection();
        if ($collection && !$this->getIsI4ShipsJoined()) {
            /* @var $collection Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection */
            $resource = Mage::getSingleton('core/resource');
            /* @var $resource Mage_Core_Model_Resource */
            $shipsTableName    = $resource->getTableName('zitec_dpd_ships');
            $manifestTableName = $resource->getTableName('zitec_dpd_manifest');

            $collection->getSelect()
                ->joinLeft(array('ships' => $shipsTableName), 'ships.shipment_id = main_table.entity_id', array('zitec_manifest_id' => 'ships.manifest_id'))
                ->joinLeft(array('manifest' => $manifestTableName), 'manifest.manifest_id = ships.manifest_id', array('zitec_manifest_ref' => 'manifest.manifest_ref'));
            $this->setIsI4ShipsJoined(true);
        }

        return $collection;
    }

    /**
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column                  $column
     *
     * @return \Zitec_Dpd_Block_Adminhtml_Sales_Shipment_Grid
     */
    protected function _filterManifesto($collection, $column)
    {
        $manifestRef = $column->getFilter()->getCondition();
        if ($manifestRef) {
            $resource = Mage::getSingleton('core/resource');
            /* @var $resource Mage_Core_Model_Resource */
            $whereClause = $resource->getConnection("core_read")->quoteInto("manifest.manifest_ref like ? ", $manifestRef);
            $collection->getSelect()
                ->where($whereClause);
            $debug = (string)$collection->getSelect();
        }

        return $this;
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }

}

