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
 * Interface to follow any shipping carrier that supports packed Shipments .
 * @category   Zitec
 * @package    Zitec_Dpd
 * @author     Zitec COM <magento@zitec.ro>
 */
interface Zitec_PackedShipment_Model_Carrier_Interface
{


    /**
     * If the carrier soporate address validation returns true else
     * postcodes in the specified country.
     *
     * @param string $countryId
     *
     * @return bool
     */
    function supportsAddressValidation($countryId);

    /*
     * True is returned if the combination of city and zip code is valid.
     * @param string $city
     * @param string $postcode
     * @param string &$errorMsg - In the case of an error
     * @return bool
     */
    public function isValidCityPostcode($city, $postcode, &$errorMsg);

    /*
     * The list of zip codes that are valid is returned to
     * la población proporcionada.
     * @param string $city
     * @param string &$errorMsg - In the case of an error
     * @return array (of string)
     */
    public function getPostcodesForCity($city, &$errorMsg);

    /*
     * The list of cities that are valid is returned to
     * the zip code provided.
     * @param string $postcode
     * @param string &$errorMsg - In the case of an error   *
     * @return array (of string)
     */
    public function getCitiesForPostcode($postcode, &$errorMsg);


    /*
     *  True is returned if the carrier can provide information on the shipping costs
     *
     * @return bool
     */
    function supportsCalculationOfShippingCosts();

    /*
     * The cost of shipping is obtained.
     * We order the city and the zip code of the recipient
     * (if different than ordering) and the list of the weights of the packages we send.
     * @param Mage_Sales_Model_Order $order
     * @param string $city
     * @param string $postcode
     * @param array $weightsPackages
     * @param string &$errorStr -- error message returned.
     * @return double -- cost of shipping
     */
    function getShippingCost(
        Mage_Sales_Model_Order $order,
        $city,
        $postcode,
        $weightsPackages,
        &$errorStr);

    /**
     * @param string $shippingMethod
     *
     * @return int
     */
    function shippingMethodRequiresShipmentsOfOnlyOneParcel($shippingMethod);
}