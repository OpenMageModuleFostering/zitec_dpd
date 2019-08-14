<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_CalculatePrice
 * Calculate price method is called using this class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_CalculatePrice extends Zitec_Dpd_Api_Shipment
{

    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_CALCULATE_PRICE;
    }

    protected function _init()
    {
        parent::_init();

        $this->setShipmentList(Zitec_Dpd_Api_Configs::PAYER_ID, $this->_payerId);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SENDER_ADDRESS_ID, $this->_senderAddressId);

        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_REFERENCE_NUMBER, null);

    }


    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Shipment_CalculatePrice_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Shipment_CalculatePrice_Response($response);
    }


    /**
     *
     * @return Zitec_Dpd_Api_Shipment_CalculatePrice_Response
     */
    public function getCalculatePriceResponse()
    {
        return $this->_response;
    }

    /**
     *
     * @param string $tag
     * @param mixed  $value
     *
     * @return Zitec_Dpd_Api_Shipment_Calculateprice
     */
    public function setShipmentList($tag, $value)
    {
        return $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, $tag), $value);
    }


    /**
     *
     * @param Mage_Sales_Model_Order_Address $shippingAddress
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function setReceiverAddress($street, $city, $postcode, $countryId)
    {
        $processedStreet = substr(implode(explode("\n", $street)), 0, Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH - 1);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_NAME, "DPD Price Calculation");
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_FIRM_NAME, null);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_COUNTRY_CODE, $countryId);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_ZIP_CODE, $postcode);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_CITY, $city);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET, $processedStreet);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_HOUSE_NO, null);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_PHONE_NO, null);

        return $this;
    }

    /**
     *
     * @param string $serviceCode
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function setShipmentServiceCode($serviceCode)
    {
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_MAIN_SERVICE_CODE, $serviceCode);

        return $this;
    }


    /**
     *
     * @param float $weight
     *
     * @return \Zitec_Dpd_Api_Shipment_Calculateprice
     */
    public function addParcel($weight)
    {
        $parcel = array(
            Zitec_Dpd_Api_Configs::PARCELS_WEIGHT                  => $weight,
            Zitec_Dpd_Api_Configs::PARCELS_PARCEL_REFERENCE_NUMBER => null
        );
        $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS), $parcel);

        return $this;
    }

}


