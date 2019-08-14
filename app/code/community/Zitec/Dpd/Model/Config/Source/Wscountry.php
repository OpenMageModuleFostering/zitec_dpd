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
class Zitec_Dpd_Model_Config_Source_Wscountry extends Zitec_Dpd_Model_Config_Source_Abstract
{

    const WS_COUNTRY_OTHER = 'zOther';

    public function toOptionArray()
    {
        $countryCollection = Mage::getResourceModel('directory/country_collection');
        /* @var $countryCollection Mage_Directory_Model_Resource_Country_Collection */
        $countryOptions = $countryCollection->toOptionArray();


        $countries = array();
        $countries[0]                    = array('value' => 0, 'label' => $this->__('-- Please select an option --'));
        foreach ($countryOptions as $option) {
            if(!empty($option['label']) && !empty($option['value'])) {
                $countries[$option['value']] = array('value' => $option['value'], 'label' => $option['label']);
            }
        }
        $countries[self::WS_COUNTRY_OTHER] = array('value' => self::WS_COUNTRY_OTHER, 'label' => $this->__('Other (enter web service URLs manually)'));

        // Only include the countries that have web service URLs defined.
        $countryIds = array_keys($countries);
        foreach ($countryIds as $countryId) {
            if ($countryId && $countryId != 'zOther' && !$this->_getWsHelper()->hasWsUrls($countryId)) {
                unset($countries[$countryId]);
            }
        }

        return $countries;
    }
}


