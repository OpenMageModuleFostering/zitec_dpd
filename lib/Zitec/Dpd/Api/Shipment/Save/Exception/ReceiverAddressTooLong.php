<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_Save_Exception_Receiveraddresstoolong
 * We have a custom exception here for save shipment method
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_Save_Exception_ReceiverAddressTooLong extends Exception
{

    /**
     *
     * @return int
     */
    public function getMaxLength()
    {
        return Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH;
    }

}


