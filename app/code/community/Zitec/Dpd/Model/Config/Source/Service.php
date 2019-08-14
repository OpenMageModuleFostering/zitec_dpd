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
class Zitec_Dpd_Model_Config_Source_Service extends Zitec_Dpd_Model_Config_Source_Abstract
{

    public function toOptionArray($isMultiselect = false)
    {

        $services = array(
            '1'   => $this->__('DPD Classic'),
            '10'  => $this->__('DPD 10:00'),
            '9'   => $this->__('DPD 12:00'),
            '109' => $this->__('DPD B2C'),
            '27'  => $this->__('DPD Same Day'),
            '40033'  => $this->__('DPD Classic International'),
            '40107'  => $this->__('DPD Classic Bulgaria')
        );

        if ($isMultiselect) {
            $options = array();
            foreach ($services as $k => $v) {
                $options[] = array('label' => $k . ' - ' . $v, 'value' => $k);
            }

            return $options;
        } else {
            return $services;
        }
    }

    /**
     * return default values for all shipping service of dpd carrier to force using
     * api response amount for enabled services
     *
     * @return array
     */
    public function getDefaultShipingRates()
    {
        $rates = array();
        foreach ($this->toOptionArray() as $key => $value) {
            $rates[] = array(
                //'pk'=>14,
                'website_id'               => null,//1
                'dest_country_id'          => 0,
                'dest_region_id'           => 0,
                'dest_zip'                 => '',
                'weight'                   => 0,
                'price'                    => 0,
                'method'                   => $key,
                'markup_type'              => 1,
                'cod_option'               => 3,
                'cashondelivery_surcharge' => "0%",
                'cod_min_surcharge'        => null,
                'price_vs_dest'            => 0,
            );
        }

        return $rates;

    }


    /**
     * return the payment rate with default values
     * it will return an array forcing the payment to be calculated as a percentage
     *
     * @return array
     */
    public function getDefaultPaymentRate()
    {
        $rate = array(
            'website_id'               => null,
            'dest_country_id'          => 0,
            'dest_region_id'           => 0,
            'dest_zip'                 => '',
            'weight'                   => 0,
            'price'                    => 0,
            'markup_type'              => 1,
            'cod_option'               => 3,
            'cashondelivery_surcharge' => "0%",
            'cod_min_surcharge'        => null,
            'price_vs_dest'            => 0,
        );

        return $rate;

    }


}


