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
class Zitec_TableRates_Model_Mysql4_Tablerate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     *
     * @var array
     */
    protected $_map = null;

    /**
     * directory/country table name
     *
     * @var string
     */
    protected $_countryTable;

    /**
     * directory/country_region table name
     *
     * @var string
     */
    protected $_regionTable;

    /**
     * Constructor
     */

    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_map = Zitec_TableRates_Model_Mysql4_Tablerate::getLogicalDbFieldNamesMap();
        $this->_init('zitec_tablerates/tablerate');
        $this->_countryTable = $this->getTable('directory/country');
        $this->_regionTable  = $this->getTable('directory/country_region');
    }

    /**
     * Initialize select, add country iso3 code and region name
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        Zitec_TableRates_Model_Mysql4_Tablerate::prepareSelectColumns($this->_select);

        $this->_select->joinLeft(
            array('country_table' => $this->_countryTable), "country_table.country_id = main_table.{$this->_map['dest_country_id']}", array('dest_country' => 'iso2_code'))
            ->joinLeft(
                array('region_table' => $this->_regionTable), "region_table.region_id = main_table.{$this->_map['dest_region_id']}", array('dest_region' => 'code', 'dest_region_name' => 'default_name'));

        $select = (string)$this->_select;
        $this->_getHelper()->log($select);
    }


    /**
     *
     * @return Zitec_TableRates_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_tablerates');
    }

}
