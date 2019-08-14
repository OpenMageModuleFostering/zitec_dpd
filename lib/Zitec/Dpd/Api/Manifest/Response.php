<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Manifest_Response
 * Customize here all manifest related response
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Manifest_Response extends Zitec_Dpd_Api_Response
{


    /**
     *
     * @return string
     */
    protected function _getErrorObjectPath()
    {
        return "return/error";
    }
}


