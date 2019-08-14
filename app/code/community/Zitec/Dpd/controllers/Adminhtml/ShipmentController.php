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
class Zitec_Dpd_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_Action
{


    /**
     * this action is used to validate manually the address postcode
     */
    public function validatePostcodeAction(){
        $params = $this->getRequest()->getParams();
        $address = '';
        foreach($params['street'] as $street){
            $address .=  ' '.$street;
        }
        $address = trim($address);
        $params['address'] = $address;
        $foundAddresses = Mage::helper('zitec_dpd/postcode_search')->findAllSimilarAddressesForAddress($params);
        $content = $this->getLayout()
            ->createBlock('zitec_dpd/adminhtml_shipment_postcode_autocompleter')
            ->setData('found_addresses',$foundAddresses)
            ->setTemplate('zitec_dpd/sales/order/shipment/postcode/autocompleter.phtml')->toHtml();

        $this->getResponse()->setBody($content);

    }


    /**
     * download the pdf containg labels for each parcel
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function getLabelPdfAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipmentid');
        if (!$shipmentId) {

        }
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipmentLabel = $shipment->getShippingLabel();
        $pdf = Zend_Pdf::parse($shipmentLabel);

        return $this->_prepareDownloadResponse($shipment->getIncrementId().'_dpd_'.$shipment->getCreatedAt().'.pdf', $pdf->render(), 'application/pdf');
    }

    /**
     * create the manifest and generate the download link for pdf
     */
    public function manifestAction()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');

        $manifest = Mage::getModel('zitec_dpd/dpd_manifest');
        /* @var $manifest Zitec_Dpd_Model_Dpd_Manifest */
        try {
            $success       = $manifest->createManifestForShipments($shipmentIds);
            $notifications = $manifest->getNotifications();
            if ($success) {
                $downloadLinkMessage = "Successfully closed manifest %s for the following shipments. <a href='%s'>Download the manifest</a>.";
                array_unshift($notifications, $this->_getHelper()->__($downloadLinkMessage, $manifest->getManifestRef(), $this->_getDownloadManifestUrl($manifest->getManifestId())));
            }
            $message = implode("<br />", $notifications);
            $this->_getHelper()->addSuccessError($success, $message);
        } catch (Exception $e) {
            $this->_getHelper()->addError($this->_getHelper()->__("An error occurred whilst closing the manifest: %s", $e->getMessage()));
            $this->_getHelper()->log($e->getMessage(), __FUNCTION__, __CLASS__, __LINE__);
        }

