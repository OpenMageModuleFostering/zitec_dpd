<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Abstract
 * This class will be extended to obtain fully functional api call
 *
 * put api parameters on constructor
 * overwrite getMethod method - to return the called action on the api
 * call execute
 * read the response with getResponse method as object
 *
 * surround the call of init and execute function with a try catch
 * it may throw some exception to be processed
 *
 * Error codes
 * 100   DPD Api - No method specified:
 * 101   Soap client problems, no api url specified
 * 102   DPD API -  Incorrect parameters supplied
 * 103   DPD API -  Incorrect supplied parameter path
 *
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
abstract class Zitec_Dpd_Api_Abstract
{

    protected $_url = null;
    protected $_connectionTimeout = null;
    protected $_wsUserName = null;
    protected $_wsPassword = null;
    protected $_payerId = null;
    protected $_senderAddressId = null;

    protected $_lastResponse = null;
    protected $_lastRequest = null;

    protected $_data = array();

    protected $_method = null;

    protected $_response = null;


    public function __construct(array $connectionParams)
    {
        $this->_data = $connectionParams;

        /**
         * initialize all others data members
         */
        $this->_init();
    }

    /**
     *
     * @throws Exception
     */
    protected function _init()
    {
        /**
         * set main service code
         * allowed
         * 1 - DPD Classic
         * 10 - DPD 10:00
         * 9 - DPD 12:00
         * 109 - DPD B2C
         * 27 - DPD Same Day
         *          */
        if (isset($this->_data[Zitec_Dpd_Api_Configs::SHIPMENT_LIST_MAIN_SERVICE_CODE])) {
            $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_MAIN_SERVICE_CODE, $this->_data[Zitec_Dpd_Api_Configs::SHIPMENT_LIST_MAIN_SERVICE_CODE]);
        }

        //conection details
        $this->_url               = $this->_data[Zitec_Dpd_Api_Configs::URL];
        $this->_connectionTimeout = isset($this->_data[Zitec_Dpd_Api_Configs::CONNECTION_TIMEOUT]) ? $this->_data[Zitec_Dpd_Api_Configs::CONNECTION_TIMEOUT] : 10;
        $this->_wsUserName        = $this->_data[Zitec_Dpd_Api_Configs::WS_USER_NAME];
        $this->_wsPassword        = $this->_data[Zitec_Dpd_Api_Configs::WS_PASSWORD];
        $this->_method            = $this->_getMethod();

        //delivery details
        $this->_senderAddressId = $this->_data[Zitec_Dpd_Api_Configs::SENDER_ADDRESS_ID];
        $this->_payerId         = $this->_data[Zitec_Dpd_Api_Configs::PAYER_ID];


