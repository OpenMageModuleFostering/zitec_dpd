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
class Zitec_PackedShipment_Block_Addressvalidationinfojs extends Mage_Core_Block_Template
{
    /*
     * @see _getOrder()
     */
    protected $_order;

    /*
     * The current order is returned.
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        // Since this block is shown on page 'new shipment'
        // know which is the id of the current order in the querystring parameter
        // order_id.
        if (!$this->_order) {
            $this->_order = Mage::getModel('sales/order');
            $orderId      = $this->getRequest()->getParam('order_id');
            if ($orderId) {
                $this->_order->load($orderId);
            }
        }

        return $this->_order;
    }


    /*
     * 'True' is returned if the current order allows carrier validation
     * direcciones.
     * @param string $countryId 
     * @return bool
     */
    public function isAddressValidationAvailable()
    {
        if ($this->_getOrder()->getId()) {
            $carrier = $this->_getOrder()->getShippingCarrier();

            return $this->helper('zitec_packedshipment')->carrierSupportsAddressValidation($carrier, $this->getShippingAddressCountryId());
        }

        return false;
    }

    /*
     * The city of the delivery address for the current order is returned.
     * @return string
     */
    public function getShippingAddressCity()
    {
        if ($this->_getOrder()->getId()) {
            return $this->_getOrder()->getShippingAddress()->getCity();
        }

        return '';
    }

    /*
    * Postal code of the shipping address for the current order is returned.
    * @return string
    */
    public function getShippingAddressPostcode()
    {
        if ($this->_getOrder()->getId()) {
            return $this->_getOrder()->getShippingAddress()->getPostcode();
        }

        return '';
    }

    /**
     * The country code of the delivery address for the current order is returned.
     *
     * @return string
     */
    public function getShippingAddressCountryId()
    {
        if ($this->_getOrder()->getId()) {
            return $this->_getOrder()->getShippingAddress()->getCountryId();
        }

        return '';
    }

    /*
     * current order id is returned.
     * @return int
     */
    public function getOrderId()
    {
        $orderId = $this->_getOrder()->getId();

        return $orderId ? $orderId : 0;
    }

    /*
     * @return string
     */
    public function getAddressValidationDialogHtmlActionUrl()
    {
        return $this->helper("adminhtml")->getUrl('zitec_packedshipment/adminhtml_index/addressvalidationdialoghtml');
    }


}

