<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response
 * getShipmentsStatus will return with this object
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response extends Zitec_Dpd_Api_Shipment_Response
{

    /**
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function _getStatusInfoAttribute($name)
    {
        $statusInfo = $this->_getResponseProperty('result/statusInfoList/statusInfo');

        return $statusInfo !== false ? $this->_getResponseProperty($name, $statusInfo) : false;
    }


    /**
     *
     * @return string
     */
    protected function _getErrorObjectPath()
    {
        return "result/statusInfoList/error";
    }

    /**
     *
     * @return string
     */
    public function getShipDate()
    {
        $shipDate = $this->_getStatusInfoAttribute("shipDate");
        $shipDate = $this->convertDPDDate($shipDate);

        return $shipDate ? $shipDate : '';
    }

    /**
     *
     * @return string
     */
    public function getShipTime()
    {
        $shipTime = $this->_getStatusInfoAttribute("shipTime");
        $shipTime = $this->convertDPDTime($shipTime);

        return $shipTime ? $shipTime : '';
    }

    /**
     *
     * @return string
     */
    public function getDeliveryDate()
    {
        $deliveryDate = $this->_getStatusInfoAttribute("deliveryDate");
        $deliveryDate = $this->convertDPDDate($deliveryDate);

        return $deliveryDate ? $deliveryDate : '';
    }

    /**
     *
     * @return string
     */
    public function getDeliveryTime()
    {
        $deliveryTime = $this->_getStatusInfoAttribute("deliveryTime");
        $deliveryTime = $this->convertDPDTime($deliveryTime);

        return $deliveryTime ? $deliveryTime : '';
    }

    /**
     *
     * @return string
     */
    public function getServiceCode()
    {
        $serviceCode = $this->_getStatusInfoAttribute("serviceCode");

        return $serviceCode;
    }

    /**
     *
     * @return string
     */
    public function getServiceDescription()
    {
        return $this->_getStatusInfoAttribute("serviceDescription");
    }

    /**
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->_getStatusInfoAttribute("weight");
    }

    /**
     *
     * @return string
     */
    public function getDpdUrl()
    {
        return $this->_getStatusInfoAttribute("dpdUrl");
    }


    public function getTrackingUrl(){
        $parcelNo = $this->getParcelNumber();
        return sprintf(Zitec_Dpd_Api_Configs::TRACKING_URL_TEMPLATE ,$parcelNo);
    }

    /**
     *
     * @return string
     */
    public function getParcelNumber()
    {
        return $this->_getStatusInfoAttribute("parcelNo");
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



}


