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
 * Ajax calls dialog packed shipment are handled.
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_PackedShipment_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /*
     * A block is loaded for dialogue address validation
     * or nothing, if the address is sent as valid or if there is nothing to do.
     */
    public function addressvalidationdialoghtmlAction()
    {

        $orderId = $this->getRequest()->getParam('order');
        $order   = Mage::getModel('sales/order');
        if ($orderId) {
            $order->load($orderId);
        }

        $city               = $this->getRequest()->getParam('city');
        $city               = $city ? $city : '';
        $postcode           = $this->getRequest()->getParam('postcode');
        $postcode           = $postcode ? $postcode : '';
        $countryId          = $this->getRequest()->getParam('countryid');
        $countryId          = $countryId ? $countryId : '';
        $dontCorrectAddress = $this->getRequest()->getParam('dontcorrectaddress');


        if (!$dontCorrectAddress) {
            $layout = Mage::getSingleton('core/layout');
            $layout->createBlock('zitec_packedshipment/addressvalidationdialog', 'root')
                ->setTemplate('zitec_packedshipment/sales/order/shipment/create/address_validation_dialog.phtml')
                ->setOrder($order)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setCountryId($countryId);
            $dialogHtml         = $layout->addOutputBlock('root')
                ->setDirectOutput(false)
                ->getOutput();
            $data['dialogHtml'] = trim($dialogHtml);
        } else // User has indicated that it wants to make more changes to the address.
        {
            $data['dialogHtml'] = '';
        }
        $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));
    }

    /*
     * The cost of transportation of packages shipping is returned.
     * this function receives the package weight, parcels, zip code of the desitnation

     * @return float
     */
    public function getshippingcostAction()
    {
        $orderId = $this->getRequest()->getParam('order');
        $order   = Mage::getModel('sales/order')->load($orderId);
        $carrier = $order->getShippingCarrier();
        if (!Mage::helper('zitec_packedshipment')->carrierSupportsCalculationOfShippingCosts($carrier)) {
            $data          = array();
            $data['error'] = Mage::helper('zitec_packedshipment')->__('An attempt to calculate the shipping cost, but the carrier does not support this operation.');
            $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));

        }

        $weightsParcels = $this->getRequest()->getParam('weightsParcels');

        $shippingAddress = $order->getShippingAddress();

        $city     = $this->getRequest()->getParam('city');
        $city     = $city ? $city : $shippingAddress->getCity();
        $postcode = $this->getRequest()->getParam('postcode');
        $postcode = $postcode ? $postcode : $shippingAddress->getPostcode();

        $errorStr = '';
        $shippingCost = $carrier->getShippingCost(
            $order,
            $city,
            $postcode,
            $weightsParcels,
            $errorStr);

        $data                 = array();
        $data['shippingcost'] = Mage::helper('core')->currency($shippingCost, true, false);

        // Shipping cost is returned to store for reports
        // Shipping module Reports.
        $data['shippingreportsshippingcost'] = $shippingCost;

        $profit         = $order->getBaseShippingAmount() - $shippingCost;
        $data['profit'] = Mage::helper('core')->currency($profit, true, false);

        $data['profitcolor'] = $profit >= 0 ? 'Black' : 'Red';

        $data['error'] = $errorStr;

        $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));
    }
}

