<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Configs
 * contains various needed constants and configs
 * it is used also in autoloading class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Configs
{

    const URL = 'url';
    const CONNECTION_TIMEOUT = 'connectionTimeout';

    const WS_USER_NAME = "wsUserName";
    const WS_PASSWORD = "wsPassword";
    const WS_LANG = "wsLang";
    const WS_LANG_EN = "EN";
    const APPLICATION_TYPE = "applicationType";
    const APPLICATION_TYPE_DEFAULT = 9;

    const PAYER_ID = "payerId";
    const SENDER_ADDRESS_ID = "senderAddressId";

    const PRICE_OPTION = "priceOption";
    const PRICE_OPTION_WITHOUT_PRICE = "WithoutPrice";

    const SHIPMENT_LIST = "shipmentList";
    const SHIPMENT_LIST_SHIPMENT_ID = "shipmentId";
    const SHIPMENT_LIST_REFERENCE_NUMBER = "shipmentReferenceNumber";
    const SHIPMENT_LIST_RECEIVER_NAME = "receiverName";
    const SHIPMENT_LIST_RECEIVER_FIRM_NAME = "receiverFirmName";
    const SHIPMENT_LIST_RECEIVER_COUNTRY_CODE = "receiverCountryCode";
    const SHIPMENT_LIST_RECEIVER_ZIP_CODE = "receiverZipCode";
    const SHIPMENT_LIST_RECEIVER_CITY = "receiverCity";
    const SHIPMENT_LIST_RECEIVER_STREET = "receiverStreet";
    const SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH = 70;
    const SHIPMENT_LIST_RECEIVER_HOUSE_NO = "receiverHouseNo";
    const SHIPMENT_LIST_RECEIVER_PHONE_NO = "receiverPhoneNo";
    const SHIPMENT_LIST_MAIN_SERVICE_CODE = "mainServiceCode";
    const SHIPMENT_LIST_ADDITIONAL_SERVICES = "additionalServices";

    const ADDITIONAL_SERVICES_COD = "cod";
    const ADDITIONAL_INSURANCE  = "highInsurance";
    const COD_AMOUNT = "amount";
    const COD_CURRENCY = "currency";
    const COD_PAYMENT_TYPE = "paymentType";
    CONST INSURANCE_GOODS_VALUE = 'goodsValue';
    CONST INSURANCE_CURRENCY = 'currency';
    CONST INSURANCE_CONTENT = 'content';


    const PARCELS = "parcels";
    const PARCELS_PARCEL_ID = "parcelId";
    const PARCELS_PARCEL_NO = "parcelNo";
    const PARCELS_PARCEL_REFERENCE_NUMBER = "parcelReferenceNumber";
    const PARCELS_DIMENSION_HEIGHT = "dimensionsHeight";
    const PARCELS_DIMENSION_WIDTH = "dimensionsWidth";
    const PARCELS_DIMENSION_LENGTH = "dimensionsLength";
    const PARCELS_WEIGHT = "weight";
    const PARCELS_DESCRIPTION = "description";

    const PRINT_OPTION = 'printOption';
    const PRINT_OPTION_PDF = 'Pdf';

    const PAYMENT_TYPE_CASH = 'Cash';
    const PAYMENT_TYPE_CREDIT_CARD = "CreditCard";
    const PAYMENT_TYPE_CROSSED_CHECK = "CrossedCheck";

    const PAYMENT_AMOUNT_TYPE_FIXED = 1;
    const PAYMENT_AMOUNT_TYPE_PERCENTAGE = 2;

    const METHOD_CALCULATE_PRICE = "calculatePrice";
    const METHOD_SHIPMENT_CALCULATE_PRICE = "calculatePrice";
    const METHOD_SHIPMENT_GET_LABEL = "getShipmentLabel";
    const METHOD_SHIPMENT_GET_SHIPMENT_STATUS = "getShipmentStatus";
    const METHOD_SHIPMENT_SAVE = "createShipment";
    const METHOD_SHIPMENT_DELETE = "deleteShipment";

    const METHOD_CREATE_SHIPMENT = "createShipment";
    const METHOD_UPDATE_SHIPMENT = "updateShipment";

    const METHOD_PICKUP_CREATE = "createPickupOrder";

    const METHOD_MANIFEST_CLOSE = "closeManifest";
    const TRACKING_URL_TEMPLATE = "https://tracking.dpd.de/cgi-bin/delistrack?typ=1&lang=ro&pknr=%s&var=internalNewSearch&x=4&y=13";


    protected static $_availableClassMethods = array(
        self::METHOD_SHIPMENT_CALCULATE_PRICE     => 'Zitec_Dpd_Api_Shipment_CalculatePrice',
        self::METHOD_SHIPMENT_GET_LABEL           => 'Zitec_Dpd_Api_Shipment_GetLabel',
        self::METHOD_SHIPMENT_GET_SHIPMENT_STATUS => 'Zitec_Dpd_Api_Shipment_GetShipmentStatus',
        self::METHOD_SHIPMENT_SAVE                => 'Zitec_Dpd_Api_Shipment_Save',
        self::METHOD_SHIPMENT_DELETE              => 'Zitec_Dpd_Api_Shipment_Delete',


        self::METHOD_PICKUP_CREATE                => 'Zitec_Dpd_Api_Pickup_Create',

        self::METHOD_MANIFEST_CLOSE               => 'Zitec_Dpd_Api_Manifest_Close',
    );


    /**
     * @param $method
     */
    public static function getClassNameForMethod($method)
    {
        if (!empty(self::$_availableClassMethods[$method])) {
            return self::$_availableClassMethods[$method];
        }

        return false;
    }







}


