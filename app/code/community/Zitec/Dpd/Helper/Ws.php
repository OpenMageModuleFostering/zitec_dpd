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
 * this helper is used for calling the api methods implemented further in the Zitec_Dpd_Api library
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Helper_Ws extends Mage_Core_Helper_Abstract
{
    protected $_store = null;

    /**
     * create shipping label and create the pdf content
     *
     * @param int    $dpdShipmentId
     * @param string $dpdShipmentReferenceNumber
     *
     * @return string
     * @throws Exception
     */
    public function getNewPdfShipmentLabelsStr($dpdShipmentId, $dpdShipmentReferenceNumber)
    {
        $apiParams = $this->getShipmentParams();
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_GET_LABEL;
        $dpdApi = new Zitec_Dpd_Api($apiParams);
        $dpdLabel = $dpdApi->getApiMethodObject();

        $dpdLabel->setShipment($dpdShipmentId, $dpdShipmentReferenceNumber);
        try {
            $dpdLabel->execute();
        } catch (Exception $e) {
            throw $e;
        }
        $labelResponse = $dpdLabel->getResponse();
        /* @var $labelResponse Zitec_Dpd_Api_Shipment_GetLabel_Response */
        if ($labelResponse->hasError()) {
            throw new Exception($labelResponse->getErrorText());
        }

        return $labelResponse->getPdfFile();
    }



    /**
     * this method is used for tracking the order shipment
     * in the response of this method will be a url on dpd website
     *
     * @param $saveShipmentResponse
     *
     * @return mixed
     */
    public function getShipmentStatus($saveShipmentResponse){

        $apiParams = $this->getShipmentParams();
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_GET_SHIPMENT_STATUS;


        $dpdApi = new Zitec_Dpd_Api($apiParams);
        $getShipmentStatus = $dpdApi->getApiMethodObject();

        $getShipmentStatus->setShipment($saveShipmentResponse->getDpdShipmentId(), $saveShipmentResponse->getDpdShipmentReferenceNumber());


        $getShipmentStatus->execute();
        $statusResponse = $getShipmentStatus->getShipmentStatusResponse();

        return $statusResponse;
    }


    /**
     * remove the shipment tracking code form DPD system
     * after this action pdf content will be set to null
     * but the shipment will still exist in magento admin interface
     *
     * @param $shipment
     * @param $saveShipmentResponse
     *
     * @return mixed
     */
    public function deleteWsShipment($shipment, $saveShipmentResponse){

        $apiParams = $this->getShipmentParams($shipment->getStore());
        $apiParams['method'] = Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_DELETE;


        $dpdApi = new Zitec_Dpd_Api($apiParams);
        $deleteShipment = $dpdApi->getApiMethodObject();

        $deleteShipment->addShipmentReference($saveShipmentResponse->getDpdShipmentId(), $saveShipmentResponse->getDpdShipmentReferenceNumber());


        $deleteShipment->execute();
        $wsResult = $deleteShipment->getDeleteShipmentResponse();

        return $wsResult;
    }



    /**
     *
     * @param mixed $store
     *
     * @return array
     */
    public function getShipmentParams($store = null)
    {
        $params = $this->_getConnectionParams($store);
        $params[Zitec_Dpd_Api_Configs::URL] = $this->_getUrlShipment();

        return $params;
    }

    /**
     *
     * @param mixed $store
     *
     * @return array
     */
    public function getManifestParams($store = null)
    {
        $params = $this->_getConnectionParams($store);
        $params[Zitec_Dpd_Api_Configs::URL] = $this->_getUrlManifest();

        return $params;
    }

    /**
     *
     * @param mixed $store
     *
     * @return array
     */
    public function getPickupParams($store = null)
    {
        $params = $this->_getConnectionParams($store);
        $params[Zitec_Dpd_Api_Configs::URL] = $this->_getUrlPickup();

        return $params;
    }

    /**
     *
     * @param mixed $store
     *
     * @return array
     */
    protected function _getConnectionParams($store = null)
    {
        $this->_store = $store;

        return array(
            Zitec_Dpd_Api_Configs::CONNECTION_TIMEOUT => $this->_getConnectionTimeout(),
            Zitec_Dpd_Api_Configs::WS_USER_NAME       => $this->_getWsUserName(),
            Zitec_Dpd_Api_Configs::WS_PASSWORD        => $this->_getWsPassword(),
            Zitec_Dpd_Api_Configs::SENDER_ADDRESS_ID  => $this->_getSenderAddressId(),
            Zitec_Dpd_Api_Configs::PAYER_ID           => $this->_getPayerId()
        );
    }

    /**
     *
     * @return int
     */
    protected function _getConnectionTimeout()
    {
        return $this->_getConfigData("connectionTimeout");
    }

    /**
     *
     * @return type
     */
    protected function _getWsUserName()
    {
        return $this->_getConfigData("username");
    }

    /**
     *
     * @return string
     */
    protected function _getWsPassword()
    {
        return $this->_getConfigData("password");
    }

    /**
     *
     * @return string
     */
    protected function _getSenderAddressId()
    {
        return $this->_getConfigData("senderAddressId");
    }

    /**
     *
     * @return string
     */
    protected function _getPayerId()
    {
        return $this->_getConfigData("payerId");
    }

    /**
     *
     * @return string
     */
    protected function _getUrlShipment()
    {
        return $this->_getWsUrl("shipmentUrl");
    }

    /**
     *
     * @return string
     */
    protected function _getUrlManifest()
    {
        return $this->_getWsUrl("manifestUrl");
    }

    /**
     *
     * @return string
     */
    protected function _getUrlPickup()
    {
        return $this->_getWsUrl("pickupUrl");
    }

    /**
     *
     * @param string $field
     *
     * @return mixed
     */
    protected function _getConfigData($field)
    {
        return $this->_getHelper()->getConfigData($field, $this->_store);
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
     * @param string $dateStr
     *
     * @return string|boolean
     */
    public function convertDPDDate($dateStr)
    {
        if (!is_string($dateStr) || strlen($dateStr) != 8) {
            return false;
        }

        return substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, -2);
    }

    /**
     *
     * @param string $timeStr
     *
     * @return string|boolean
     */
    public function convertDPDTime($timeStr)
    {
        if (!is_string($timeStr) || strlen($timeStr) != 6) {
            return false;
        }

        return substr($timeStr, 0, 2) . ':' . substr($timeStr, 2, 2) . ':' . substr($timeStr, -2);
    }


    /**
     *
     * @return array|boolean
     */
    public function getPickupAddress()
    {
        $address = array();
        $address[Zitec_Dpd_Api_Pickup_Create::NAME] = $this->_getPickupAddressConfig("name");
        $address[Zitec_Dpd_Api_Pickup_Create::ADDITIONAL_NAME] = $this->_getPickupAddressConfig("additionalname", false);
        $address[Zitec_Dpd_Api_Pickup_Create::COUNTRY_CODE] = $this->_getPickupAddressConfig("country");
        $address[Zitec_Dpd_Api_Pickup_Create::CITY] = $this->_getPickupAddressConfig("city");
        $address[Zitec_Dpd_Api_Pickup_Create::STREET] = $this->_getPickupAddressConfig("street");
        $address[Zitec_Dpd_Api_Pickup_Create::POSTCODE] = $this->_getPickupAddressConfig("postcode");
        $address[Zitec_Dpd_Api_Pickup_Create::PHONE] = $this->_getPickupAddressConfig("phone");
        $address[Zitec_Dpd_Api_Pickup_Create::EMAIL] = $this->_getPickupAddressConfig("email");

        return !in_array(false, $address, true) ? $address : false;
    }

    /**
     *
     * @param string  $field
     * @param boolean $mandatory
     *
     * @return string|false
     */

    protected function _getPickupAddressConfig($field, $mandatory = true)
    {
        $value = Mage::getStoreConfig("shipping/zitec_pickupaddress/$field");

        return $value || !$mandatory ? $value : false;
    }

    /**
     * @param $urlType
     *
     * @return mixed|string
     */
    protected function _getWsUrl($urlType)
    {
        $wsCountry = $this->_getConfigData("wscountry");
        $mode = $this->_getHelper()->getConfigData("mode") ? "production" : "test";
        if ($wsCountry && $wsCountry != "zOther") {
            $url = $this->_getConfigData("countryurls/$wsCountry/$mode");
        } elseif ($wsCountry == "zOther") {
            $url = $this->_getConfigData("wsurl$mode");
            $url = ($url && substr($url, -1) != '/') ? "$url/" : $url;
        } else {
            return "";
        }
        if (!$url) {
            return "";
        }

        $urlServicePart = $this->_getConfigData("{$urlType}Part");
        $url = $url . $urlServicePart;

        return $url;
    }

    /**
     *
     * @param string $countryId
     *
     * @return boolean
     */
    public function hasWsUrls($countryId)
    {
        return $this->hasWsProductionUrl($countryId) || $this->hasWsTestUrl($countryId);
    }

    /**
     *
     * @param string $countryId
     *
     * @return boolean
     */
    public function hasWsTestUrl($countryId)
    {
        return $this->_getConfigData("countryurls/$countryId/test") ? true : false;
    }

    /**
     *
     * @param string $countryId
     *
     * @return boolean
     */
    public function hasWsProductionUrl($countryId)
    {
        return $this->_getConfigData("countryurls/$countryId/production") ? true : false;
    }
}