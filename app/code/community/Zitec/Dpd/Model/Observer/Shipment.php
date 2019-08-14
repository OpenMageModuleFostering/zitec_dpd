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
class Zitec_Dpd_Model_Observer_Shipment
{

    /**
     *
     * @var boolean
     */
    protected $_isProcessed = false;

    /**
     *
     * @var Mage_Sales_Model_Order_Address
     */
    protected $_shippingAddress = null;

    /**
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     *
     * @var Zitec_Dpd_Api_Shipment_Save_Response
     */
    protected $_response = null;

    /**
     *
     * @var Zitec_Dpd_Api_Shipment_Save
     */
    protected $_call = null;

    /**
     *
     * @var string
     */
    protected $_labelPdfStr = null;


    /**
     *
     * @var Mage_Sales_Model_Order_Shipment
     */
    protected $_shipment = null;

    /**
     *
     */
    protected $_isOrderShipmentNew = false;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderShipmentSaveBefore(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if (is_null($shipment->getId())) {
            $this->_isOrderShipmentNew = true;
        }
    }

    /**
     * Saves the DPD shipment when the shipment is created.
     *
     * @param void
     */
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = $observer->getEvent()->getShipment();
        if (!$this->_canSaveDpdShipment($shipment)) {
            return;
        }

        $this->_isProcessed = true;

        $this->_setShipment($observer->getEvent()->getShipment());
        $this->_setOrder($this->_getShipment()->getOrder());

        $packedShipment = new Zitec_PackedShipment_Model_PackedShipment($this->_getShipment(), Mage::app()->getRequest()->getParam('packages'));
        if (!$packedShipment->getPackages()) {
            return $observer;
        }


        $this->_createShipment($packedShipment);

        $this->_getLabels();


