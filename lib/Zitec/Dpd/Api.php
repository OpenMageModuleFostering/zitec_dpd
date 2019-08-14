<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api
 *
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api
{

    const API_VERSION = 1;


    protected $_parameters;
    protected $_method;

    protected $_methodObject;

    protected static $_instance;


    public function __construct()
    {
        $parameters = func_get_args();

        if (count($parameters) == 0) {
            return;
        }
        foreach ($parameters as $param) {
            if (is_string($param)) {
                $this->_method = $param;
            }
            if (is_array($param)) {
                $this->_parameters = $param;
                $this->_method = $this->_parameters['method'];
                unset($this->_parameters['method']);
            }
        }

        $this->__init();
    }


    public function __init()
    {
        if (empty($this->_method) || empty($this->_parameters) || !is_array($this->_parameters)) {
            throw new Exception('DPD API - wrong parameters set for calling the api', 110);
        }

        $className = Zitec_Dpd_Api_Configs::getClassNameForMethod($this->_method);

        if (!empty($className)) {
            $this->_methodObject = new $className($this->_parameters);
        } else {
            throw new Exception('DPD API - Invalid method called', 111);
        }

    }


    public function __invoke()
    {
        return $this->getApiMethodObject();
    }


    /**
     * @return Zitec_Dpd_Api_Abstract
     */
    public function getApiMethodObject()
    {
        return $this->_methodObject;
    }

    /**
     * Singleton pattern implementation
     *
     * @return Varien_Autoload
     */
    static public function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new Zitec_Dpd_Api();
        }

        return self::$_instance;
    }

    /**
     * Register SPL autoload function
     */
    static public function autoloadRegister()
    {
        spl_autoload_register(array(self::instance(), 'autoload'));
    }


    /**
     * Load class source code
     *
     * @param string $class
     */
    public function autoload($class)
    {
        $baseDir = dirname(dirname(dirname(__FILE__)));

        $classFile = $baseDir . DIRECTORY_SEPARATOR . str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
        $classFile .= '.php';

        return include $classFile;
    }


}


Zitec_Dpd_Api::autoloadRegister();