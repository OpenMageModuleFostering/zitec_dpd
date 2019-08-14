<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Pickup_Create_Response
 * Customize here all pickup related response
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Pickup_Create_Response extends Zitec_Dpd_Api_Pickup_Response
{

    /**
     *
     * @return string
     */
    protected function _getErrorObjectPath()
    {
        return "result/resultList/error";
    }

    /**
     *
     * @return string
     */
    public function getReferenceNumber()
    {
        return $this->_getResponseProperty("result/resultList/pickupOrderReference/referenceNumber");
    }

    /**
     *
     * @return string
     */
    public function getDpdId()
    {
        return $this->_getResponseProperty("result/resultList/pickupOrderReference/id");
    }
}


