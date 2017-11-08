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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_KProject_Redeem
{
    /**
     * Sets up affiliate data in session & disable place_order_after observer call
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
        $parameters = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::PARAMS);
        Mage::getSingleton('kproject_sas/session')->setParameters($parameters);
        Mage::register('kproject_sas_observer_disable', true, true);

        return true;
    }

    /**
     * Send transaction to ShareASale, only called from addOrder
     *
     * @param Varien_Object $params - array(
     *                              'sg_order' => Shopgate Order,
     *                              'mage_order' => Magento Order,
     *                              )
     *
     * @return Mage_Sales_Model_Order
     */
    public function promptCommission(Varien_Object $params)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::MAGE_ORDER);

        if (Mage::helper('kproject_sas')->newTransactionViaApiEnabled()) {
            $response = Mage::helper('kproject_sas/transaction')->create($order);
            Mage::helper('kproject_sas/status')->setKOrderStatus(
                $order,
                KProject_ShareASale_Helper_Status::STATUS_SUCCESS,
                $response
            );
        }

        return $order;
    }

    /**
     * Destroy cookies when done
     */
    public function destroyCookies()
    {
        Mage::getSingleton('kproject_sas/session')->unsetParameters();
        Mage::unregister('kproject_sas_observer_disable');
    }
}
