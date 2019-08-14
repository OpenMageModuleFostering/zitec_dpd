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
class Zitec_Dpd_Model_Payment_Cashondelivery_Source_Country extends Mage_Adminhtml_Model_System_Config_Source_Country
{

    protected static $_allAllowedCountries = null;

    public function toOptionArray($isMultiselect = false)
    {
        $options = parent::toOptionArray($isMultiselect);
        if (self::getAllAllowedCountries()) {
            foreach ($options as $key => $option) {
                if ($option['value'] && !in_array($option['value'], self::getAllAllowedCountries())) {
                    unset($options[$key]);
                }
            }
        }

        return $options;
    }

    /**
     *
     * @return array
     */
    public static function getAllAllowedCountries()
    {
        if (!is_array(self::$_allAllowedCountries)) {
            $allAllowedCountries = trim(Mage::getStoreConfig("payment/zitec_dpd_cashondelivery/all_allowed_countries"));
            if ($allAllowedCountries) {
                self::$_allAllowedCountries = explode(",", Mage::getStoreConfig("payment/zitec_dpd_cashondelivery/all_allowed_countries"));
            } else {
                self::$_allAllowedCountries = array();
            }
        }

        return self::$_allAllowedCountries;
    }


}