        $this->_setData(Zitec_Dpd_Api_Configs::WS_USER_NAME, $this->_wsUserName);
        $this->_setData(Zitec_Dpd_Api_Configs::WS_PASSWORD, $this->_wsPassword);
    }


    /**
     * it is used to identify the method will be called on api
     *
     * @return string
     */
    protected abstract function _getMethod();


    /**
     * instantiate the standard soap client for PHP 5.3 >
     * or instantiate the custom soap client
     *
     * @return \SoapClient
     * @throws Exception
     */
    protected function _getSoapClient()
    {

        try {
            if (version_compare(phpversion(), '5.3.0', '<') === true) {
                $soapClient = new Zitec_Dpd_Api_Soapclient($this->_url, array('soap_version' => SOAP_1_1, 'encoding' => 'UTF-8', 'cache_wsdl' => WSDL_CACHE_NONE, "trace" => true));
            } else {
                $soapClient = new SoapClient($this->_url, array('soap_version' => SOAP_1_1, 'encoding' => 'UTF-8', 'cache_wsdl' => WSDL_CACHE_NONE, "trace" => true));
            }

            return $soapClient;
        }   catch (SoapFault $e) {
            throw new Exception($e->getMessage(), 101);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 101);
        }
    }


    /**
     * execute the soap call with the existing param on $_data at url $this->_url
     *
     * @param string $method
     * @param array  $data
     *
     * @return Zitec_Dpd_Api_Response
     * @throws Exception
     */
    public function execute($method = null)
    {
        if (!$method) {
            $method = $this->_method;
        }

        if (!$method) {
            throw new Exception("DPD Api - No method specified: " . __FUNCTION__, 100);
        }

        $this->_beforeExecute();

        $e        = null;
        $response = null;

        $soapClient = $this->_getSoapClient();

        $response = $soapClient->__soapCall($method, array($this->_data));

        $this->_lastRequest  = $soapClient->__getLastRequest();
        $this->_lastResponse = $soapClient->__getLastResponse();

        $this->_response = $this->_createResponse($response);

        $this->_afterExecute();

        return $this->_response;
    }


    /**
     *
     * @param mixed $path
     * @param mixed $value
     * @param array $data
     *
     * @throws Exception
     * @return Zitec_Dpd_Api_Abstract
     */
    protected function _setData($path, $value, &$data = null)
    {
        if (!$path) {
            throw new Exception("DPD API -  Incorrect supplied parameter path", 102);
        }

        if (!isset($data)) {
            return $this->_setData($path, $value, $this->_data);
        }

        if (!is_array($path)) {
            $path = explode('/', $path);
        }


        if (count($path) == 1) {
            if (array_key_exists($path[0], $data)) {
                if (is_array($data[$path[0]]) && $value != null) {
                    $pathKeys = array_keys($data[$path[0]]);
                    if (count($pathKeys) > 0 && $pathKeys[0] == "0") {
                        $data[$path[0]][] = $value;
                    } else {
                        $currentValue   = $data[$path[0]];
                        $data[$path[0]] = array_merge($currentValue, $value);
                    }
                } else {
                    $data[$path[0]] = $value;
                }
            } else {
                $data[$path[0]] = $value;
            }
        } else {
            $pathEl = array_shift($path);
            if (!array_key_exists($pathEl, $data)) {
                $data[$pathEl] = array();
            }

            return $this->_setData($path, $value, $data[$pathEl]);
        }

        return $this;

    }


    /**
     *
     * @param mixed $path
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    protected function _getData($path, $data = null)
    {
        if (!$path) {
            throw new Exception("DPD API -  Incorrect supplied parameter path" , 103);
        }

        if (!isset($data)) {
            $data = $this->_data;
        }

        if (!is_array($path)) {
            $path = array($path);
        }

        $pathEl = array_shift($path);
        if (array_key_exists($pathEl, $data)) {
            if (count($path) == 0) {
                return $data[$pathEl];
            } else {
                return $this->_getData($path, $data[$pathEl]);
            }
        } else {
            return null;
        }

    }





    /**
     *
     * @param string|array $path
     *
     * @return int
     */
    protected function _count($path)
    {
        $value = $this->_getData($path);
        if (!$value) {
            return 0;
        } elseif (is_array($value)) {
            $keys = array_keys($value);
            if (count($keys) > 0 && $keys[0] == "0") {
                return count($value);
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }



    /**
     * create a response object and fill it with data
     *
     * @return Zitec_Dpd_Api_Response
     */
    protected abstract function _createResponse(stdClass $response);



    /**
     * return the response of the last call to the api
     *
     * @return stdClass
     */
    public function getResponse()
    {
        return $this->_response;
    }


    protected function _afterExecute()
    {
        return $this;
    }

    protected function _beforeExecute()
    {
        return $this;
    }


    /**
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }


    /**
     *
     * @param string $tag
     * @param mixed  $value
     *
     * @return Zitec_Dpd_Api_Shipment_Save
     */
    public function addAdditionalServices($tag, $value)
    {
        return $this->setShipmentList(Zitec_Dpd_Api_Configs::SHIPMENT_LIST_ADDITIONAL_SERVICES, array($tag => $value));
    }


    /**
     * set the insurance value on each package on the delivery
     *
     * @param $amount
     * @param $currency
     * @param $description
     */
    public function setAdditionalHighInsurance($amount, $currency, $description)
    {

        $this->addAdditionalServices(Zitec_Dpd_Api_Configs::ADDITIONAL_INSURANCE,
            array(Zitec_Dpd_Api_Configs::INSURANCE_GOODS_VALUE => $amount,
                  Zitec_Dpd_Api_Configs::INSURANCE_CURRENCY    => $currency,
                  Zitec_Dpd_Api_Configs::INSURANCE_CONTENT     => $description));
        return $this;
    }




}
