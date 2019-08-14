<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_GetLabel
 * call getLabel method on api using this class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_GetLabel extends Zitec_Dpd_Api_Shipment
{

    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_SHIPMENT_GET_LABEL;
    }

    protected function _init()
    {
        parent::_init();
        $this->_setData(Zitec_Dpd_Api_Configs::PRINT_OPTION, Zitec_Dpd_Api_Configs::PRINT_OPTION_PDF);
    }


    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Shipment_GetLabel_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Shipment_GetLabel_Response($response);
    }

    /**
     *
     * @param type $dpdShipmentId
     * @param type $dpdShipmentReferenceNumber
     *
     * @return \Zitec_Dpd_Api_Shipment_Getlabel
     */
    public function setShipment($dpdShipmentId, $dpdShipmentReferenceNumber)
    {
        $this->_setData('shipmentReferenceList/id', $dpdShipmentId);
        $this->_setData('shipmentReferenceList/referenceNumber', $dpdShipmentReferenceNumber);

        return $this;
    }

    /**
     * @param array $data
     *
     * @throws Exception
     * @return  \Zitec_Dpd_Api_Shipment_Getlabel
     */
    public function getShipmentLabels()
    {

        try {
            $response = $this->_soapCall();

        } catch (Exception $e) {
            throw $e;
        }

        return $response;
    }


}


