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
 * this class is used for
 *
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Dpd_Helper_Postcode_Search extends Mage_Core_Helper_Abstract
{

    public function extractPostCodeForShippingRequest($request)
    {
        $countryName = Mage::getModel('directory/country')->loadByCode($request->getDestCountryId())->getName();

        if ($this->isEnabledAutocompleteForPostcode($countryName)) {
            if($request->getDestRegionId()) {
                $regionName = Mage::getModel('directory/region')->load($request->getDestRegionId())->getName();
            }

            $address = array(
                'country'  => $countryName,
                'region'   => $regionName,
                'city'     => $request->getDestCity(),
                'address'  => $request->getDestStreet(),
                'postcode' => $request->getDestPostcode(),
            );

            $postcodeRelevance = new stdClass();
            $postCode          = $this->search($address, $postcodeRelevance);

            $checkout    = Mage::getSingleton('checkout/session')->getQuote();
            $shipAddress = $checkout->getShippingAddress();
            $shipAddress->setData('auto_postcode', $postCode);
            $shipAddress->setData('valid_auto_postcode', $this->isValid($postCode, $postcodeRelevance));
            if ($this->isValid($postCode, $postcodeRelevance)){
                $shipAddress->setPostcode($postCode);
            }
        } else {
            $postCode = $request->getDestPostcode();
        }

        return $postCode;
    }



    /**
     * it is used to create a list of relevant addresses for given address.
     * used in admin panel to validate the postcode
     *
     * @param array $address The content will be the edit form for address from admin
     * $address contain next keys
     *      MANDATORY
     *      country
     *      city
     *
     * OPTIONAL
     *      region
     *      address
     *      street
     */
    public function findAllSimilarAddressesForAddress($address){
        if(!empty($address['country_id'])){
            $countryName = Mage::getModel('directory/country')->loadByCode($address['country_id'])->getName();
            $address['country'] = $countryName;
        }

        if ($this->isEnabledAutocompleteForPostcode($countryName)) {
            if ($address['region_id']) {
                $regionName        = Mage::getModel('directory/region')->load($address['region_id'])->getName();
                $address['region'] = $regionName;
            }

            $foundAddresses = $this->getSearchPostcodeModel()->searchSimilarAddresses($address);
            return $foundAddresses;
        }

        return false;
    }



    /**
     * @param array $address
     *      $address contain next keys
     *      MANDATORY
     *      country
     *      city
     *
     * OPTIONAL
     *      region
     *      address
     *      street
     *
     * @param null $postcodeRelevance
     *
     * @return string
     */
    public function search($address, $postcodeRelevance = null)
    {
        $foundPostCode = $this->getSearchPostcodeModel()->search($address, $postcodeRelevance);
        if (isset($address['postcode']) && strlen($address['postcode']) > 4) {
            if ($foundPostCode == $address['postcode']) {
                return $foundPostCode;
            } elseif (!empty($foundPostCode)) {
                //mark the response as not exactly the same
                return $foundPostCode;
            }

            return $address['postcode'];
        }

        return $foundPostCode;
    }

    /**
     * test if found postcode relevance is enough for considering the postcode useful in the rest of checkout process
     *
     * @param          $postCode
     * @param stdClass $relevance
     *
     * @return int
     */
    public function isValid($postCode, stdClass $relevance = null)
    {
        if (empty($relevance)) {
            return 0;
        }
        if (!empty($relevance->percent) && $relevance->percent > Zitec_Dpd_Postcode_Search::SEARCH_RESULT_RELEVANCE_THRESHOLD_FOR_VALIDATION) {
            return 1;
        }

        return 0;
    }


    public function isEnabledAutocompleteForPostcode($countryName)
    {
        $isValid = $this->getSearchPostcodeModel()->isEnabled($countryName);
        if(empty($isValid)){
            return false;
        }

        $value = Mage::getStoreConfig('carriers/zitecDpd/postcode_autocomplete_checkout');

        return !empty($value);
    }


    public function getSearchPostcodeModel()
    {
        $getSearchPostcodeModel = Mage::registry('getSearchPostcodeModel');

        if (empty($getSearchPostcodeModel)) {
            $connection  = Mage::getSingleton('core/resource')->getConnection('core_read');
            $libInstance = new Zitec_Dpd_Postcode_Search(Zitec_Dpd_Postcode_Search::MYSQL_ADAPTER, $connection);
            Mage::register('getSearchPostcodeModel', $libInstance);
            $getSearchPostcodeModel = $libInstance;
        }

        return $getSearchPostcodeModel;
    }


}