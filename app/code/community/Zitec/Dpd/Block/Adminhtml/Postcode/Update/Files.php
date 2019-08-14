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
class Zitec_Dpd_Block_Adminhtml_Postcode_Update_Files extends Mage_Adminhtml_Block_Widget
{
    public function __construct(){
        $this->setTemplate('zitec_dpd/potcode/update/available-files.phtml');
    }

    /**
     * return all csv files in the predefined path
     * @return array
     */
    public function getAvailableCsvFiles()
    {
        $path = Mage::helper('zitec_dpd/postcode_search')->getPathToDatabaseUpgradeFiles();
        $files = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if($entry=='.' || $entry == '..'){
                    continue;
                }
                $ext = pathinfo($entry, PATHINFO_EXTENSION);
                if(strtolower($ext) !== 'csv'){
                    continue;
                }
                $files[$entry] = filemtime($path.$entry);
            }
            closedir($handle);
        }
        asort ($files);
        return $files;

    }
}


