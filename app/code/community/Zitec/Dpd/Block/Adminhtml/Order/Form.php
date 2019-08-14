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
class Zitec_Dpd_Block_Adminhtml_Customer_Edit_Form_Addresslength extends Zitec_Dpd_Block_Addresslength
{


    /**
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array("street0", "street1", "street2", "street3");
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


}


