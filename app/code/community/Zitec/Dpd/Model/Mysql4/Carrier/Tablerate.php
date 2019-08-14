<?php
/**
 * Zitec_Dpd – shipping carrier extension
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

/**
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Model_Mysql4_Carrier_Tablerate extends Mage_Core_Model_Mysql4_Abstract
{

    const CSV_COL_IDX_COUNTRY_ID = 0;
    const CSV_COL_IDX_REGION = 1;
    const CSV_COL_IDX_ZIP = 2;
    const CSV_COL_IDX_WEIGHT = 3;
    const CSV_COL_IDX_PRICE = 4;
    const CSV_COL_IDX_METHOD = 5;
    const CSV_COL_IDX_COD_SURCHARGE = 6;
    const CSV_COL_IDX_COD_MIN_SURCHARGE = 7;
    const CSV_COL_IDX_COD_PRICE_VS_DEST = 8;


    const CSV_COL_COUNT = 9;


    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = array();

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = array();

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     * [country_id][default_name] = region_id
     *
     * @var array
     */
    protected $_importRegions;


    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = array();

    /**
     * List of valid method codes
     *
     * @var array
     */
    protected $_validMethods = null;

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('zitec_dpd/carrier_tablerate', 'pk');
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array|false
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':website_id' => (int)$request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id'  => $request->getDestRegionId(),
            ':postcode'   => $request->getDestPostcode(),
            ':weight'     => (float)$request->getPackageWeight(),
            ':price'      => (float)$request->getData('zitec_table_price')
        );
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('website_id=:website_id')
            ->order(array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'method DESC', 'price_vs_dest DESC', 'weight DESC'));


        // render destination condition
        $orWhere = '(' . implode(') OR (', array(
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",
            )) . ')';
        $select->where($orWhere);
        $select->where('((weight <= :weight and price_vs_dest = 0) or (weight <= :price and price_vs_dest = 1))');
        $rates = $adapter->fetchAll($select, $bind);

        if (empty($rates)) {
            $rates = Mage::getModel('zitec_dpd/config_source_service')->getDefaultShipingRates();
        }

        return $rates;
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     *
     * @throws Mage_Core_Exception
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object, $csvFile = null)
    {
        $this->_log(__METHOD__);

        if (!$csvFile && !empty($_FILES['groups']['tmp_name']['zitec_dpd']['fields']['import']['value'])) {
            $csvFile = $_FILES['groups']['tmp_name']['zitec_dpd']['fields']['import']['value'];
        }
        if (!$csvFile) {
            return $this;
        }


        $website = Mage::app()->getWebsite($object->getScopeId());

        $this->_importWebsiteId  = (int)$website->getId();
        $this->_importUniqueHash = array();
        $this->_importErrors     = array();
        $this->_importedRows     = 0;

        $io   = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < self::CSV_COL_COUNT) {
            $io->streamClose();
            Mage::throwException($this->_getHelper()->__('Invalid Table Rates File Format'));
        }


        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 1;
            $importData = array();

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website and condition name
            $condition = array(
                'website_id = ?' => $this->_importWebsiteId,
            );
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber++;
                if (is_array($csvLine)) {
                    foreach ($csvLine as &$cell) {
                        $cell = filter_var($cell, FILTER_SANITIZE_STRING);
                    }
                }

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            $this->_log($e->getMessage());
            Mage::throwException($this->_getHelper()->__('An error occurred while import table rates.'));
        }

        $adapter->commit();

        if ($this->_importErrors) {
            array_unshift($this->_importErrors, "");
            $error = $this->_getHelper()->__('%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s', $this->_importedRows, implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Load directory countries
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _loadDirectoryCountries()
    {
        if (!is_null($this->_importIso2Countries) && !is_null($this->_importIso3Countries)) {
            return $this;
        }

        $this->_importIso2Countries = array();
        $this->_importIso3Countries = array();

        /** @var $collection Mage_Directory_Model_Mysql4_Country_Collection */
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection->getData() as $row) {
            $this->_importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->_importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }

    /**
     * Load directory regions
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _loadDirectoryRegions()
    {
        if (!is_null($this->_importRegions)) {
            return $this;
        }

        $this->_importRegions = array();

        /** @var $collection Mage_Directory_Model_Mysql4_Region_Collection */
        $collection = Mage::getResourceModel('directory/region_collection');
        foreach ($collection->getData() as $row) {
            $this->_importRegions[$row['country_id']][$row['code']]         = (int)$row['region_id'];
            $this->_importRegions[$row['country_id']][$row['default_name']] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * Return import condition full name by condition name code
     *
     * @return string
     */
    protected function _getConditionFullName($conditionName)
    {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name                                      = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int   $rowNumber
     *
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < self::CSV_COL_COUNT) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid Table Rates format in the Row #%s', $rowNumber);

            return false;
        }
        // validate country
        if (isset($this->_importIso2Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]])) {
            $countryId = $this->_importIso2Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]];
        } else if (isset($this->_importIso3Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]])) {
            $countryId = $this->_importIso3Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]];
        } else if ($row[self::CSV_COL_IDX_COUNTRY_ID] == '*' || $row[self::CSV_COL_IDX_COUNTRY_ID] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid Country "%s" in the Row #%s.', $row[self::CSV_COL_IDX_COUNTRY_ID], $rowNumber);

            return false;
        }

        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[self::CSV_COL_IDX_REGION]])) {
            $regionId = $this->_importRegions[$countryId][$row[self::CSV_COL_IDX_REGION]];
        } else if ($row[self::CSV_COL_IDX_REGION] == '*' || $row[self::CSV_COL_IDX_REGION] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid Region/State "%s" in the Row #%s.', $row[self::CSV_COL_IDX_REGION], $rowNumber);

            return false;
        }

        // detect zip code
        if ($row[self::CSV_COL_IDX_ZIP] == '*' || $row[self::CSV_COL_IDX_ZIP] == '') {
            $zipCode = '';
        } else {
            $zipCode = $row[self::CSV_COL_IDX_ZIP];
        }

        // Validar el peso/precio
        $weight = $this->_parseDecimalValue($row[self::CSV_COL_IDX_WEIGHT]);
        if ($weight === false || $weight < 0) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid weight/price "%s" in the Row #%s.', $weight, $rowNumber);

            return false;
        }

        // validate price
        $price      = null;
        $markupType = null;
        $priceInput = $row[self::CSV_COL_IDX_PRICE];
        if (!$this->_parsePricePercentage($priceInput, $price, $markupType)) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid price/percentage/addition "%s" in the Row #%s.', $priceInput, $rowNumber);

            return false;
        }

        $method = trim($row[self::CSV_COL_IDX_METHOD]);
        if (!$this->_validateMethod($method)) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid service "%s" in the Row #%s.', $method, $rowNumber);

            return false;
        }

        $cashondelivery_surcharge = $this->_parseCashOnDeliverySurcharge($row[self::CSV_COL_IDX_COD_SURCHARGE]);
        if ($cashondelivery_surcharge === false) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid COD Surcharge "%s" in the Row #%s.', $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);

            return false;
        }

        // Validar el sobrecargo contrareembolso mínimo.
        $minCodSurcharge = $this->_parseMinCodSurcharge($row[self::CSV_COL_IDX_COD_MIN_SURCHARGE], $cashondelivery_surcharge);
        if ($minCodSurcharge === false) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid Minimum COD Surcharge "%s" in the Row #%s. The minimum COD surcharge must be greater or equal to zero, and can only be used where the Cash On Delivery Surcharge is specified as a percentage.', $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);

            return false;
        }

        $priceVsDest = $row[self::CSV_COL_IDX_COD_PRICE_VS_DEST] ? $row[self::CSV_COL_IDX_COD_PRICE_VS_DEST] : '0';
        if (array_search($priceVsDest, array('0', '1')) === false) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid value Price vs Dest value "%s" in the Row #%s. The value should be 0 (Weight vs Dest) or 1 (Price vs Dest).', $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);

            return false;
        }

        $this->_log("[$countryId] [$regionId] [$zipCode] [$weight] [$priceInput] [$method] [$cashondelivery_surcharge] [$minCodSurcharge] [$priceVsDest]");

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F-%d-%d", $countryId, $regionId, $zipCode, $weight, $method, $priceVsDest);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = $this->_getHelper()->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s", Weight/Price "%s", Method "%s", Price vs Dest "%s").', $rowNumber, $row[self::CSV_COL_IDX_COUNTRY_ID], $row[self::CSV_COL_IDX_REGION], $zipCode, $weight, $method);

            return false;
        }
        $this->_importUniqueHash[$hash] = true;


        return array(
            $this->_importWebsiteId, // website_id
            $countryId, // dest_country_id
            $regionId, // dest_region_id,
            $zipCode, // dest_zip
            $weight, // weight
            $price, // price - percentage, addition, or fixed
            $method,      // method
            $markupType,// markup_type,
            $cashondelivery_surcharge,
            $minCodSurcharge,
            $priceVsDest
        );
    }


    /**
     *
     * @param type   $value
     * @param string $value
     * @param string $cashondelivery_surcharge
     *
     * @return boolean
     */
    protected function _parseMinCodSurcharge($value, $cashondelivery_surcharge)
    {
        if (empty($value)) {
            return null;
        }

        $minCodSurcharge = $this->_parseDecimalValue($value, 2);
        if ($minCodSurcharge === false || $minCodSurcharge < 0) {
            return false;
        }

        if ($this->_getHelper()->parsePercentageValueAsFraction($cashondelivery_surcharge) === false) {
            return false;
        }

        return $minCodSurcharge;
    }


    /**
     * Save import data batch
     *
     * @param array $data
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip',
                'weight', 'price', 'method', "markup_type", "cashondelivery_surcharge", "cod_min_surcharge", "price_vs_dest");
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Parse and validate decimal value
     * Return false if value is not decimal
     *
     * @param string $value
     *
     * @return bool|float
     */
    protected function _parseDecimalValue($value, $precision = 4)
    {
        $value = trim($value);
        if (!is_numeric($value)) {
            return false;
        }
        $value = (float)sprintf("%.{$precision}F", $value);

        return $value;
    }

    /**
     * Parse the price/percentage column into a decimal value and a flag
     * which indicates whether the value is a percentage.
     *
     * @param string  $value
     * @param float   &$decimalPart
     * @param boolean $markupType
     *
     * @return float|boolean
     */
    protected function _parsePricePercentage($value, &$decimalPart, &$markupType)
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }
        $identifier = '';
        switch (substr($value, -1)) {
            case '#': {
                $markupType = 0;
                $identifier = '#';
                break;
            }
            case '%': {
                $markupType = 1;
                $identifier = '%';
                break;
            }
            case '+': {
                $markupType = 2;
                $identifier = '+';
                break;
            }
            default: {
            if (is_numeric(substr($value, -1)) && is_numeric($value)) {
                $markupType = 0;
                $identifier = '';

                break;
            }

            return false;
            }
        }

        if ($value == '#') {
            $decimalPart = 0;
            $markupType  = 0;

            return true;
        }
        if (strlen($identifier) == 0) {
            $decimalPart = $value;
        } else {
            $decimalPart = substr($value, 0, strlen($value) - 1);
        }
        $decimalPart = $this->_parseDecimalValue($decimalPart);
        if ($decimalPart === false) {
            return false;
        }
        if (!$markupType && $decimalPart < 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the method code specified is valid
     *
     * @param string $method
     *
     * @return boolean
     */
    protected function _validateMethod($method)
    {
        if (!is_array($this->_validMethods)) {
            $this->_validMethods = array_keys(Mage::getModel('zitec_dpd/config_source_service')->toOptionArray());
        }

        return in_array($method, $this->_validMethods);
    }

    /**
     * Return CashOnDelivery Surcharge Value
     *
     * @param Varien_Object
     *
     * @return float
     */
    public function getCashOnDeliverySurcharge(Varien_Object $request)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':website_id' => (int)$request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id'  => $request->getDestRegionId(),
            ':postcode'   => $request->getDestPostcode(),
            ':weight'     => (float)$request->getPackageWeight(),
            ':price'      => (float)$request->getData('zitec_table_price'),
            ':method'     => $request->getMethod()
        );

        $select = $adapter->select()
            ->from($this->getMainTable(), array('cashondelivery_surcharge', 'cod_min_surcharge'))
            ->where('website_id=:website_id')
            ->order(array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'method DESC', 'price_vs_dest DESC', 'weight DESC'));


        // render destination condition
        $orWhere = '(' . implode(') OR (', array(
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",
            )) . ')';

        $select->where($orWhere);


        $select->where('((weight <= :weight and price_vs_dest = 0) or (weight <= :price and price_vs_dest = 1))');
        $select->where('method = :method');

        $rate = $adapter->fetchRow($select, $bind);

        if (empty($rate) && $this->isRateDefinedForMethod($request)) {
            $rate = null;
        }

        return $rate;
    }


    /**
     * find if there is a rate defined for method in the table rate
     *
     * @param $request
     *
     * @return bool
     */
    public function isRateDefinedForMethod($request)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':method' => $request->getMethod()
        );

        $select = $adapter->select()
            ->from($this->getMainTable(), array('count(*)'));

        $select->where('method = :method');

        $rate = $adapter->fetchOne($select, $bind);
        if ($rate) {
            return true;
        }

        return false;
    }


    /**
     * obtain the cash on delivery tax amounth
     *
     * @param mixed $value
     *
     * @return string|null|boolean
     */
    protected function _parseCashOnDeliverySurcharge($value)
    {
        if (!isset($value)) {
            return null;
        }
        $value = trim(strval($value));
        if ($value === "") {
            return null;
        }

        $asDecimal = $this->_parseDecimalValue($value);
        if ($asDecimal !== false) {
            if ($asDecimal >= 0) {
                return $value;
            } else {
                return false;
            }
        }

        if ($this->_getHelper()->parsePercentageValueAsFraction($value) !== false) {
            return $value;
        } else {
            return false;
        }
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }

    /**
     *
     * @param string $message
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _log($message)
    {
        return $this->_getHelper()->log($message);
    }


}