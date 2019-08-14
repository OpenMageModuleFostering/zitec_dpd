<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_Save_Response
 *
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_Save_Response extends Zitec_Dpd_Api_Shipment_Response
{

    /**
     *
     * @return string
     */
    public function getDpdShipmentReferenceNumber()
    {
        return $this->_getResponseProperty('result/resultList/shipmentReference/referenceNumber');
    }

    /**
     *
     * @return string
     */
    public function getDpdShipmentId()
    {
        return $this->_getResponseProperty('result/resultList/shipmentReference/id');
    }

    /**
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_getResponseProperty("result/resultList/message");
    }

    /**
     *
     * @return type
     */
    public function getParcelRefsIds()
    {
        $parcels = $this->_getResponseProperty('result/resultList/parcelResultList');
        if (!$parcels) {
            return array();
        }

        if (!is_array($parcels)) {
            $parcels = array($parcels);
        }

        $parcelRefsIds = array();
        foreach ($parcels as $parcel) {
            $parcelRefNumber                 = $this->_getResponseProperty('parcelReferenceNumber', $parcel);
            $parcelId                        = $this->_getResponseProperty('parcelId', $parcel);
            $parcelRefsIds[$parcelRefNumber] = $parcelId;
        }

        return $parcelRefsIds;
    }
}


