<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_GetLabel_Response
 * getLabel method will respond with this object
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_GetLabel_Response extends Zitec_Dpd_Api_Shipment_Response
{

    /**
     *
     * @return string
     */
    public function getPdfFile()
    {
        return $this->_getResponseProperty('result/pdfFile');
    }
}


