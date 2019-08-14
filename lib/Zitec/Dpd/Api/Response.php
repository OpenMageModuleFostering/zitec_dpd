<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Response
 * All api response will extend this class
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Response
{

    /**
     *
     * @var stdClass
     */
    protected $_response;

    /**
     *
     * @param stdClass $response
     */
    public function __construct(stdClass $response)
    {
        $this->_response = $response;
    }

    /**
     *
     * @return stdClass
     */
    protected function _getResponse()
    {
        return $this->_response;
    }

    /**
     *
     * @param string   $path
     * @param stdClass $responseObject
     *
     * @return mixed
     * @throws Exception
     */
    protected function _getResponseProperty($path, $responseObject = null)
    {
        if (!$path) {
            return NULL;
        }

        if (!is_array($path)) {
            $path = explode("/", $path);
        }

        if (!$responseObject) {
            $responseObject = $this->_response;
        }

        if (!$responseObject) {
            return NULL;
        }

        if (!($responseObject instanceof stdClass)) {
            return NULL;
        }

        $property = $path[0];
        if (property_exists($responseObject, $path[0])) {
            $responseObject = $responseObject->$property;
        } else {
            return false;
        }

        if (count($path) == 1) {
            return $responseObject;
        } else {
            array_shift($path);

            return $this->_getResponseProperty($path, $responseObject);
        }


    }

    /**
     *
     * @return stdClass|false
     */
    protected function _getErrorObject()
    {
        return $this->_getResponseProperty($this->_getErrorObjectPath());
    }

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
     * @return boolean
     */
    public function hasError()
    {
        return $this->_getErrorObject() instanceof stdClass;
    }

    /**
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->_getResponseProperty($this->_getErrorObjectPath() . '/code');
    }

    /**
     *
     * @return string
     */
    public function getErrorText()
    {
        return $this->_getResponseProperty($this->_getErrorObjectPath() . '/text');
    }


}


