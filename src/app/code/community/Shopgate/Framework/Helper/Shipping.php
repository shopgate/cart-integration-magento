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
class Shopgate_Framework_Helper_Shipping
{

    /**
     * Just make the shipping shopgate_fix if there is a shopgate coupon with free ship
     * or if the shipping is made on Shopgate's end. Also checks
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateOrder          $order
     *
     * @throws Exception
     */
    public function setShippingMethod(Mage_Sales_Model_Quote $quote, ShopgateOrder $order)
    {
        if ($this->hasFreeShippingCoupon($order)
            || $order->getShippingType() === ShopgateDeliveryNote::MANUAL
            || Mage::getStoreConfigFlag(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_FORCE_SG_SHIPPING)
        ) {
            $quote->getShippingAddress()->setShippingMethod(Shopgate_Framework_Model_Carrier_Fix::CODE);
        } else {
            $quote->getShippingAddress()->setShippingMethod($order->getShippingInfos()->getName());
        }
    }

    /**
     * Processes rates, applies new shipping info and re-collects
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateOrder          $order
     */
    public function applyShipping(Mage_Sales_Model_Quote $quote, ShopgateOrder $order)
    {
        $info    = $order->getShippingInfos();
        $address = $quote->getShippingAddress();
        $this->setShippingMethod($quote, $order);

        $quote->setData('inventory_processed_flag', false);
        $quote->setData('totals_collected_flag', false);
        $address->setCollectShippingRates(true);
        $quote->collectTotals();
        $quote->save();

        /**
         * In case original shipping mapping was not found (the only way to know if we run collector)
         */
        $rate = $address->collectShippingRates()->getShippingRateByCode($info->getName());
        if (!$rate || !$address->getShippingMethod()) {
            $address->setShippingMethod(Shopgate_Framework_Model_Carrier_Fix::CODE);
            ShopgateLogger::getInstance()->log('Could not map shipping method');

            /**
             * Need to re-process to apply shopgate_fix fee to quote totals
             * There is an issue with two collector calls reducing taxes for coupons on
             * lower mage versions (<1.5). Unfortunately this collect is a must.
             *
             * @see https://shopgate.atlassian.net/browse/MAGENTO-880
             */
            $quote->setData('totals_collected_flag', false);
            $quote->collectTotals();
        }
    }

    /**
     * @param ShopgateOrder $order
     *
     * @return bool
     */
    private function shopgateCouponExists(ShopgateOrder $order)
    {
        foreach ($order->getItems() as $item) {
            if ($item->isSgCoupon()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if order has a Free Ship coupon applied to it
     *
     * @param ShopgateOrder $order
     *
     * @return bool
     */
    public function hasFreeShippingCoupon(ShopgateOrder $order)
    {
        return $this->shopgateCouponExists($order) && $order->getAmountShipping() == 0;
    }
}
