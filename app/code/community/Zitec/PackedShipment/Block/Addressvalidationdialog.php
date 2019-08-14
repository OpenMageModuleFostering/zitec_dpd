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
class Zitec_PackedShipment_Block_Addressvalidationdialog extends Mage_Adminhtml_Block_Template
{
    /*
     * @see setOrder
     */
    protected $_order;

    protected $_postcode;
    protected $_city;
    protected $_countryId;

    /*
     * Se establece el pedido para el bloque
     * @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    /*
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->_postcode = $postcode;

        return $this;
    }

    public function getPostcode()
    {
        return $this->_postcode;
    }

    /*
     * @param string $city
     */
    public function setCity($city)
    {
        $this->_city = $city;

        return $this;
    }

    public function getCity()
    {
        return $this->_city;
    }

    /**
     *
     * @param string $countryId
     *
     * @return \Zitec_PackedShipment_Block_Addressvalidationdialog
     */
    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_countryId;
    }


}

