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
class Zitec_Dpd_Model_Observer_Payment
{


    /**
     * force collect totals on checkout if the payment method is DPD
     *
     * @param Varien_Event_Observer $observer
     */
    public function setTotalsCollectedFlag(Varien_Event_Observer $observer)
    {
        $input = $observer->getEvent()->getInput();
        if ($input->getMethod() == Mage::helper('zitec_dpd')->getDpdPaymentCode()) {
            Mage::getModel('checkout/cart')->getQuote()->setTotalsCollectedFlag(false);
        }
    }

    /**
     * force collect html for review order in admin order create
     *
     * @param Varien_Event_Observer $observer
     */
    public function refreshTotalsInAdminOrderCreate(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequestModel();
        $payment = $request->getParam('payment');
        if (!empty($payment)) {
            $block  = $request->getParam('block');
            $blocks = explode(',', $block);
            if (!in_array('totals', $blocks)) {
                $request->setParam('block', $request->getParam('block') . ',totals');
            }
        }
    }


}