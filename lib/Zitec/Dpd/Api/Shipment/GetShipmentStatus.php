<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_GetShipmentStatus
 * getShipmentStatus  api method is called using this class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_GetShipmentStatus extends Zitec_Dpd_Api_Shipment
{

    /**
     *
     * @return string
     */
    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_GET_SHIPMENT_STATUS ;
    }

    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response($response);
    }

    /**
     *
     * @param string $dpdShipmentId
     * @param string $dpdShipmentReferenceNumber
     *
     * @return \Zitec_Dpd_Api_Shipment_Getshipmentstatus
     */
    public function setShipment($dpdShipmentId, $dpdShipmentReferenceNumber)
    {
        $this->_setData('shipmentReferenceList/id', $dpdShipmentId);
        $this->_setData('shipmentReferenceList/referenceNumber', $dpdShipmentReferenceNumber);

        return $this;
    }

    /**
     *
     * @return Zitec_Dpd_Api_Shipment_GetShipmentStatus_Response
     */
    public function getShipmentStatusResponse()
    {
        return $this->_response;
    }


}


