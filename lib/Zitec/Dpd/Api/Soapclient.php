<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Soapclient
 *
 * use this SOAP client if you are using PHP 5.3<
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Soapclient extends SoapClient
{

    /**
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl, $options)
    {
        $url = parse_url($wsdl);
        if ($url['port']) {
            $this->_port = $url['port'];
        }

        return parent::__construct($wsdl, $options);
    }



    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int    $version
     * @param int    $one_way
     *
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $parts = parse_url($location);
        if ($this->_port) {
            $parts['port'] = $this->_port;
        }
        $location = $this->buildLocation($parts);

        $return = parent::__doRequest($request, $location, $action, $version, $one_way);

        return $return;
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    public function buildLocation($parts = array())
    {
        $location = '';

        if (isset($parts['scheme'])) {
            $location .= $parts['scheme'] . '://';
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            $location .= $parts['user'] . ':' . $parts['pass'] . '@';
        }
        $location .= $parts['host'];
        if (isset($parts['port'])) {
            $location .= ':' . $parts['port'];
        }
        $location .= $parts['path'];
        if (isset($parts['query'])) {
            $location .= '?' . $parts['query'];
        }

        return $location;
    }


}