        $successNotice = $this->__('Your new shipment was successfully communicated to DPD.');
        if ($this->_getDPDMessage()) {
            $successNotice .= '<br />' . sprintf($this->__('DPD says, "%s"'), $this->_getDPDMessage());
        }
        $this->_getHelper()->addNotice($successNotice);
    }

    /**
     * @param Zitec_PackedShipment_Model_PackedShipment $packedShipment
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _createShipment(Zitec_PackedShipment_Model_PackedShipment $packedShipment)
    {
        $dpdApi      = new Zitec_Dpd_Api($this->_getShipmentParams());
        $dpdShipment = $dpdApi->getApiMethodObject();

        $serviceCode = $this->_getHelper()->getDPDServiceCode($this->_getOrder()->getShippingMethod());
        if (!$serviceCode) {
            Mage::throwException(sprintf($this->__("An error occurred communicating the shipment to DPD. The shipping method '%s' is invalid"), $this->_getOrder()->getShippingMethod()));
        }
        $dpdShipment->setReceiverAddress($this->_getShippingAddress())
            ->setShipmentReferenceNumber($this->_getShipment()->getIncrementId())
            ->setShipmentServiceCode($serviceCode);


        foreach ($packedShipment->getPackages() as $packageIdx => $package) {
            $dpdShipment->addParcel($packageIdx + 1, $package->getPackageWeight(), $package->getRef());
        }

        if ($this->_getHelper()->isOrderCashOnDelivery($this->_getOrder())) {
            $paymentType = $this->_getHelper()->getCodPaymentType($this->_getOrder());
            $dpdShipment->setCashOnDelivery(round($this->_getOrder()->getBaseGrandTotal(), 2), $this->_getOrder()->getBaseCurrencyCode(), $paymentType);
        }
        $order = $this->_getOrder();
        $insurance      = Mage::helper('zitec_dpd')->extractInsuranceValuesByOrder($order);
        $dpdShipment->setAdditionalHighInsurance($insurance['goodsValue'], $insurance['currency'], $insurance['content']);

        try {
            $response = $dpdShipment->execute();
        } catch (Zitec_Dpd_Api_Shipment_Save_Exception_ReceiverAddressTooLong $e) {
            $message = "The shipment could not be communicated to DPD because the shipping street the maximum permitted length of %s characters. <br />Please edit the shipping address to reduce the length of the street in the shipping address.";
            Mage::throwException(sprintf($this->__($message), $e->getMaxLength()));
        } catch (Exception $e) {
            Mage::throwException(sprintf($this->__("An error occurred communicating the shipment to DPD at %s:<br /> '%s'"), $dpdShipment->getUrl(), $e->getMessage()));
        }

        if ($response->hasError()) {
            $message = sprintf($this->__('DPD could not process the new shipment. The following error was returned: <br /> "%s: %s"'), $response->getErrorCode(), $response->getErrorText());
            Mage::throwException($message);
        }

        $this->_response = $response;
        $this->_call     = $dpdShipment;

        $this->_saveShipmentResponse($response, $dpdShipment);

        return true;
    }

    /**
     *
     * @return string|boolean
     */
    protected function _getDPDMessage()
    {
        if ($this->_response instanceof Zitec_Dpd_Api_Shipment_Save_Response) {
            return $this->_response->getMessage();
        } else {
            return false;
        }
    }

    /**
     *
     * @return array
     */
    protected function _getShipmentParams()
    {
        $apiParams           = $this->_getWsHelper()->getShipmentParams($this->_getOrder()->getStoreId());
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_CREATE_SHIPMENT;

        return $apiParams;
    }

    /**
     * @param Zitec_Dpd_Api_Shipment_Save_Response $response
     *
     * @return bool
     * @throws Exception
     */
    protected function _saveTracking($response)
    {
        $trackNumber = $response->getDpdShipmentReferenceNumber();
        $carrier     = Mage::helper('zitec_dpd')->getDpdCarrierCode();
        $shipment    = $this->_getShipment();
        $carrierName = $this->_getHelper()->getCarrierName($this->_getStore());
        $track       = Mage::getModel('sales/order_shipment_track')
            ->setNumber($trackNumber)
            ->setTrackNumber($trackNumber)
            ->setCarrierCode($carrier)
            ->setTitle($carrierName);
        $shipment->addTrack($track);
        $shipment->save();

        return true;
    }

    /**
     *
     * @return boolean
     */
    protected function _getLabels()
    {
        try {
            $this->_labelPdfStr = $this->_getWsHelper()->getNewPdfShipmentLabelsStr($this->_response->getDpdShipmentId(), $this->_response->getDpdShipmentReferenceNumber());
            $this->_getShipment()->setShippingLabel($this->_labelPdfStr)->save();

        } catch (Exception $e) {
            Mage::throwException(sprintf('An error occurred whilst retreiving the shipping labels from DPD for the new shipment. <br /> "%s"'));
        }

        return true;
    }

    /**
     *
     * @param Zitec_Dpd_Api_Shipment_Save_Response $response
     *
     * @return boolean
     */
    protected function _saveShipmentResponse($response, $call)
    {
        $ship = Mage::getModel('zitec_dpd/dpd_ship');
        $ship->setShipmentId($this->_getShipment()->getId())
            ->setOrderId($this->_getOrder()->getId())
            ->setSaveShipmentCall(serialize($call))
            ->setShippingLabels(base64_encode($this->_labelPdfStr))
            ->setSaveShipmentResponse(serialize($response))
            ->save();

        $this->_saveTracking($response);

        $this->_getShipment()->setShippingLabel($this->_labelPdfStr)->save();

        return true;
    }

    /**
     * is not used anymore - to copy the file we use config.xml
     *
     * @param Varien_Object $observer
     *
     * @return
     */
    public function postcodeAddressConvertToOrder($observer)
    {
        if ($observer->getEvent()->getAddress()->getValidPostcode()) {
            $observer->getEvent()->getOrder()
                ->setValidPostcode($observer->getEvent()->getAddress()->getValidPostcode());
        }
        if ($observer->getEvent()->getAddress()->getAutoPostcode()) {
            $observer->getEvent()->getOrder()
                ->setAutoPostcode($observer->getEvent()->getAddress()->getAutoPostcode());
        }

        return $this;
    }

    /**
     * add some blocks used for postcode validation
     *
     * @param Varien_Object $observer
     *
     * @return
     */
    public function adminAddPostcodeAutocompleteBlock($observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $block     = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Address) {
            $content = $transport->getHtml();

            $content .= Mage::app()->getLayout()
                ->createBlock('core/template')
                ->setTemplate('zitec_dpd/sales/order/address/postcode/validate.phtml')->toHtml();

            $transport->setHtml($content);
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info) {
            $isDpdCarrier             = Mage::helper('zitec_dpd')->isDpdCarrierByOrder($block->getOrder());
            $isPCAutocompleterEnabled = Mage::helper('zitec_dpd')->isEnabledPostcodeAutocompleteByOrder($block->getOrder());
            if ($isDpdCarrier) {
                $content = $transport->getHtml();
                if ($isPCAutocompleterEnabled) {

                    $content .= Mage::app()->getLayout()
                        ->createBlock('core/template')
                        ->setOrder($block->getOrder())
                        ->setTemplate('zitec_dpd/sales/order/address/postcode/alert-problem.phtml')->toHtml();
                }

                $content .= Mage::app()->getLayout()
                    ->createBlock('core/template')
                    ->setOrder($block->getOrder())
                    ->setTemplate('zitec_dpd/sales/order/address/street/alert-problem.phtml')->toHtml();

                $transport->setHtml($content);
            }
        }


        return $this;
    }


    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return bool
     */
    protected function _canSaveDpdShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$this->_isOrderShipmentNew || $this->_isProcessed) {
            return false;
        }

        if (!$this->_getHelper()->moduleIsActive($shipment->getOrder()->getStore())) {
            return false;
        }

        if (!$this->_getHelper()->isShippingMethodDpd($shipment->getOrder()->getShippingMethod())) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return \Zitec_Dpd_Model_Observer_Shipment
     */
    protected function _setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return $this->_order;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return \Zitec_Dpd_Model_Observer_Shipment
     */
    protected function _setShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->_shipment = $shipment;

        return $this;
    }

    /**
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _getShipment()
    {
        return $this->_shipment;
    }

    /**
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return $this->_getOrder()->getStore();
    }

    /**
     * Important: Here we load the shipping address from the database rather than
     * using the one accessible from the order. This is intentional. using the one on the order
     * appears to cause a crash with some versions of PHP.
     *
     * @return Mage_Sales_Model_Order_Address
     */
    protected function _getShippingAddress()
    {
        if (!$this->_shippingAddress) {
            $this->_shippingAddress = Mage::getModel('sales/order_address')->load($this->_getOrder()->getShippingAddressId());
        }

        return $this->_shippingAddress;
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Ws
     */
    protected function _getWsHelper()
    {
        return Mage::helper('zitec_dpd/ws');
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }

    /**
     *
     * @param string $translateStr
     *
     * @return string
     */
    protected function __($translateStr)
    {
        return $this->_getHelper()->__($translateStr);
    }

}