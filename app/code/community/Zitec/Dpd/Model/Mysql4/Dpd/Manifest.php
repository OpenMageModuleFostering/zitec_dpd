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
class Zitec_Dpd_Model_Mysql4_Dpd_Manifest extends Mage_Core_Model_Mysql4_Abstract
{


    protected function _construct()
    {
        $this->_init('zitec_dpd/zitec_dpd_manifest', 'manifest_id');
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Zitec_Dpd_Model_Mysql4_Dpd_Manifest
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $result = parent::_afterSave($object);

        $manifest = $object;
        /* @var $manifest Zitec_Dpd_Model_Dpd_Manifest */
        foreach ($manifest->getShipsForManifest() as $ship) {
            /* @var $ship Zitec_Dpd_Model_Dpd_Ship */
            $ship->setManifestId($manifest->getId());
            $ship->save();
        }

        return $result;
    }


}


