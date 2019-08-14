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
class Zitec_Dpd_Adminhtml_PostcodeController extends Mage_Adminhtml_Controller_Action
{


    /**
     * print the form to the user
     *
     */
    public function updateFormAction()
    {
        // if an error occurred in the past because of php upload size limit, then trigger an error message
        // while the setting is not modified
        $fileSizeError   = Mage::getSingleton('core/session')->getDpdMaxFileUploadError();
        $currentSettings = ini_get('upload_max_filesize');
        if (!empty($fileSizeError) && $fileSizeError == $currentSettings) {
            Mage::getSingleton('core/session')->addError(
                Mage::helper('core')->__('Your PHP settings for upload_max_filesize is too low (%s). Please increase this limit or upload the file manually into media/dpd/postcode', $fileSizeError)
            );
        }


        $this->loadLayout();
        $this->_setActiveMenu("zitec_dpd");

        // create the form container + form and the left panel
        $this->_addContent($this->getLayout()->createBlock('zitec_dpd/adminhtml_postcode_formContainer'));
        $this->_addContent($this->getLayout()->createBlock('zitec_dpd/adminhtml_postcode_update_files'));

        $this->renderLayout();

    }

    /**
     * upload the file and run the import script on it
     * if a file was already uploaded and the name
     * of the file is sent in the post request then run the import on this file
     */
    public function importAction()
    {
        $baseFileName = '';
        try {

            //process the upload logic for the csv file
            if (isset($_FILES['csv']['name']) && $_FILES['csv']['name'] != '') {
                if (isset($_FILES['csv']['error']) && !empty($_FILES['csv']['error'])) {
                    $message = $this->getUploadCodeMessage($_FILES['csv']['error']);
                    throw new Exception($message, $_FILES['csv']['error']);
                }
                $uploader = new Varien_File_Uploader('csv');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = Mage::helper('zitec_dpd/postcode_search')->getPathToDatabaseUpgradeFiles();
                //$path .= date('Y-m-d').DS;
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $uploader->save($path, $_FILES['csv']['contacts']);
                $newUpdateFilename = $path . $uploader->getUploadedFileName();
                $baseFileName      = $uploader->getUploadedFileName();
            }

            // if the no uploads made then check if the path_to_csv field was filed
            if (empty($newUpdateFilename)) {
                $baseFileName = $this->getRequest()->getPost('path_to_csv');
                if (empty($baseFileName)) {
                    throw new Exception(
                        Mage::helper('core')->__('Nothing to do!! Please upload a file or select an uploaded file.')
                    );
                }
                if (isset($baseFileName)) {
                    $path           = Mage::helper('zitec_dpd/postcode_search')->getPathToDatabaseUpgradeFiles();
                    $updateFilename = $path . $baseFileName;
                    if (!is_file($updateFilename)) {
                        throw new Exception(
                            Mage::helper('core')->__('File %s was not found in path media/dpd/postcode_updates', $baseFileName)
                        );
                    }

                    $newUpdateFilename = $updateFilename;
                }
            }

            if (!is_file($newUpdateFilename)) {
                throw new Exception(
                    Mage::helper('core')->__('File %s was not found in path media/dpd/postcode_updates', $baseFileName)
                );
            }


        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect("zitec_dpd/adminhtml_postcode/updateForm");

            return;
        }

        // with the filename found, we need to run the database update script
        // by calling the updateDatabase function of the postcode library
        try {
            Mage::helper('zitec_dpd/postcode_search')->updateDatabase($newUpdateFilename);
            Mage::getSingleton('core/session')->addSuccess(
                Mage::helper('core')->__('Last updates found on file %s, were installed successfully.', $baseFileName)
            );
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_redirect("zitec_dpd/adminhtml_postcode/updateForm");
    }


    /**
     *
     * @param $code
     *
     * @return string
     */
    private function getUploadCodeMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                Mage::getSingleton('core/session')->setDpdMaxFileUploadError(ini_get('upload_max_filesize'));
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }

        return $message;
    }

    /**
     *
     * @return Zitec_TableRates_Helper_Data
     */
    protected function _getTableRateHelper()
    {
        return Mage::helper('zitec_tablerates');
    }


}
