<?php

/**
 * Zitec Dpd - Api
 *
 * Class Zitec_Dpd_Api_Manifest_Close_Response
 * Customize here manifest close response
 *
 * @category   Zitec
 * www.zitec.com
 * @package    Zitec_Dpd
 * @author     george.babarus <george.babarus@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Zitec_Dpd_Api_Manifest_Close_Response extends Zitec_Dpd_Api_Manifest_Response
{

    const RESULT = 'return';
    const PDF_MANIFEST_FILE = 'pdfManifestFile';
    const MANIFEST_ID = 'manifestId';
    const MANIFEST_NAME = 'manifestName';
    const MANIFEST_REFERENCE_NUMBER = 'manifestReferenceNumber';

    /**
     *
     * @return string
     */
    public function getPdfFile()
    {
        return $this->_getResponseProperty(array(self::RESULT, self::PDF_MANIFEST_FILE));
    }

    /**
     *
     * @return string
     */
    public function getManifestId()
    {
        return $this->_getResponseProperty(array(self::RESULT, self::MANIFEST_ID));
    }

    /**
     *
     * @return string
     */
    public function getManifestName()
    {
        return $this->_getResponseProperty(array(self::RESULT, self::MANIFEST_NAME));
    }

    /**
     *
     * @return string
     */
    public function getManifestReferenceNumber()
    {
        return $this->_getResponseProperty(array(self::RESULT, self::MANIFEST_REFERENCE_NUMBER));
    }

}


