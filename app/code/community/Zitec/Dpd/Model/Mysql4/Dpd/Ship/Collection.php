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
class Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zitec_dpd/dpd_ship');
    }


    /**
     *
     * @param int $shipmentId
     *
     * @return Zitec_Dpd_Model_Dpd_Ship
     */
    public function setOrderFilter($orderId)
    {
        $this->addFieldToFilter('order_id', $orderId);
    }

    /**
     *
     * @param int $shipmentId
     *
     * @return Zitec_Dpd_Model_Dpd_Ship
     */
    public function getByShipmentId($shipmentId)
    {
        $this->addFieldToFilter('shipment_id', $shipmentId);
        if ($this->count() == 0) {
            return false;
        }

        return $this->getFirstItem();
    }

    /**
     *
     * @param array $shipmentIds
     *
     * @return \Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection
     */
    public function filterByShipmentIds(array $shipmentIds)
    {
        $this->addFieldToFilter("shipment_id", array("in" => $shipmentIds));

        return $this;
    }

    /**
     *
     * @param type $shipmentId
     *
     * @return Zitec_Dpd_Model_Dpd_Ship|boolean
     */
    public function findByShipmentId($shipmentId)
    {
        foreach ($this as $ship) {
            /* @var $ship Zitec_Dpd_Model_Dpd_Ship */
            if ($ship->getShipmentId() == $shipmentId) {
                return $ship;
            }
        }

        return false;
    }

}

