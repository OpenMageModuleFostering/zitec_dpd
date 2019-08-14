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
class Zitec_ShippingReports_Block_Adminhtml_Profitability_Renderer_Orderref extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $link = Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
        $html = '<a href="' . $link . '">' . $row->getIncrementId() . '</a>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        return $row->getIncrementId();
    }
}

