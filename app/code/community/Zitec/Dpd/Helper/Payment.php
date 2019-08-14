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
 * this class is used for
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Helper_Payment extends Mage_Core_Helper_Abstract
{


    public function getWebsiteId()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
            $store        = $sessionQuote->getStore();
            if (empty($store)) {
                return 0;
            }
            $webSiteId = $store->getWebsiteId();

            return $webSiteId;
        }

        $webSiteId = Mage::app()->getStore()->getWebsiteId();

        return $webSiteId;
    }



    public function getQuote(){
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
           return Mage::getSingleton('checkout/session')->getQuote();
        }
    }

}