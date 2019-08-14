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
 * For columns of type 'currency'. Shown in green if it is positive, and red if negative.
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_ReportsCommon_Block_Renderer_Posnegcurrency extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value          = $row->getData($this->getColumn()->getIndex());
        $formattedValue = Mage::helper('core')->currency($value, true, false);
        $html           = '<span style="color: ' . ($value >= 0 ? 'green' : 'red') . '">' . $formattedValue . '</span>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        $value          = $row->getData($this->getColumn()->getIndex());
        $formattedValue = Mage::helper('core')->currency($value, true, false);

        return $formattedValue;
    }
}

