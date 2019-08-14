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
class Zitec_Dpd_Block_Addresslength extends Mage_Core_Block_Template
{
    /**
     *
     * @return string
     */
    public function getClassName()
    {
        return "zitec_dpd-address-length-validate";
    }

    /**
     *
     * @return int
     */
    public function getMaxLength()
    {
        return Zitec_Dpd_Api_Configs::SHIPMENT_LIST_RECEIVER_STREET_MAX_LENGTH;
    }

    public function getMinLength()
    {
        return 35;
    }

    /**
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array();
    }

    /**
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->__("The total length of the address cannot be more than %s characters.", $this->getMaxLength());
    }

    /**
     *
     * @param array $fieldNames
     *
     * @return string
     */
    protected function _getHtml(array $fieldNames)
    {
        if (!$fieldNames) {
            return '';
        }

        $fieldsHtml = '';
        foreach ($fieldNames as $fieldName) {
            $fieldsHtml .= "
            field = $('{$fieldName}');
            if (field) {
                fields.push(field);
            }";
        }

        $html = "
<script type='text/javascript'>
//<![CDATA[
        var className = '{$this->getClassName()}',
            fields = [],
            field = null,
            message = '{$this->getMessage()}',
            maxLength = {$this->getMaxLength()},
            minLength = {$this->getMinLength()};

            {$fieldsHtml}

            new zitecFieldLengths.Validator(className, fields, message, maxLength, minLength);
//]]>
</script>";

        return $html;
    }

    /**
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_showBlock()) {
            return $this->_getHtml($this->getFieldNames());
        } else {
            return '';
        }
    }

    /**
     *
     * @return boolean
     */
    protected function _showBlock()
    {
        return $this->_getHelper()->moduleIsActive();
    }

    /**
     *
     * @return Zitec_Dpd_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zitec_dpd');
    }
}

