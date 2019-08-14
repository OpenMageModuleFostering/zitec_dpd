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
 * Parent class drivers reports "Record Report" (showing * rows - eg orders - individual).
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_ReportsCommon_Controller_Adminhtml_RecordsreportController extends Mage_Adminhtml_Controller_Action
{


    protected $_exportFileName = 'Zitec_Report';


    protected $_gridBlock;

    /*
     * The maximum number of days between the date of permit beginning and end.
     * @var int
     */
    protected $_maxDaysInDateRange = 62;


    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    public function indexAction()
    {
        $reportFrom =  Mage::helper('core')->htmlEscape($this->getRequest()->getParam('report_from'));
        $reportTo   =  Mage::helper('core')->htmlEscape($this->getRequest()->getParam('report_to'));

        // We force the user to enter a range of dates when
        // click on 'Show Report'.
        if (!$reportFrom || !$reportTo) {
            if (!is_null($this->getRequest()->getParam('showReport'))) {
                Mage::getSingleton('core/session')->addError(Mage::helper('zitec_reportscommon')->__("Please select a 'from' and 'to' date."));
            }
        }

        /* We confirm that the dates are valid and do not break the established limits. */
        try {
            $from = Mage::app()->getLocale()->date($reportFrom, Zend_Date::DATE_SHORT, null, false);
            $to   = Mage::app()->getLocale()->date($reportTo, Zend_Date::DATE_SHORT, null, false);

            $diff = $to->sub($from)->toValue();
            $days = ceil($diff / 60 / 60 / 24) + 1;

            if ($days > $this->_maxDaysInDateRange) {
                Mage::getSingleton('core/session')
                    ->addError(Mage::helper('zitec_reportscommon')
                        ->__(sprintf("The 'to' date must be at most %d days after the 'from' date.", $this->_maxDaysInDateRange)));
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError(Mage::helper('zitec_reportscommon')->__('Invalid date specified'));
        }


        $this->_initAction()->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = $this->_exportFileName . '.csv';
        $content  = $this->getLayout()->createBlock($this->_gridBlock)
            ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName = $this->_exportFileName . '.xml';
        $content  = $this->getLayout()->createBlock($this->_gridBlock)
            ->getExcel($fileName);
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }


}

