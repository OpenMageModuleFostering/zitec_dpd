<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Pickup_Response
 * Customize here all pickup related response
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_Save extends Zitec_Dpd_Api_Shipment
{


    /**
     *
     * @return string
     */
    protected function _getMethod()
    {
        return Zitec_Dpd_Api_Configs::METHOD_CREATE_SHIPMENT;
    }


    protected function _init()
    {
        parent::_init();
        $this->_setData(Zitec_Dpd_Api_Configs::PRICE_OPTION, Zitec_Dpd_Api_Configs::PRICE_OPTION_WITHOUT_PRICE);

        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_SHIPMENT_ID, null);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::PAYER_ID, $this->_payerId);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SENDER_ADDRESS_ID, $this->_senderAddressId);

    }


    /**
     *
     * @param string $tag
     * @param        string mixed
     *
     * @return Zitec_Dpd_Api_Shipment_Save
     */
    public function setShipmentList($tag, $value)
    {
        return $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, $tag), $value);
    }

    /**
     *
     * @param string $tag
     *
     * @return string
     */
    public function getShipmentList($tag)
    {
        return $this->_getData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, $tag));
    }


    /**
     *
     * @param Mage_Sales_Model_Order_Address $shippingAddress
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function setReceiverAddress(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_NAME, $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname());
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_FIRM_NAME, $shippingAddress->getCompany());
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_COUNTRY_CODE, $shippingAddress->getCountryId());
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_ZIP_CODE, $shippingAddress->getPostcode());
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_CITY, $shippingAddress->getCity());
        $street = is_array($shippingAddress->getStreetFull()) ? $shippingAddress->getStreetFull() : explode("\n", $shippingAddress->getStreetFull());
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET, implode(" ", $street));
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_HOUSE_NO, null);
        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_PHONE_NO, $shippingAddress->getTelephone());

        return $this;
    }


    /**
     *
     * @return string
     */
    public function getReceiverCountryCode()
    {
        return $this->getShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_COUNTRY_CODE);
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
     * @return string
     */
    public function getShipmentServiceCode()
    {
        return $this->getShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_MAIN_SERVICE_CODE);
    }

    /**
     *
     * @param string $referenceNumber
     * @param type   $makeUnique
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function setShipmentReferenceNumber($referenceNumber, $makeUnique = true)
    {
        if ($makeUnique) {
            $referenceNumber .= '-' . strtoupper(substr(sha1(uniqid(rand(), true)), 0, 4));
        }

        $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_REFERENCE_NUMBER, $referenceNumber);

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getShipmentReferenceNumber()
    {
        return $this->getShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_REFERENCE_NUMBER);
    }

    /**
     *
     * @param string $referenceNumber
     * @param float  $weight
     * @param string $description
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function addParcel($referenceNumber, $weight, $description)
    {
        $shipmentReferenceNumber = $this->getShipmentReferenceNumber();
        if (!$shipmentReferenceNumber) {
            throw new Exception('DPD API - Shipment has no reference number', 106);
        }
        $parcelRefNo = (string)$referenceNumber . '-' . $shipmentReferenceNumber;
        $parcel      = array(
            Zitec_Dpd_Api_Configs::PARCELS_PARCEL_ID               => null,
            Zitec_Dpd_Api_Configs::PARCELS_PARCEL_NO               => null,
            Zitec_Dpd_Api_Configs::PARCELS_PARCEL_REFERENCE_NUMBER => $parcelRefNo,
            Zitec_Dpd_Api_Configs::PARCELS_DIMENSION_HEIGHT        => null,
            Zitec_Dpd_Api_Configs::PARCELS_DIMENSION_WIDTH         => null,
            Zitec_Dpd_Api_Configs::PARCELS_DIMENSION_LENGTH        => null,
            Zitec_Dpd_Api_Configs::PARCELS_WEIGHT                  => $weight,
            Zitec_Dpd_Api_Configs::PARCELS_DESCRIPTION             => $description
        );
        $parcels = $this->_getData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS));
        if(empty($parcels)) {
            $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS), array($parcel));
        } else {
            $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS), $parcel);
        }

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getParcelCount()
    {
        return $this->_count(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS));
    }

    /**
     *
     * @param string $parcelReferenceNumber
     * @param string $parcelId
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    protected function _setParcelId($parcelReferenceNumber, $parcelId)
    {
        $parcels = $this->_getParcels();
        foreach (array_keys($parcels) as $parcelKey) {
            if ($parcels[$parcelKey][Zitec_Dpd_Api_Configs::PARCELS_PARCEL_REFERENCE_NUMBER] == $parcelReferenceNumber) {
                $parcels[$parcelKey][Zitec_Dpd_Api_Configs::PARCELS_PARCEL_ID] = $parcelId;
                break;
            }
        }
        $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS), null);
        foreach ($parcels as $parcel) {
            $this->_setData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS), $parcel);
        }

        return $this;
    }

    protected function _getParcels()
    {
        $parcels = $this->_getData(array(Zitec_Dpd_Api_Configs::SHIPMENT_LIST, Zitec_Dpd_Api_Configs::PARCELS));
        if ($this->getParcelCount() == 1) {
            $parcels = array($parcels);
        }

        return $parcels;
    }

    /**
     *
     * @return array
     */
    public function getParcelWeights()
    {
        $parcelWeights = array();
        foreach ($this->_getParcels() as $parcel) {
            $parcelWeights[$parcel[Zitec_Dpd_Api_Configs::PARCELS_PARCEL_REFERENCE_NUMBER]] = $parcel[Zitec_Dpd_Api_Configs::PARCELS_WEIGHT];
        }

        return $parcelWeights;
    }

    /**
     *
     * @return float
     */
    public function getTotalWeight()
    {
        return array_sum($this->getParcelWeights());
    }

    /**
     *
     * @param float  $amount
     * @param string $currency
     * @param string $paymentType
     *
     * @return Zitec_Dpd_Api_Shipment_Save
     */
    public function setCashOnDelivery($amount, $currency, $paymentType)
    {
        return $this->addAdditionalServices(Zitec_Dpd_Api_Configs::ADDITIONAL_SERVICES_COD,
            array(Zitec_Dpd_Api_Configs::COD_AMOUNT       => $amount,
                  Zitec_Dpd_Api_Configs::COD_CURRENCY     => $currency,
                  Zitec_Dpd_Api_Configs::COD_PAYMENT_TYPE => $paymentType));
    }


    /**
     *
     * @param stdClass $response
     *
     * @return \Zitec_Dpd_Api_Shipment_Save_Response
     */
    protected function _createResponse(stdClass $response)
    {
        return new Zitec_Dpd_Api_Shipment_Save_Response($response);
    }

    /**
     *
     * @param type $shipmentId
     *
     * @return \Zitec_Dpd_Api_Shipment_Save
     */
    public function setDpdShipmentId($shipmentId)
    {
        $this->setShipmentList('shipmentId', $shipmentId);

        return $this;
    }

    /**
     *
     * @return Zitec_Dpd_Api_Shipment_Save_Response
     */
    public function getShipmentSaveResponse()
    {
        return $this->getResponse();
    }

    protected function _afterExecute()
    {
        $this->setDpdShipmentId($this->getShipmentSaveResponse()->getDpdShipmentId());
        foreach ($this->getShipmentSaveResponse()->getParcelRefsIds() as $parcelRefNo => $parcelId) {
            $this->_setParcelId($parcelRefNo, $parcelId);
        }
        if ($this->_method == Zitec_Dpd_Api_Configs::METHOD_CREATE_SHIPMENT) {
            $this->_method = Zitec_Dpd_Api_Configs::METHOD_UPDATE_SHIPMENT;
        }
    }

    /**
     *
     * @throws Zitec_Dpd_Api_Shipment_Save_Exception_ReceiverAddressTooLong
     */
    protected function _beforeExecute()
    {
        $receiverStreet = $this->getShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET);
        if (strlen($receiverStreet) > Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH) {
            throw new Zitec_Dpd_Api_Shipment_Save_Exception_ReceiverAddressTooLong("Receiver address too long");
        }
    }

}


