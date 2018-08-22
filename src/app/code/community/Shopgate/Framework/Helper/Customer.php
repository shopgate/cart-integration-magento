<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class Shopgate_Framework_Helper_Customer extends Shopgate_Framework_Helper_Data
{
    /**
     * @param ShopgateAddress $shopgateCustomerAddress
     *
     * @return Mage_Customer_Model_Address
     */
    public function getMagentoCustomerAddress(ShopgateAddress $shopgateCustomerAddress)
    {
        $region = $this->getMagentoRegionByShopgateAddress($shopgateCustomerAddress);

        /** @var Mage_Customer_Model_Address $magentoCustomerAddress */
        $magentoCustomerAddress = Mage::getModel('customer/address');
        $magentoCustomerAddress->setIsActive(true);
        $magentoCustomerAddress->setPrefix($this->getPrefixForGender($shopgateCustomerAddress->getGender()));
        $magentoCustomerAddress->setFirstname($shopgateCustomerAddress->getFirstName());
        $magentoCustomerAddress->setLastname($shopgateCustomerAddress->getLastName());
        $magentoCustomerAddress->setCompany($shopgateCustomerAddress->getCompany());
        $magentoCustomerAddress->setStreet($shopgateCustomerAddress->getStreet1());
        $magentoCustomerAddress->setCity($shopgateCustomerAddress->getCity());
        $magentoCustomerAddress->setPostcode($shopgateCustomerAddress->getZipcode());
        $magentoCustomerAddress->setCountryId($shopgateCustomerAddress->getCountry());
        $magentoCustomerAddress->setRegion($region->getId());
        $magentoCustomerAddress->setIsDeliveryAddress($shopgateCustomerAddress->getIsDeliveryAddress());
        $magentoCustomerAddress->setIsInvoiceAddress($shopgateCustomerAddress->getIsInvoiceAddress());

        if ($shopgateCustomerAddress->getMobile()) {
            $magentoCustomerAddress->setTelephone($shopgateCustomerAddress->getMobile());
        } else {
            if ($shopgateCustomerAddress->getPhone()) {
                $magentoCustomerAddress->setTelephone($shopgateCustomerAddress->getPhone());
            }
        }

        return $this->setCustomFields($magentoCustomerAddress, $shopgateCustomerAddress);
    }

    /**
     * Get Magento region model by given ShopgateAddress
     *
     * @param ShopgateAddress $address
     *
     * @return Mage_Directory_Model_Region|Varien_Object
     */
    public function getMagentoRegionByShopgateAddress(ShopgateAddress $address)
    {
        $map = Mage::helper('shopgate')->_getIsoToMagentoMapping();
        if (!$address->getState()) {
            return new Varien_Object();
        }

        $state = preg_replace("/{$address->getCountry()}\-/", "", $address->getState());
        /** @var Mage_Directory_Model_Region $region */
        $region = Mage::getModel('directory/region')->getCollection()
                      ->addRegionCodeFilter($state)
                      ->addCountryFilter($address->getCountry())
                      ->getFirstItem();

        // If no region was found
        if (!$region->getId() && !empty($state) && isset($map[$address->getCountry()][$state])) {
            $regionCode = $map[$address->getCountry()][$state];

            $region = Mage::getModel('directory/region')->getCollection()
                          ->addRegionCodeFilter($regionCode)
                          ->addCountryFilter($address->getCountry())
                          ->getFirstItem();
        }

        return $region;
    }

    /**
     * get gender according to shopgate needs
     *
     * @param Mage_Customer_Model_Customer|Mage_Customer_Model_Address $data
     *
     * @return string
     */
    public function getShopgateCustomerGender($data)
    {
        $options = Mage::getResourceModel('customer/customer')
                       ->getAttribute('gender')
                       ->getSource()
                       ->getAllOptions(false);
        $gender  = null;
        foreach ($options as $option) {
            if ($option['value'] == $data->getGender()) {
                $gender = $option['label'];
            }
        }

        switch ($gender) {
            case 'Male':
                return ShopgateCustomer::MALE;
            case 'Female':
                return ShopgateCustomer::FEMALE;
            default:
        }

        switch ($data->getPrefix()) {
            case $this->__('Mr.'):
                return ShopgateCustomer::MALE;
            case $this->__('Mrs.'):
                return ShopgateCustomer::FEMALE;
            default:
                return '';
        }

    }

    /**
     * @param string $shopgateGender
     *
     * @return string
     */
    public function getMagentoCustomerGender($shopgateGender)
    {
        $gender = Mage::getResourceModel('customer/customer')
                      ->getAttribute('gender')
                      ->getSource();

        switch ($shopgateGender) {
            case ShopgateCustomer::MALE:
                return $gender->getOptionId('Male');
            case ShopgateCustomer::FEMALE:
                return $gender->getOptionId('Female');
            default:
                return '';
        }
    }

    /**
     * @param string $shopgateGender
     *
     * @return string
     */
    public function getPrefixForGender($shopgateGender)
    {
        if ($shopgateGender == ShopgateCustomer::FEMALE) {
            return $this->__('Mrs.');
        }

        return $this->__('Mr.');
    }

    /**
     * @param Mage_Customer_Model_Customer $magentoCustomer
     *
     * @return array $collection
     */
    public function getShopgateCustomerGroups($magentoCustomer)
    {
        $collection = Mage::getModel('customer/group')->getCollection();

        if (!$magentoCustomer->getId()) {
            $collection->addFieldToFilter('customer_group_code', 'NOT LOGGED IN');
        } else {
            $collection->addFieldToFilter('customer_group_id', $magentoCustomer->getGroupId());
        }

        $groups = array();
        foreach ($collection as $customerGroup) {
            $group['id']   = $customerGroup->getCustomerGroupId();
            $group['name'] = $customerGroup->getCustomerGroupCode();
            $groups[]      = $group;
        }

        return $groups;
    }

    /**
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @param ShopgateCustomer             $shopgateCustomer
     */
    public function registerCustomer($magentoCustomer, $shopgateCustomer)
    {
        $magentoCustomer = $this->_registerSetBasicData($magentoCustomer, $shopgateCustomer);
        $magentoCustomer = $this->setCustomFields($magentoCustomer, $shopgateCustomer);
        $this->_registerAddCustomerAddresses($magentoCustomer, $shopgateCustomer);
    }

    /**
     * Set customers basic data like name, gender etc.
     *
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @param ShopgateCustomer             $shopgateCustomer
     *
     * @return Mage_Customer_Model_Customer $magentoCustomer
     */
    protected function _registerSetBasicData($magentoCustomer, $shopgateCustomer)
    {
        $magentoCustomer->setPrefix($this->getPrefixForGender($shopgateCustomer->getGender()));
        $magentoCustomer->setConfirmation(null);
        $magentoCustomer->setFirstname($shopgateCustomer->getFirstName());
        $magentoCustomer->setLastname($shopgateCustomer->getLastName());
        $magentoCustomer->setGender($this->getMagentoCustomerGender($shopgateCustomer->getGender()));
        $magentoCustomer->setDob($shopgateCustomer->getBirthday());
        $magentoCustomer->setForceConfirmed(true);
        $magentoCustomer->save();
        $magentoCustomer->sendNewAccountEmail('registered', '', $magentoCustomer->getStore()->getId());

        return $magentoCustomer;
    }

    /**
     * add addresses to the customer
     *
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @param ShopgateCustomer             $shopgateCustomer
     */
    protected function _registerAddCustomerAddresses($magentoCustomer, $shopgateCustomer)
    {
        foreach ($shopgateCustomer->getAddresses() as $shopgateCustomerAddress) {
            $magentoCustomerAddress = $this->getMagentoCustomerAddress($shopgateCustomerAddress);
            $magentoCustomerAddress->setCustomer($magentoCustomer);
            $magentoCustomerAddress->setCustomerId($magentoCustomer->getId());
            $magentoCustomerAddress->save();

            if ($magentoCustomerAddress->getIsInvoiceAddress() && !$magentoCustomer->getDefaultBillingAddress()) {
                $magentoCustomer->setDefaultBilling($magentoCustomerAddress->getId());
            }

            if ($magentoCustomerAddress->getIsDeliveryAddress() && !$magentoCustomer->getDefaultShippingAddress()) {
                $magentoCustomer->setDefaultShipping($magentoCustomerAddress->getId());
            }
        }
        $magentoCustomer->save();
    }

    /**
     * add customer to cart e.g to validate customer related price rules
     *
     * @param ShopgateCart $cart
     */
    public function addCustomerToCart(&$cart)
    {
        if ($cart->getMail()) {
            /** @var Mage_Customer_Model_Customer $magentoCustomer */
            $magentoCustomer = Mage::getModel("customer/customer");
            $magentoCustomer->setWebsiteId(Mage::app()->getWebsite()->getid());
            $magentoCustomer->loadByEmail($cart->getMail());
            if ($magentoCustomer->getId()) {
                $cart->setExternalCustomerId($magentoCustomer->getId());
            }
        }
    }
}
