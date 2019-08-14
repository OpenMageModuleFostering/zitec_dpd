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
class Zitec_Dpd_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{

    public function exportTableratesAction()
    {
        $fileName  = 'dpd_tablerates.csv';
        $gridBlock = $this->getLayout()->createBlock('zitec_dpd/adminhtml_tablerate_grid');
        $website   = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        $gridBlock->setWebsiteId($website->getId());
        $content = $gridBlock->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

}


