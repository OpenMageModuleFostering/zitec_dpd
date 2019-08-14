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
class Zitec_Dpd_Model_Config_Data_Abstract extends Mage_Core_Model_Config_Data
{
    /**
     *
     * @return boolean
     */
    protected function _isActive()
    {
        $isActive = $this->_getConfigValue('active');

        return $isActive ? true : false;
    }

    /**
     *
     * @param string $field
     * @param string $group
     *
     * @return string|null
     */
    protected function _getConfigValue($field, $group = 'zitec_dpd')
    {
        $configValueData = $this->getData("groups/{$group}/fields/{$field}");
        if (is_array($configValueData) && array_key_exists('value', $configValueData)) {
            $configValue = $configValueData['value'];

            return $configValue;
        }

        return null;
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
     * @return Zitec_Dpd_Helper_Ws
     */
    protected function _getWsHelper()
    {
        return Mage::helper('zitec_dpd/ws');
    }
}


