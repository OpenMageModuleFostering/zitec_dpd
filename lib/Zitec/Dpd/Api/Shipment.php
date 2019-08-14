<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment
 *
 * customize api call for shipment entity processing
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
abstract class Zitec_Dpd_Api_Shipment extends Zitec_Dpd_Api_Abstract
{


    protected function _init()
    {
        parent::_init();
        $this->_setData(Zitec_Dpd_Api_Configs::WS_LANG, Zitec_Dpd_Api_Configs::WS_LANG_EN);
        $this->_setData(Zitec_Dpd_Api_Configs::APPLICATION_TYPE, Zitec_Dpd_Api_Configs::APPLICATION_TYPE_DEFAULT);
    }




}


