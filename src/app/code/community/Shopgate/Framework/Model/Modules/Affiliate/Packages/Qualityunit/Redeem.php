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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Redeem
{
    /**
     * Sets up affiliate cookie so that the affiliate logic picks it up
     *
     * @param Varien_Object $params - array(
     *                              'quote' => Mage Quote,
     *                              'parameters' => valid get params,
     *                              'customer_id' => customer id this quote belongs to
     *                              )
     *
     * @return bool
     */
    public function setAffiliateData(Varien_Object $params)
    {
        $cookieName = Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Validator::VISITOR_COOKIE;
        $parameters = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::PARAMS);
        if (!empty($parameters[$cookieName])) {
            $_COOKIE += $parameters;
        }

        return true;
    }

    /**
     * Binds this order with the affiliate ID
     *
     * @param Varien_Object $params - array(
     *                              'sg_order' => Shopgate Order,
     *                              'mage_order' => Magento Order,
     *                              )
     *
     */
    public function promptCommission(Varien_Object $params)
    {
        /** @var Mage_Sales_Model_Order $mageOrder */
        $mageOrder = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::MAGE_ORDER);
        $cookie    =
            $_COOKIE[Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Validator::VISITOR_COOKIE];

        try {
            Mage::getModel('pap/pap')->createAffiliate($mageOrder, false);
            Mage::getModel('pap/pap')->registerOrder($mageOrder, $cookie);
        } catch (Exception $e) {
            ShopgateLogger::getInstance()->log('Encountered a hard error trying to call PAP affiliate logic:');
            ShopgateLogger::getInstance()->log($e->getMessage());
        }
    }

    /**
     * Destroy cookies when done
     */
    public function destroyCookies()
    {
        unset($_COOKIE[Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Validator::VISITOR_COOKIE]);
    }
}
