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
class Zitec_TableRates_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     *
     * @return string
     * @throws Exception
     */
    public function getCarrierCode()
    {
        $carrierCode = Mage::app()->getRequest()->getParam("carrier");
        if (!$carrierCode) {
            throw new Exception("Carrier code not found");
        }

        return $carrierCode;
    }

    /**
     *
     * @param string $field
     *
     * @return string
     */
    public function getCarrierConfig($field)
    {
        $path  = "carriers/{$this->getCarrierCode()}/zitec_tablerates/$field";
        $value = Mage::getStoreConfig($path);

        return $value;
    }

    /**
     *
     * @param string $logicalName
     *
     * @return string
     */
    public function getCarrierConfigDbTableFieldName($logicalName)
    {
        return $this->getCarrierConfig("db_table_field_names/$logicalName");
    }

    /**
     *
     * @return boolean
     */
    public function supportsPriceVsDest()
    {
        return $this->supportsFeature("price_vs_dest");
    }

    /**
     *
     * @return boolean
     */
    public function supportsProduct()
    {
        return $this->_getProductSource() ? true : false;
    }

    /**
     *
     * @return string
     */
    protected function _getProductSource()
    {
        return $this->getCarrierConfig('product_source');
    }

    /**
     *
     * @return boolean
     */
    public function supportsMarkup()
    {
        return $this->supportsFeature("markup_type");
    }

    /**
     *
     * @return boolean
     */
    public function supportsCashOnDelivery()
    {
        return $this->supportsFeature('cash_on_delivery');
    }

    /**
     *
     * @return type
     */
    public function supportsCodMinSurcharge()
    {
        return $this->supportsCashOnDelivery() && $this->supportsFeature('cod_min_surcharge');
    }

    /**
     *
     * @param string $feature
     *
     * @return boolean
     */
    public function supportsFeature($feature)
    {
        return $this->getCarrierConfig("features/$feature") ? true : false;
    }

    /**
     *
     * @return string
     */
    public function getGridTitle()
    {
        $gridTitle = $this->getCarrierConfig("grid_title");

        return $gridTitle ? $this->getCarrierHelper()->__($gridTitle) : $this->__("Zitec Table Rates");
    }

    /**
     *
     * @return array
     */
    public function getMethodOptions()
    {
        return $this->_optionArrayToValueArray("method_source");
    }

    /**
     *
     * @return array
     */
    public function getProductOptions()
    {
        return $this->_optionArrayToValueArray("product_source");
    }

    /**
     *
     * @param string $sourceModel
     *
     * @return type
     */
    protected function _optionArrayToValueArray($sourceConfig)
    {
        $source = $this->getCarrierConfig($sourceConfig);
        if (!$source) {
            $message = __FUNCTION__ . ": $sourceConfig not in carrier's config.xml";
            $this->log($message);
            throw new Exception($message);
        }
        $optionArray = Mage::getModel($source)->toOptionArray(true);
        $options     = array();
        foreach ($optionArray as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    public function getTableratesDbTable()
    {
        return $this->getCarrierConfig("tablerates_db_table");
    }

    /**
     *
     * @param string &$module
     * @param string &$controller
     * @param string &$action
     *
     * @return type
     */
    public function getExportAction(&$module, &$controller, &$action)
    {
        $exportAction = $this->getCarrierConfig('export_action');
        list($module, $controller, $action) = explode("/", $exportAction);

        return $exportAction;
    }

    /**
     *
     * @return boolean
     */
    public function isExportUsingRedirect()
    {
        return $this->getCarrierConfig("export_use_redirect") ? true : false;
    }

    /**
     *
     * @param string &$resourceClass
     * @param string &$method
     *
     * @return boolean
     */
    public function getImportAction(&$resourceClass, &$method)
    {
        $resourceClass = $this->getCarrierConfig("import/resource_class");
        $method        = $this->getCarrierConfig("import/method");

        return true;
    }

    /**
     *
     * @return type
     */
    public function getCarrierHelper()
    {
        return Mage::helper('zitec_dpd');
    }

    /**
     *
     * @param string $message
     *
     * @return \Zitec_TableRates_Helper_Data
     */
    public function log($message)
    {
        Mage::log($message, null, "zitec_tablerates.log");

        return $this;
    }

    /**
     *
     * @param string $errorMessage
     *
     * @return boolean
     */
    public function isMySqlDuplicateKeyErrorMessage($errorMessage)
    {
        return strpos($errorMessage, "SQLSTATE[23000]") !== false;
    }
}

