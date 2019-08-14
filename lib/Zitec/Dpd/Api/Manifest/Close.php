<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Manifest_Close
 *
 * Use this class to call close manifest api method
 *
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Manifest_Close extends Zitec_Dpd_Api_Manifest
{

    const MANIFEST = 'manifest';
    const SHIPMENT_REFERENCE_LIST = "shipmentReferenceList";
    const SHIPMENT_REFERENCE_ID = "id";
    const SHIPMENT_REFERENCE_NUMBER = "referenceNumber";
    const MANIFEST_PRINT_OPTION = "manifestPrintOption";
    const MANIFEST_PRINT_OPTION_MANIFEST_ONLY = "PrintOnlyManifest";
    const MANIFEST_PRINT_OPTION_WITH_UNPRINTED = "PrintManifestWithUnprintedParcels";
    const PRINT_OPTION = "printOption";
    const PRINT_OPTION_PDF = "Pdf";



    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_MANIFEST_CLOSE ;
    }


    protected function _init()
    {
        parent::_init();
        $this->_setData(Zitec_Dpd_Api_Configs::WS_LANG, Zitec_Dpd_Api_Configs::WS_LANG_EN);
        $this->_setData(Zitec_Dpd_Api_Configs::APPLICATION_TYPE, Zitec_Dpd_Api_Configs::APPLICATION_TYPE_DEFAULT);

        $this->_setData(self::MANIFEST_PRINT_OPTION, self::MANIFEST_PRINT_OPTION_WITH_UNPRINTED);
        $this->_setData(self::PRINT_OPTION, self::PRINT_OPTION_PDF);
    }


    /**
     *
     * @param string $referenceNumber
     *
     * @return Zitec_Dpd_Api_Manifest_Close
     */
    public function setManifestReferenceNumber($referenceNumber)
    {
        return $this->setManifest("manifestReferenceNumber", $referenceNumber);
    }


    /**
     *
     * @return string
     */
    public function getManifestReferenceNumber()
    {
        return $this->_getData(array(self::MANIFEST, "manifestReferenceNumber"));
    }


    /**
     *
     * @param string $notes
     *
     * @return Zitec_Dpd_Api_Manifest_Close
     */
    public function setManifestNotes($notes)
    {
        return $this->setManifest("manifestNotes", $notes);
    }


    /**
     *
     * @param string $tag
     * @param string $value
     *
     * @return Zitec_Dpd_Api_Manifest_Close
     */
    public function setManifest($tag, $value)
    {
        return $this->_setData(array(self::MANIFEST, $tag), $value);
    }


    /**
     *
     * @param int    $dpdShipmentId
     * @param string $dpdShipmentReferenceNumber
     *
     * @return Zitec_Dpd_Api_Manifest_Close
     */
    public function addShipment($dpdShipmentId, $dpdShipmentReferenceNumber)
    {
        $shipments =  $this->_getData(array(self::MANIFEST,self::SHIPMENT_REFERENCE_LIST));
        if(empty($shipments)){
            return $this->_setData(
                array(
                    self::MANIFEST,
                    self::SHIPMENT_REFERENCE_LIST),
                array(
                    array(
                        self::SHIPMENT_REFERENCE_ID     => $dpdShipmentId,
                        self::SHIPMENT_REFERENCE_NUMBER => $dpdShipmentReferenceNumber
                    )
                )
            );
        } else {
            return $this->_setData(
                array(
                    self::MANIFEST,
                    self::SHIPMENT_REFERENCE_LIST),
                array(
                    self::SHIPMENT_REFERENCE_ID     => $dpdShipmentId,
                    self::SHIPMENT_REFERENCE_NUMBER => $dpdShipmentReferenceNumber
                )
            );
        }

    }

    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Manifest_Close_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Manifest_Close_Response($response);
    }

    /**
     *
     * @return Zitec_Dpd_Api_Manifest_Close_Response
     */
    public function getCloseManifestResponse()
    {
        return $this->getResponse();
    }



}


