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
class Zitec_ShippingReports_Block_Adminhtml_Profitability extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_profitability';
        $this->_blockGroup = 'zitec_shippingreports';
        $this->_headerText = Mage::helper('zitec_reportscommon')->__('Shipping Price vs Cost');
        parent::__construct();
        $this->setTemplate('zitec_reportscommon/records_report_container.phtml');
        $this->addButton('filter_form_submit', array(
            'label'   => Mage::helper('zitec_reportscommon')->__('Show Report'),
            'onclick' => 'filterFormSubmit()'
        ));
        $this->_removeButton('add');
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);

        return $this->getUrl('*/*/*', array('_current' => true));
    }
}
