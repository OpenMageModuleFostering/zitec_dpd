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
class Zitec_TableRates_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    /**
     *
     * @return array
     */
    protected function _getMap()
    {
        return Zitec_TableRates_Model_Mysql4_Tablerate::getLogicalDbFieldNamesMap();
    }

    protected function _initTablerate($idFieldName = 'tablerate_id')
    {
        $this->_title($this->__('DPD Table Rate'))
            ->_title($this->_getTableRateHelper()->getGridTitle());

        $tablerateId = (int)$this->getRequest()->getParam($idFieldName);

        $tablerate = Mage::getModel('zitec_tablerates/tablerate');

        if ($tablerateId) {
            $tablerate->load($tablerateId);
        }

        Mage::register('tablerate_data', $tablerate);
        Mage::register('current_tablerate', $tablerate);

        return $this;
    }

    /**
     *
     * @return \Zitec_TableRates_Adminhtml_IndexController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu("zitec_dpd/zitec_tablerates/{$this->_getCarrierCode()}");

        return $this;
    }

    /**
     *
     * @return string
     */
    protected function _getCarrierCode()
    {
        return $this->_getTableRateHelper()->getCarrierCode();
    }

    /**
     *
     * @return Zitec_TableRates_Helper_Data
     */
    protected function _getTableRateHelper()
    {
        return Mage::helper('zitec_tablerates');
    }

    public function indexAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $this->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('zitec_tablerates/adminhtml_tablerate_grid')->toHtml()
        );
    }

    public function newAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }
        Mage::getSingleton('adminhtml/session')->setTablerateData(false);
        $this->_forward('edit');
    }

    public function editAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $this->_initTablerate();
        $this->loadLayout();

        /* @var $store Zitec_SeurMfCustom_Model_Tablerate */
        $tablerate = Mage::registry('current_tablerate');

        $this->_title($tablerate->getTablerateId() ? implode(', ', array($tablerate->getDestCountryId(), $tablerate->getDestRegionId(), $tablerate->getDestZip())) : $this->__('New Rate'));

        /**
         * Set active menu item
         */
        $this->_setActiveMenu("zitec_dpd/{$this->_getTableRateHelper()->getCarrierCode()}");

        $this->renderLayout();
    }

    public function importAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $this->loadLayout();

        $this->_title($this->__('Import Rates'));

        /**
         * Set active menu item
         */
        $this->_setActiveMenu("zitec_dpd/{$this->_getTableRateHelper()->getCarrierCode()}");

        $this->renderLayout();
    }

    public function exportAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $this->loadLayout();

        $this->_title($this->__('Export Rates'));

        /**
         * Set active menu item
         */
        $this->_setActiveMenu("zitec_dpd/{$this->_getTableRateHelper()->getCarrierCode()}");

        $this->renderLayout();
    }

    /**
     *
     * @param Zitec_TableRates_Model_Tablerate $tablerate
     * @param array                            $data
     *
     * @return boolean
     */
    protected function _prepareSaveData(Zitec_TableRates_Model_Tablerate $tablerate, array $data)
    {
        if (isset($data['pk']) && !$data['pk']) {
            unset($data['pk']);
        }

        $data['dest_zip'] = isset($data['dest_zip']) && $data['dest_zip'] != '*' ? $data['dest_zip'] : '';

        if (!$this->_getTableRateHelper()->supportsProduct() && isset($data['product'])) {
            unset($data['product']);
        }

        if ($this->_getTableRateHelper()->supportsPriceVsDest()) {
            $data['price_vs_dest'] = isset($data['price_vs_dest']) ? $data['price_vs_dest'] : '0';
        } elseif (isset($data['price_vs_dest'])) {
            unset($data['price_vs_dest']);
        }

        $data['weight_price'] = isset($data['weight_price']) && trim($data['weight_price']) ? $data['weight_price'] : '0';

        if (!isset($data['shipping_method_enabled']) || $data['shipping_method_enabled']) {
            if ($this->_getTableRateHelper()->supportsCashOnDelivery()) {
                if (!isset($data['cod_option'])) {
                    $data['cod_option'] = Zitec_TableRates_Model_Tablerate::COD_NOT_AVAILABLE;
                }
                switch ($data['cod_option']) {
                    case Zitec_TableRates_Model_Tablerate::COD_NOT_AVAILABLE:
                        $data['cashondelivery_surcharge'] = null;
                        if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                            $data['cod_min_surcharge'] = null;
                        }
                        break;
                    case Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_ZERO:
                        $data['cashondelivery_surcharge'] = '0';
                        if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                            $data['cod_min_surcharge'] = null;
                        }
                        break;
                    case Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_FIXED:
                        if (!isset($data['cashondelivery_surcharge']) || !trim($data['cashondelivery_surcharge'])) {
                            $data['cashondelivery_surcharge'] = '0';
                        }
                        if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                            $data['cod_min_surcharge'] = null;
                        }
                        break;
                    case Zitec_TableRates_Model_Tablerate::COD_SURCHARGE_PERCENTAGE:
                        if (!isset($data['cashondelivery_surcharge']) || !trim($data['cashondelivery_surcharge'])) {
                            $data['cashondelivery_surcharge'] = '0';
                        }
                        $data['cashondelivery_surcharge'] = $data['cashondelivery_surcharge'] ? $data['cashondelivery_surcharge'] . '%' : '0';
                        if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                            $data['cod_min_surcharge'] = isset($data['cod_min_surcharge']) && trim($data['cod_min_surcharge']) ? $data['cod_min_surcharge'] : null;
                        }
                        break;
                    default:
                        $data['cashondelivery_surcharge'] = null;
                        if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                            $data['cod_min_surcharge'] = null;
                        }
                }
            }
        } else {
            if ($this->_getTableRateHelper()->supportsMarkup()) {
                $data['markup_type'] = '0';
            }
            $data['price'] = -1;
            if ($this->_getTableRateHelper()->supportsCashOnDelivery()) {
                $data['cashondelivery_surcharge'] = null;
            }
            if ($this->_getTableRateHelper()->supportsCodMinSurcharge()) {
                $data['cod_min_surcharge'] = null;
            }
        }

        if (isset($data['shipping_method_enabled'])) {
            unset($data['shipping_method_enabled']);
        }

        if (!$this->_getTableRateHelper()->supportsMarkup() && isset($data['markup_type'])) {
            unset($data['markup_type']);
        }


        if (isset($data['cod_option'])) {
            unset($data['cod_option']);
        }

        if (!$this->_getTableRateHelper()->supportsCashOnDelivery() && isset($data['cashondelivery_surcharge'])) {
            unset($data['cashondelivery_surcharge']);
        }

        if (!$this->_getTableRateHelper()->supportsCodMinSurcharge() && isset($data['cod_min_surcharge'])) {
            unset($data['cod_min_surcharge']);
        }


        $saveData = array();
        foreach ($this->_getMap() as $logicalName => $dbFieldName) {
            $saveData[$dbFieldName] = isset($data[$logicalName]) ? $data[$logicalName] : null;
        }

        $tablerate->setData($saveData);

        return true;
    }

    public function saveAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $data = $this->getRequest()->getPost();
        if ($data) {
            if ($this->getRequest()->getParam("duplicate") && isset($data['pk'])) {
                unset($data['pk']);
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rate duplicated successfully'));
                Mage::getSingleton('adminhtml/session')->setTablerateData($data);
                $this->_redirect("*/*/edit", array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));

                return;
            }

            $tablerate = Mage::getModel('zitec_tablerates/tablerate');
            /* @var $tablerate Zitec_TableRates_Model_Tablerate */
            $this->_prepareSaveData($tablerate, $data);
            try {
                $tablerate->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('zitec_tablerates')->__('Rate was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setTablerateData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('tablerate_id' => $tablerate->getTablerateId(), "carrier" => $this->_getTableRateHelper()->getCarrierCode()));

                    return;
                }
                $this->_redirect('*/*/', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));

                return;
            } catch (Exception $e) {
                if ($this->_getTableRateHelper()->isMySqlDuplicateKeyErrorMessage($e->getMessage())) {
                    $message = $this->__("The rate could not be saved because it duplicates the destination, service/product and weight/price of an existing rate. Change some of the rate's values and try saving again.");
                } else {
                    $message = $e->getMessage();
                }
                Mage::getSingleton('adminhtml/session')->addError($message);
                Mage::getSingleton('adminhtml/session')->setTablerateData($data);
                $this->_redirect('*/*/edit', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), "carrier" => $this->_getTableRateHelper()->getCarrierCode()));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('zitec_tablerates')->__('Unable to find rate to save'));
        $this->_redirect('*/*/', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));
    }


    public function deleteAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $tablerateId = $this->getRequest()->getParam('tablerate_id');
        if ($tablerateId > 0) {
            try {
                $model = Mage::getModel('zitec_tablerates/tablerate')->load($tablerateId);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rate was successfully deleted'));
                $this->_redirect('*/*/', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), "carrier" => $this->_getTableRateHelper()->getCarrierCode()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), "carrier" => $this->_getTableRateHelper()->getCarrierCode()));
            }
        }
        $this->_redirect('*/*/', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));
    }

    public function massDeleteAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $Ids = (array)$this->getRequest()->getParam('tablerates');
        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('zitec_tablerates/tablerate')->load($id);
                $result->delete();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }
        $this->_redirect('*/*/', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));
    }

    public function importratesAction()
    {
        if (!$this->_checkCarrierCode()) {
            return;
        }

        $websiteId = $this->getRequest()->getParam('website_id');
        $csvFile   = !empty($_FILES['import']['tmp_name']) ? $_FILES['import']['tmp_name'] : null;
        if (!$websiteId || !$csvFile) {
            $this->_getSession()->addError($this->__("Please specify the website and file you wish to import"));
            $this->_redirect('*/*/import', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));

            return;
        }

        $params = new Varien_Object();
        $params->setScopeId($websiteId);

        $resourceClass = null;
        $method        = null;
        $this->_getTableRateHelper()->getImportAction($resourceClass, $method);

        $message = "";
        try {
            Mage::getResourceModel($resourceClass)->$method($params, $csvFile);
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__("An error occurred whilst importing the tablerates: %s", $e->getMessage()));
            $this->_redirect('*/*/import', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));

            return;
        }
        if (!$message) {
            $message = $this->__("Table rates imported successfully");
            $this->_getSession()->addSuccess($message);
        } else {
            $this->_getSession()->addError(str_replace("\n", "<br />", $message));
        }
        $this->_redirect('*/*/index', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));
    }

    public function exportratesAction()
    {
        $websiteId = $this->getRequest()->getParam('website_id');
        if (!$websiteId) {
            $this->_getSession()->addError($this->__("Please specify the website whose rates you want to export"));
            $this->_redirect('*/*/export', array("carrier" => $this->_getTableRateHelper()->getCarrierCode()));

            return;
        }
        $module       = null;
        $controller   = null;
        $action       = null;
        $exportAction = $this->_getTableRateHelper()->getExportAction($module, $controller, $action);
        $params       = array('website' => $websiteId);
        if (!$this->_getTableRateHelper()->isExportUsingRedirect()) {
            $this->_forward($action, $controller, $module, $params);
        } else {
            $this->_redirect($exportAction, $params);
        }

    }

    /**
     * @return boolean
     */
    protected function _checkCarrierCode()
    {
        try {
            $this->_getTableRateHelper()->getCarrierCode();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

}