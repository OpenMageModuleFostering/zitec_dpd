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
class Zitec_TableRates_Model_Tablerate extends Mage_Core_Model_Abstract
{
    const COD_NOT_AVAILABLE = 0;
    const COD_SURCHARGE_ZERO = 1;
    const COD_SURCHARGE_FIXED = 2;
    const COD_SURCHARGE_PERCENTAGE = 3;

    /**
     *
     * @var array
     */
    protected $_map = null;

    public function _construct()
    {
        parent::_construct();
        $this->_map = Zitec_TableRates_Model_Mysql4_Tablerate::getLogicalDbFieldNamesMap();
        $this->_init('zitec_tablerates/tablerate');
    }

    /**
     *
     * @param string $logicalFieldName
     *
     * @return mixed
     */
    public function getMappedData($logicalFieldName)
    {
        return $this->getData($this->getMappedName($logicalFieldName));
    }

    /**
     *
     * @param string $logicalFieldName
     *
     * @return string
     * @throws Exception
     */
    public function getMappedName($logicalFieldName)
    {
        if (isset($this->_map[$logicalFieldName])) {
            return $this->_map[$logicalFieldName];
        } else {
            throw new Exception("Invalid logical field name $logicalFieldName");
        }
    }

    /**
     *
     * @return int
     */
    public function getCashOnDeliverySurchargeOption()
    {
        if (is_null($this->getMappedData('cashondelivery_surcharge'))) {
            return self::COD_NOT_AVAILABLE;
        } elseif (round($this->getData('cod_surcharge_price'), 2) > 0) {
            return self::COD_SURCHARGE_FIXED;
        } elseif (round($this->getData('cod_surcharge_percentage'), 2) > 0) {
            return self::COD_SURCHARGE_PERCENTAGE;
        } else {
            return self::COD_SURCHARGE_ZERO;
        }
    }


}