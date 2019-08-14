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
class Zitec_PrintShippingLabel_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{


    public function printshippinglabelsAction()
    {
        if (Mage::helper('zitec_dpd/compatibility')->checkMassPrintShippingLabelExists()) {
            $module     = (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
            $controller = 'sales_order_shipment';
            $action     = 'massPrintShippingLabel';
        } else {
            $module     = 'zitec_printshippinglabel';
            $controller = 'adminhtml_index';
            $action     = 'massPrintShippingLabel';
        }

        $orderId    = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $this->getRequest()->setPost('order_ids', array($orderId));
            $this->getRequest()->setParam('massaction_prepare_key', 'order_ids');
            $this->_forward($action, $controller, $module);

            return;
        }

        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $this->getRequest()->setPost('shipment_ids', array($shipmentId));
            $this->getRequest()->setParam('massaction_prepare_key', 'shipment_ids');
            $this->_forward($action, $controller, $module);

            return;
        }
        $this->_redirect('adminhtml/sales_order/index');
    }


    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @return null
     */
    public function massPrintShippingLabelAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('order_ids');
        $createdFromOrders = !empty($ids);
        $shipments = null;
        $labelsContent = array();
        switch ($request->getParam('massaction_prepare_key')) {
            case 'shipment_ids':
                $ids = $request->getParam('shipment_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->addFieldToFilter('entity_id', array('in' => $ids));
                }
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->setOrderFilter(array('in' => $ids));
                }
                break;
        }

        if ($shipments && $shipments->getSize()) {
            foreach ($shipments as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
            }
        }

        if (!empty($labelsContent)) {
            $outputPdf = $this->_combineLabelsPdf($labelsContent);
            $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf->render(), 'application/pdf');
            return;
        }

        if ($createdFromOrders) {
            $this->_getSession()
                ->addError(Mage::helper('sales')->__('There are no shipping labels related to selected orders.'));
            $this->_redirect('*/sales_order/index');
        } else {
            $this->_getSession()
                ->addError(Mage::helper('sales')->__('There are no shipping labels related to selected shipments.'));
            $this->_redirect('*/sales_order_shipment/index');
        }
    }


    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }



}

