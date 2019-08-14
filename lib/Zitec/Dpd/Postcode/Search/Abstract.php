<?php
/**
 * Zitec_Dpd – shipping carrier extension - postcode validation
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @copyright  Copyright (c) 2014 Zitec COM
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class Zitec_Dpd_Postcode_Search_Abstract
{

    const MYSQL_ADAPTER = 'mysql';
    const SQLITE_ADAPTER = 'sqlite';

    const ADDRESS_FIELD_CITY = 'city';
    const ADDRESS_FIELD_COUNTRY = 'country';
    const ADDRESS_FIELD_REGION = 'region';
    const ADDRESS_FIELD_ADDRESS = 'address';
    const ADDRESS_FIELD_POSTCODE = 'postcode';

    const SEARCH_APPLY_SIMILARITY_MAX_THRESHOLD = 100;
    const SEARCH_CAN_RETURN_RANDOM_VALUES = 1;
    const SEARCH_RESULT_RELEVANCE_THRESHOLD_FOR_VALIDATION = 84;

    const SEARCH_APPLY_SIMILARITY_MIN_THRESHOLD = 2;

    const SEARCH_APPLY_SIMILARITY_CITY_PERCENTAGE_THRESHOLD = 60;

    const SEARCH_HOUSE_NUMBER_IDENTIFIER_CAN_SKIP_WORDS = 1;

    //constants used to calibrate house number mapping in address
    //is used to define the comparison threshold
    const SEARCH_HOUSE_NUMBER_CONSTANT1 = 2;
    //is used to increase the results mapping house numbers
    const SEARCH_HOUSE_NUMBER_CONSTANT2 = 5;


    protected $_searchModel;
    protected $_connection;

    protected static $_instance;
    protected static $base_path;





    /**
     * filter input address by search model function
     * @param $array
     */
    protected function  filterAddressInput($array)
    {
        if(is_array($array)){
            foreach($array as $key => &$value){
                $value = $this->applyInputFilter($key, $value);
            }
        }
        return $array;
    }

    /**
     * you can customize here how input filters will be applied
     *
     * default is applied the search model filter
     * @param $key
     * @param $value
     */
    public function applyInputFilter($key, $value){

        return  $this->_searchModel->applyFiltersForAddress( $value );
    }







    /**
     * Singleton pattern implementation
     *
     * @return Varien_Autoload
     */
    static public function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new Zitec_Dpd_Postcode_Search();
        }

        return self::$_instance;
    }

    /**
     * Register SPL autoload function
     */
    static public function autoloadRegister()
    {
        spl_autoload_register(array('Zitec_Dpd_Postcode_Search', 'autoload'));
    }


    public function getModel(){
        return $this->_searchModel;
    }

    /**
     * Load class source code
     *
     * @param string $class
     */
    public static function autoload($class)
    {
        $baseDir                              = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        Zitec_Dpd_Postcode_Search::$base_path = $baseDir;
        $classFile                            = $baseDir . DIRECTORY_SEPARATOR . str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
        $classFile .= '.php';

        return include $classFile;
    }

    public static function getBasePath()
    {
        if(empty(self::$base_path)){
            $baseDir                              = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
            Zitec_Dpd_Postcode_Search::$base_path = $baseDir;
        }
        return self::$base_path;
    }

} 