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
/**
 * @method  Zitec_Dpd_Model_Dpd_Pickup setReference($reference)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setDpdId($dpdId)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setPickupDate($pickupDate)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setPickupTimeFrom($from)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setPickupTimeTo($to)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setCallData($callData)
 * @method  Zitec_Dpd_Model_Dpd_Pickup setResponseData($responseData)
 */
class Zitec_Dpd_Model_Dpd_Pickup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('zitec_dpd/dpd_pickup');
    }
}


