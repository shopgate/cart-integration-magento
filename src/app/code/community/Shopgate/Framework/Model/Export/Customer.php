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
class Shopgate_Framework_Model_Export_Customer extends Shopgate_Framework_Model_Export_Abstract
{
    /**
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return ShopgateCustomer
     */
    public function loadGetCustomerData($magentoCustomer)
    {
        $shopgateCustomer = new ShopgateCustomer();
        $this->_getCustomerSetBaseData($shopgateCustomer, $magentoCustomer);
        $this->_getCustomerNewsletterSubscription($shopgateCustomer, $magentoCustomer);
        $this->_getCustomerSetAddresses($shopgateCustomer, $magentoCustomer);
        return $shopgateCustomer;
    }

    /**
     * @param ShopgateCustomer             $shopgateCustomer
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return ShopgateCustomer
     */
    protected function _getCustomerSetBaseData(&$shopgateCustomer, $magentoCustomer)
    {
        $shopgateCustomer->setCustomerId($magentoCustomer->getId());
        $shopgateCustomer->setCustomerToken($this->_getCustomerToken($magentoCustomer));
        $shopgateCustomer->setFirstName($magentoCustomer->getFirstname());
        $shopgateCustomer->setLastName($magentoCustomer->getLastname());
        $shopgateCustomer->setMail($magentoCustomer->getEmail());
        $shopgateCustomer->setBirthday($magentoCustomer->getDob());
        $shopgateCustomer->setPhone($magentoCustomer->getTelephone());
        $shopgateCustomer->setGender($this->_getCustomerHelper()->getShopgateCustomerGender($magentoCustomer));

        $customerGroups = array();
        foreach ($this->_getCustomerHelper()->getShopgateCustomerGroups($magentoCustomer) as $customerGroup) {
            $customerGroups[] = new ShopgateCustomerGroup($customerGroup);
        }

        $shopgateCustomer->setCustomerGroups($customerGroups);

        return $shopgateCustomer;
    }

    /**
     * @param ShopgateCustomer             $shopgateCustomer
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return ShopgateCustomer
     */
    protected function _getCustomerNewsletterSubscription(&$shopgateCustomer, $magentoCustomer)
    {
        /** @var Mage_Newsletter_Model_Subscriber $newsletterSubscriber */
        $newsletterSubscriber = Mage::getModel("newsletter/subscriber");
        $newsletterSubscriber->loadByEmail($magentoCustomer->getEmail());
        $shopgateCustomer->setNewsletterSubscription($newsletterSubscriber->getSubscriberStatus() == 1);

        return $shopgateCustomer;
    }

    /**
     * @param ShopgateCustomer             $shopgateCustomer
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return ShopgateCustomer
     */
    protected function _getCustomerSetAddresses(&$shopgateCustomer, $magentoCustomer)
    {
        $aAddresses = array();
        foreach ($magentoCustomer->getAddresses() as $magentoCustomerAddress) {
            /** @var  Mage_Customer_Model_Address $magentoCustomerAddress */
            $shopgateAddress = new ShopgateAddress();
            $shopgateAddress->setId($magentoCustomerAddress->getId());
            $shopgateAddress->setIsDeliveryAddress(1);
            $shopgateAddress->setIsInvoiceAddress(1);
            $shopgateAddress->setFirstName($magentoCustomerAddress->getFirstname());
            $shopgateAddress->setLastName($magentoCustomerAddress->getLastname());
            $shopgateAddress->setGender(
                $this->_getCustomerHelper()->getShopgateCustomerGender($magentoCustomerAddress)
            );
            $shopgateAddress->setCompany($magentoCustomerAddress->getCompany());
            $shopgateAddress->setMail($magentoCustomerAddress->getMail());
            $shopgateAddress->setPhone($magentoCustomerAddress->getTelephone());
            $shopgateAddress->setStreet1($magentoCustomerAddress->getStreet1());
            $shopgateAddress->setStreet2($magentoCustomerAddress->getStreet2());
            $shopgateAddress->setCity($magentoCustomerAddress->getCity());
            $shopgateAddress->setZipcode($magentoCustomerAddress->getPostcode());
            $shopgateAddress->setCountry($magentoCustomerAddress->getCountry());
            $shopgateAddress->setState($this->_getHelper()->getIsoStateByMagentoRegion($magentoCustomerAddress));

            $aAddresses[] = $shopgateAddress;
        }
        $shopgateCustomer->setAddresses($aAddresses);

        return $shopgateCustomer;
    }

    /**
     * @param   Mage_Customer_Model_Customer $magentoCustomer
     * @return  string
     */
    protected function _getCustomerToken($magentoCustomer)
    {
        $relationModel = Mage::getModel('shopgate/customer')->loadByCustomerId($magentoCustomer->getId());

        if (!$relationModel->getId()) {
            $relationModel->setToken(md5($magentoCustomer->getId() . $magentoCustomer->getEmail()));
            $relationModel->setCustomerId($magentoCustomer->getId());
            $relationModel->save();
        }
        return $relationModel->getToken();
    }
}