        $this->_redirect("adminhtml/sales_shipment/index");
    }


    /**
     * @param int $manifestId
     *
     * @return string
     */
    protected function _getDownloadManifestUrl($manifestId)
    {
        return $this->_getHelper()->getDownloadManifestUrl($manifestId);
    }


    /**
     * create the pdf and download it
     */
    public function downloadManifestAction()
    {
        $manifestId = $this->getRequest()->getParam("manifest_id");
        try {
            if (!$manifestId) {
                $message = $this->_getHelper()->__("A problem occurred whilst attempting to download a manifest. No manifest was specified in the request.");
                Mage::throwException($message);
            }

            $manifest = Mage::getModel('zitec_dpd/dpd_manifest');
            /* @var $manifest Zitec_Dpd_Model_Dpd_Manifest */
            try {
                $manifest->load($manifestId);
            } catch (Exception $e) {
                $message = $this->_getHelper()->__("A problem occurred whilst attempting to download the manifest id %s: %s", $manifestId, $e->getMessage());
                Mage::throwException($message);
            }
            if ($manifest->getManifestId() != $manifestId) {
                $message = $this->_getHelper()->__("A problem occurred whilst attempting to download the manifest %s. The manifest no longer exists.", $manifestId);
                Mage::throwException($message);
            }
            $pdfFile = base64_decode($manifest->getPdf());
            $pdf     = Zend_Pdf::parse($pdfFile);

            return $this->_prepareDownloadResponse("{$manifest->getManifestRef()}_dpd_manifest.pdf", $pdf->render(), 'application/pdf');
        } catch (Mage_Core_Exception $e) {
            $this->_getHelper()->addError($e->getMessage());
            $this->_getHelper()->log($e->getMessage(), __FUNCTION__, __CLASS__, __LINE__);
        } catch (Exception $e) {
            $message = $this->_getHelper()->__("An unexpected problem occurred whilst attempting to download the manifest %s. %s", $manifestId, $e->getMessage());
            $this->_getHelper()->addError($message);
            $this->_getHelper()->log($message, __FUNCTION__, __CLASS__, __LINE__);
        }
        $this->_redirect("adminhtml/sales_shipment/index");

    }


    /**
     * merge more labels into on pdf and return the Zend_Pdf object
     *
     * @param array $labelsContent
     *
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }

        return $outputPdf;
    }


    protected function _createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page  = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_'
            . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);

        return $page;
    }

    /**
     * Delete shipment if the manifest was not closed before
     */
    public function deleteAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (!$shipmentId) {
            $this->_setDeleteResponse("No shipment was specified", $shipmentId);

            return;
        }
        $shipment = Mage::getModel('sales/order_shipment');
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment->load($shipmentId);

        $ships = Mage::getResourceModel('zitec_dpd/dpd_ship_collection');
        /* @var $ships Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection */
        $ship = $ships->getByShipmentId($shipmentId);
        /*  @var  $ship Zitec_Dpd_Model_Dpd_Ship */
        if (!$ship) {
            $this->_setDeleteResponse("Could not find any DPD shipment information for this shipment.", $shipment);

            return;
        }

        if ($ship->getManifestId()) {
            $this->_setDeleteResponse("You cannot cancel this shipment with DPD because the manifest is already closed.", $shipment);

            return;
        }
        $response = @unserialize($ship->getSaveShipmentResponse());
        /* @var $response Zitec_Dpd_Api_Shipment_Save_Response */
        if (!$response) {
            $this->_setDeleteResponse("Unable to load shipment information for this shipment.", $shipment);

            return;
        }


        try {
            $wsResult = $this->_getWsHelper()->deleteWsShipment($shipment,$response);
        } catch (Exception $e) {
            $this->_setDeleteResponse('An error occurred whilst attempting to delete the shipment information with DPD. <br /> "%s"', $shipment, $e->getMessage());

            return;
        }

        if ($wsResult->hasError()) {
            $this->_setDeleteResponse('An error occurred whilst attempting to delete the shipment information with DPD. <br /> "%s"', $shipment, $wsResult->getErrorText());

            return;
        }

        $shipment->setShippingLabel(null)->save();
        $ship->setShippingLabels(null)->save();

        $this->_forward("removeTrack", "sales_order_shipment", "admin");
    }



    protected function _setDeleteResponse($message, $shipment, $additional = '', $isError = true)
    {
        $response = array(
            'error'   => $isError,
            'message' => $this->__($message, $additional),
        );
        $response = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setBody($response);
        if ($isError) {
            $isShipmentLoaded = $shipment instanceof Mage_Sales_Model_Order_Shipment;
            $incrementId      = $isShipmentLoaded ? $shipment->getIncrementId() : "Unknown";
            $shipmentId       = $isShipmentLoaded ? $shipment->getId() : $shipment;
            $this->_getHelper()->log(sprintf("Error deleting shipment, id: %s, reference: %s", $shipmentId, $incrementId));
            $this->_getHelper()->log(sprintf("Message: %s", $message));
            if ($additional) {
                $this->_getHelper()->log(sprintf("Additional: %s", $additional));
            }
        }

        return $isError;
    }


    /**
     *
     * Create a pickup request in the future
     * sender address have to be configures
     * shipment should be already generated
     * the manifest can be closed or not
     */
    public function createPickupAction()
    {
        $shipmentIds = $this->getRequest()->getParam("shipment_ids");
        if (!$shipmentIds) {
            $this->_createPickupRedirect($this->__('Please select the shipments for which you wish to arrange a pickup.'));

            return;
        }

        list($day, $month, $year) = explode("/", $this->getRequest()->getParam("zitec_dpd_pickup_date"));
        if (!checkdate($month, $day, $year)) {
            $this->_createPickupRedirect($this->__('Please enter a pickup date in the format DD/MM/YYYY.'));

            return;
        }
        $year       = isset($year) && strlen($year) == 2 ? "20$year" : $year;
        $month      = isset($month) && strlen($month) < 2 ? str_pad($month, 2, "0") : $month;
        $day        = isset($day) && strlen($day) < 2 ? str_pad($day, 2, "0") : $day;
        $pickupDate = "$year$month$day";

        $pickupFromParts = $this->getRequest()->getParam("zitec_dpd_pickup_from");
        if (!is_array($pickupFromParts) || count($pickupFromParts) != 3) {
            $this->_createPickupRedirect($this->__('Please select a from and to time for the pickup.'));

            return;
        }
        $pickupFrom = implode("", $pickupFromParts);

        $pickupToParts = $this->getRequest()->getParam("zitec_dpd_pickup_to");
        if (!is_array($pickupToParts) || count($pickupToParts) != 3) {
            $this->_getHelper()->addError($this->__('Please select a from and to time for the pickup.'));
            $this->_redirect("adminhtml/sales_shipment/index");

            return;
        }
        $pickupTo = implode("", $pickupToParts);

        $instruction = $this->getRequest()->getParam("zitec_dpd_pickup_instruction");

        $pickupAddress = $this->_getWsHelper()->getPickupAddress();
        if (!is_array($pickupAddress)) {
            $this->_getHelper()->addError($this->__('You cannot create a pickup because you have not fully specified your pickup address. <br />Please set your pickup address in System->Configuration->Sales->Shipping Settings->DPD GeoPost Pickup Address.'));
            $this->_redirect("adminhtml/sales_shipment/index");

            return;
        }

        $apiParams = $this->_getWsHelper()->getPickupParams();
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_PICKUP_CREATE;

        $dpdApi = new Zitec_Dpd_Api($apiParams);
        $createPickup = $dpdApi->getApiMethodObject();

        $createPickup->setPickupTime($pickupDate, $pickupFrom, $pickupTo);
        $createPickup->setSpecialInstruction($instruction);
        $createPickup->setPickupAddress($pickupAddress);

        $shipments = Mage::getResourceModel('sales/order_shipment_collection');
        /* @var $shipments Mage_Sales_Model_Resource_Order_Shipment_Collection */
        $shipments->addFieldToFilter('entity_id', array("in" => $shipmentIds));

        $ships = Mage::getResourceModel('zitec_dpd/dpd_ship_collection');
        /* @var $ships Zitec_Dpd_Model_Mysql4_Dpd_Ship_Collection */
        $ships->filterByShipmentIds($shipmentIds);


        $includedShipments = array();
        foreach ($shipments as $shipment) {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            $ship = $ships->findByShipmentId($shipment->getId());
            /* @var $ship Zitec_Dpd_Model_Dpd_Ship */
            if (!$ship || !$this->_getHelper()->isShippingMethodDpd($shipment) || $this->_getHelper()->isCancelledWithDpd($shipment)) {
                continue;
            }
            $includedShipments[] = $shipment;

            $call = @unserialize($ship->getSaveShipmentCall());
            /* @var $call Zitec_Dpd_Api_Shipment_Save */
            if (!$call) {
                $message = $this->__("Unable to load shipment information for this shipment %s.", $shipment);
                $this->_createPickupRedirect($message);

                return;
            }
            $createPickup->addPieces($call->getShipmentServiceCode(), $call->getParcelCount(), $call->getTotalWeight(), $call->getReceiverCountryCode());
        }
        if (!$includedShipments) {
            $message = $this->__("Your list did not contain any DPD shipments for which to arrange a pickup.", $shipment);
            $this->_createPickupRedirect($message);

            return;
        }

        try {
            $createPickup->execute();
        } catch (Exception $e) {
            $message = $this->__('A problem occurred whilst communicating your shipment to DPD. <br />"%s"', $e->getMessage());
            $this->_getHelper()->log($message);
            $this->_createPickupRedirect($message);

            return;
        }
        $response = $createPickup->getCreatePickupResponse();
        if ($response->hasError()) {
            $message = $this->__('DPD reported an error whilst attempting to arrange your pickup. <br />DPD says, "%s"', $response->getErrorText());
            $this->_getHelper()->log($message);
            $this->_createPickupRedirect($message);

            return;
        }

        $pickup = Mage::getModel('zitec_dpd/dpd_pickup');
        /* @var $pickup Zitec_Dpd_Model_Dpd_Pickup */

        $pickup->setReference($response->getReferenceNumber())
            ->setDpdId($response->getDpdId())
            ->setPickupDate("$year-$month-$day")
            ->setPickupTimeFrom("$year-$month-$day " . implode(":", $pickupFromParts))
            ->setPickupTimeTo("$year-$month-$day " . implode(":", $pickupToParts))
            ->setCallData(serialize($createPickup))
            ->setResponseData(serialize($response))
            ->save();

        foreach ($includedShipments as $includedShipment) {
            $includedShipment->setData('zitec_dpd_pickup_time', "$year-$month-$day " . implode(":", $pickupFromParts));
            $includedShipment->setData('zitec_dpd_pickup_id', $pickup->getEntityId());
            $includedShipment->save();
        }

        $this->_getHelper()->addNotice("Your pickup was created successfully");
        $this->_redirect("adminhtml/sales_shipment/index");
    }



    /**
     *
     * @param string  $message
     * @param boolean $isError
     */
    protected function _createPickupRedirect($message, $isError = true)
    {
        if ($isError) {
            $this->_getHelper()->addError($message);
        } else {
            $this->_getHelper()->addNotice($message);
        }
        $this->_redirect("adminhtml/sales_shipment/index");
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


}
