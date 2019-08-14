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
class Zitec_Dpd_Model_Mysql4_Carrier_Tablerate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

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
     * Define resource model and item
     *
     */
    protected function _construct()
    {
        $this->_init('zitec_dpd/shipping_carrier_dpd', 'zitec_dpd/carrier_tablerate');
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

        $this->_select
            ->columns(array('shipping_price' => new Zend_Db_Expr("IF(main_table.markup_type = '1' OR main_table.markup_type = '2', IF(main_table.markup_type = '1',CONCAT(main_table.price, '%'), CONCAT(main_table.price,'+')), CONCAT(main_table.price,'#'))")))
            ->joinLeft(
                array('country_table' => $this->_countryTable), 'country_table.country_id = main_table.dest_country_id', array('dest_country' => 'iso3_code'))
            ->joinLeft(
                array('region_table' => $this->_regionTable), 'region_table.region_id = main_table.dest_region_id', array('dest_region' => 'code'));

        $this->addOrder('dest_country', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region', self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip', self::SORT_ORDER_ASC);
    }

    /**
     * Add website filter to collection
     *
     * @param int $websiteId
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
     */
    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    /**
     * Add condition name (code) filter to collection
     *
     * @param string $conditionName
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
     */
    public function setConditionFilter($conditionName)
    {
        return $this->addFieldToFilter('condition_name', $conditionName);
    }

    /**
     * Add country filter to collection
     *
     * @param string $countryId
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
     */
    public function setCountryFilter($countryId)
    {
        return $this->addFieldToFilter('dest_country_id', $countryId);
    }

}
