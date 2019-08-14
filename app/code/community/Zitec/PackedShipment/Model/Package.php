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
class Zitec_PackedShipment_Model_Package extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Sales_Model_Order_Shipment
     */
    protected $_shipment;

    /**
     * Ids of the items packed in this parcel
     *
     * @var array
     */
    protected $_packedItemsIds;

    /**
     * Array with items packaged in this parcel
     *
     * @var array
     */
    protected $_packedItems;

    /*
     * Array with the quantities of each item in the package array (id1 => qty1 ...)
     */
    protected $_packedItemsQtys;

    /**
     * reference used to generate the labels
     *
     * @var string
     */
    protected $_ref;

    /**
     * (sum of items that compose)
     *
     * @var float
     */
    protected $_weight;

    /**
     * Total number of items that are in this package
     *
     * @var unknown_type
     */
    protected $_totalItemsQty;

    /**
     * Total price of the package (sum of the prices of the items that comprise it)
     *
     * @var float
     */
    protected $_price;


    /**
     *  We built a parcel, forcing get 2 mandatory parameters
     *
     * @param array                           $ids      array with the ids of items that are packed in this parcel
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param String                          $ref
     *
     * @throws Exception
     */
    public function __construct(Mage_Sales_Model_Order_Shipment $shipment, array $ids, $ref = null)
    {

        if (empty($ids))
            throw new Exception("__CLASS__ : One package has to have at least one item. Passed an array of empty itemsIds");

        $this
            ->setShipment($shipment)
            ->setPackedItemsIds($ids)
            ->setRef($ref);
    }

    /**
     * We set the ids of the items that are part of this package
     *
     * @param unknown_type $ids
     */
    public function setPackedItemsIds(array $ids)
    {
        $this->_packedItemsIds = $ids;

        return $this;
    }


    /**
     * Adds a collection of items in this bundle
     *
     * @param unknown_type $items
     */
    public function setShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->_shipment = $shipment;

        return $this;
    }


    /**
     * @param null $ref
     *
     * @return $this
     */
    public function setRef($ref = null)
    {
        $this->_ref = ($ref) ? $ref : $this->_autocalculateRef();

        return $this;
    }

    /**
     * calculate the reference ref using the sku of products in the package
     * @return string
     */
    protected function _autocalculateRef()
    {
        $items = $this->getPackedItems();
        $qtys  = $this->_getPackedItemsQtys();
        $skus  = array();
        foreach ($items as $itemId => $item) {
            $tmp = $item->getSku();
            if ($qtys[$itemId] > 1)
                $tmp .= '(' . $qtys[$itemId] . ' uds.)';

            $skus[] = $tmp;
        }

        return implode(', ', $skus);
    }


    /**
     *
     * Returns an array of the items in this bundle
     */
    public function getPackedItems()
    {
        if (!empty($this->_packedItems))
            return $this->_packedItems;

        $this->_packedItems = array();
        foreach ($this->_packedItemsIds as $id) {
            $this->_packedItems[$id] = $this->getItemByProductId($id);
        }

        return $this->_packedItems;
    }

    /*
     * Returns an array of the like  array (<item id> => <qty> ...)
     * for all products in the package
     */
    protected function _getPackedItemsQtys()
    {
        if (!empty($this->_packedItemsQtys))
            return $this->_packedItemsQtys;

        $this->_packedItemsQtys = array();
        foreach ($this->_packedItemsIds as $id) {
            if (!array_key_exists($id, $this->_packedItemsQtys)) {
                $this->_packedItemsQtys[$id] = 1;
            } else {
                $this->_packedItemsQtys[$id]++;
            }
        }

        return $this->_packedItemsQtys;
    }

    /**
     * Returns array with the ids item of this package
     */
    public function getPackedItemsIds()
    {
        return $this->_packedItemsIds;
    }


    public function getItemByProductId($productId)
    {
        foreach ($this->_shipment->getItemsCollection() as $item) {
            if ($item->getProductId() == $productId) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Returns the weight of the package. The first time is calculated by summing weights of items
     */
    public function getPackageWeight()
    {
        if (!empty($this->_weight))
            return $this->_weight;

        $this->_weight = (float)0;
        $qtys          = $this->_getPackedItemsQtys();
        foreach ($this->getPackedItems() as $itemId => $item) {
            $this->_weight += ($item->getWeight() * $qtys[$itemId]);
        }

        return $this->_weight;
    }

    /**
     * @return float
     */
    public function getPackagePrice()
    {
        if (!empty($this->_price))
            return $this->_price;

        $this->_price = (float)0;
        $qtys         = $this->_getPackedItemsQtys();
        foreach ($this->getPackedItems() as $itemId => $item) {
            $this->_price += ($item->getPrice() * $qtys[$itemId]);
        }

        return $this->_price;
    }

    /**
     * @return int|unknown_type
     */
    public function getTotalItemsQty()
    {
        if (!empty($this->_totalItemsQty))
            return $this->_totalItemsQty;
        $qtys                 = $this->_getPackedItemsQtys();
        $this->_totalItemsQty = 0;
        foreach ($qtys as $qty) {
            $this->_totalItemsQty += $qty;
        }

        return $this->_totalItemsQty;
    }

    public function getRef()
    {
        return $this->_ref;
    }

}
