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
class Zitec_TableRates_Block_Adminhtml_Widget_Grid_Column_Filter_Region
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    /**
     * Get condition
     *
     * @return array
     */
    public function getCondition()
    {
        $value = trim($this->getValue());
        if ($value == '*') {
            return array('null' => true);
        } else {
            return array('like' => '%' . $this->_escapeValue($this->getValue()) . '%');
        }
    }
}
