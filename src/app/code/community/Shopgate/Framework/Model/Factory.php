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
class Shopgate_Framework_Model_Factory
{
    /** @var null | Shopgate_Framework_Model_Payment_Factory */
    protected $paymentFactory = null;

    /** @var null | Shopgate_Framework_Model_Modules_Affiliate_Factory */
    protected $affiliateFactory = null;

    /**
     * Payment factory retriever
     *
     * @param ShopgateCartBase | null $sgOrder
     *
     * @return Shopgate_Framework_Model_Payment_Factory
     */
    public function getPayment(ShopgateCartBase $sgOrder = null)
    {
        if (is_null($this->paymentFactory)) {
            if (is_null($sgOrder)) {
                $sgOrder = Mage::registry('shopgate_order');
            }
            $this->paymentFactory = Mage::getModel('shopgate/payment_factory', array($sgOrder));
        }

        return $this->paymentFactory;
    }

    /**
     * Affiliate factory retriever
     *
     * @param ShopgateCartBase | null $sgOrder
     *
     * @return Shopgate_Framework_Model_Modules_Affiliate_Factory
     */
    public function getAffiliate(ShopgateCartBase $sgOrder = null)
    {
        if (is_null($this->affiliateFactory)) {
            if (is_null($sgOrder)) {
                $sgOrder = Mage::registry('shopgate_order');
            }
            $router                 = Mage::getModel('shopgate/modules_affiliate_router', array($sgOrder));
            $this->affiliateFactory = Mage::getModel('shopgate/modules_affiliate_factory', array($sgOrder, $router));
        }

        return $this->affiliateFactory;
    }
}
