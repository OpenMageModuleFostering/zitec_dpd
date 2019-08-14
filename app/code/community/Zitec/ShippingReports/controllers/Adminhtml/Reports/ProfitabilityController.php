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
class Zitec_ShippingReports_Adminhtml_Reports_ProfitabilityController extends Zitec_ReportsCommon_Controller_Adminhtml_RecordsreportController
{

    protected function _construct()
    {
        $this->_exportFileName = 'zitec_shippingreports_price_vs_cost';
        $this->_gridBlock      = 'zitec_shippingreports/adminhtml_profitability_grid';
    }


}

