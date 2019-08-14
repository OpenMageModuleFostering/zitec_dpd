<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Pickup_Create
 * Customize here pickup create request
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Pickup_Create extends Zitec_Dpd_Api_Pickup
{

    const PICKUP_ORDER_LIST = "pickupOrderList";
    const REFERENCE_NUMBER = "referenceNumber";

    const NAME = "contactName";
    const ADDITIONAL_NAME = "senderAddress/additionalName";
    const COUNTRY_CODE = "senderAddress/countryCode";
    const CITY = "senderAddress/city";
    const STREET = "senderAddress/street";
    const POSTCODE = "senderAddress/zipCode";
    const PHONE = "contactPhone";
    const EMAIL = "contactEmail";

    const PIECES = "pieces";
    const PIECES_SERVICE_CODE = "serviceCode";
    const PIECES_QUANTITY = "quantity";
    const PIECES_WEIGHT = "weight";
    const PIECES_DESTINATION_COUNTRY_CODE = "destinationCountryCode";


    /**
     *
     * @return string
     */
    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_PICKUP_CREATE;
    }



    protected function _init()
    {
        parent::_init();

        $this->setPickupOrderList(Zitec_Dpd_Api_Configs::PAYER_ID, $this->_payerId);
        $this->setPickupOrderList(Zitec_Dpd_Api_Configs::SENDER_ADDRESS_ID, $this->_senderAddressId);


    }

    /**
     *
     * @param string $tag
     * @param mixed  $value
     *
     * @return Zitec_Dpd_Api_Pickup_Create
     */
    public function setPickupOrderList($tag, $value)
    {

        return $this->_setData(self::PICKUP_ORDER_LIST . "/" . $tag, $value);
    }

    /**
     *
     * @param string $date (format YYYYMMDD)
     * @param type   $from (format HHMMSS)
     * @param type   $to   (format HHMMSS)
     *
     * @return \Zitec_Dpd_Api_Pickup_Create
     */
    public function setPickupTime($date, $from, $to)
    {
        $this->setPickupOrderList("date", $date);
        $this->setPickupOrderList("fromTime", $from);
        $this->setPickupOrderList("toTime", $to);
        $referenceNumber = "$date/$from/" . strtoupper(substr(sha1(uniqid(rand(), true)), 0, 5));
        $this->setPickupOrderList(self::REFERENCE_NUMBER, $referenceNumber);

        return $this;
    }

    /**
     *
     * @param string $instruction
     *
     * @return Zitec_Dpd_Api_Pickup_Create
     */
    public function setSpecialInstruction($instruction)
    {
        return $this->setPickupOrderList("specialInstruction", $instruction);
    }

    /**
     *
     * @param array $pickupAddress
     *
     * @return \Zitec_Dpd_Api_Pickup_Create
     */
    public function setPickupAddress(array $pickupAddress)
    {
        foreach ($pickupAddress as $tag => $value) {
            $this->setPickupOrderList($tag, $value);
        }

        return $this;
    }

    /**
     *
     * @param string $serviceCode
     * @param int    $quantity
     * @param float  $weight
     * @param string $destinationCountryCode
     *
     * @return \Zitec_Dpd_Api_Pickup_Create
     */
    public function addPieces($serviceCode, $quantity, $weight, $destinationCountryCode)
    {
        $pieces = array(
            self::PIECES_SERVICE_CODE             => $serviceCode,
            self::PIECES_QUANTITY                 => $quantity,
            self::PIECES_WEIGHT                   => $weight,
            self::PIECES_DESTINATION_COUNTRY_CODE => $destinationCountryCode
        );
        $this->setPickupOrderList(self::PIECES, $pieces);

        return $this;
    }

    /**
     *
     * @return Zitec_Dpd_Api_Pickup_Create_Response
     */
    public function getCreatePickupResponse()
    {
        return $this->getResponse();
    }

    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Pickup_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Pickup_Create_Response($response);
    }


}


