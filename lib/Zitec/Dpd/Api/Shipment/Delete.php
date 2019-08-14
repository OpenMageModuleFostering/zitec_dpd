<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_Delete
 * Delete shipment api method will be available using this class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_Delete extends Zitec_Dpd_Api_Shipment
{


    /**
     *
     * @return string
     */
    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_DELETE;
    }


    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Shipment_Delete_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Shipment_Delete_Response($response);
    }

    /**
     *
     * @param type $dpdShipmentId
     * @param type $dpdShipmentReferenceNumber
     *
     * @return \Zitec_Dpd_Api_Shipment_Getlabel
     */
    public function addShipmentReference($dpdShipmentId, $dpdShipmentReferenceNumber)
    {
        $this->_setData('shipmentReferenceList/id', $dpdShipmentId);
        $this->_setData('shipmentReferenceList/referenceNumber', $dpdShipmentReferenceNumber);

        return $this;
    }

    /**
     *
     * @return Zitec_Dpd_Api_Shipment_Delete_Response
     */
    public function getDeleteShipmentResponse()
    {
        return $this->_response;
    }
}


