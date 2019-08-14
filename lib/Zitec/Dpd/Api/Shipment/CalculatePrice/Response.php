<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Shipment_Calculateprice_Response
 * calculate price method will have this response - contain all customization fot needed to this method
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Shipment_Calculateprice_Response extends Zitec_Dpd_Api_Shipment_Response
{

    /**
     *
     * @param string $name
     *
     * @return stdClass
     */
    protected function _getPriceAttribute($name)
    {
        $price = $this->_getResponseProperty('result/priceList/price');

        return $price !== false ? $this->_getResponseProperty($name, $price) : false;
    }

    /**
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_getPriceAttribute("amount");
    }

    public function getVatAmount()
    {
        return $this->_getPriceAttribute("vatAmount");
    }

    /**
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->_getPriceAttribute("totalAmount");
    }

    /**
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getPriceAttribute("currency");
    }

    /**
     *
     * @return float
     */
    public function getAmountLocal()
    {
        return $this->_getPriceAttribute("amountLocal");
    }

    /**
     *
     * @return float
     */
    public function getVatAmountLocal()
    {
        return $this->_getPriceAttribute("vatAmountLocal");
    }

    /**
     *
     * @return float
     */
    public function getTotalAmountLocal()
    {
        return $this->_getPriceAttribute("totalAmountLocal");
    }

    /**
     *
     * @return string
     */
    public function getCurrencyLocal()
    {
        return $this->_getPriceAttribute("currencyLocal");
    }

    /**
     *
     * @return string
     */
    protected function _getErrorObjectPath()
    {
        return "result/priceList/error";
    }

}


