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
class Shopgate_Framework_Model_Carrier_Fix
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const CODE = 'shopgate_fix';
    /** @var string */
    protected $_code = 'shopgate';
    /** @var string */
    protected $_method = 'fix';
    /** @var bool */
    protected $_isFixed = false;
    /** @var int */
    protected $_numBoxes = 1;

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool | Mage_Shipping_Model_Rate_Result | null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /* @var $sgOrder ShopgateOrder | ShopgateCart */
        $sgOrder = Mage::registry('shopgate_order');
        if (!$sgOrder) {
            return false;
        }

        if (!$sgOrder->getShippingInfos() instanceof ShopgateShippingInfo) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = $this->createSgShippingMethod($sgOrder);
        $result->append($method);

        return $result;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::helper('shopgate')->isShopgateApiRequest();
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_method => $this->getConfigData('name'));
    }

    /**
     * Creates our own personal shipping method
     * and passes the data to it
     *
     * @param ShopgateCartBase $shopgateOrder
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function createSgShippingMethod(ShopgateCartBase $shopgateOrder)
    {
        $shippingInfo = $shopgateOrder->getShippingInfos();
        $group        = $shopgateOrder->getShippingGroup();

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setData('carrier', $this->_code);
        $method->setData('carrier_title', $group !== ShopgateDeliveryNote::OTHER ? $group : '');
        $method->setData('method', $this->_method);
        $displayName = ($shopgateOrder->getShippingType()
            === Shopgate_Framework_Model_Shopgate_Shipping_Mapper::SHIPPING_TYPE_PLUGINAPI)
            ? $shippingInfo->getDisplayName() : $shippingInfo->getName();
        $method->setData('method_title', $displayName);

        $scopeId             = Mage::helper('shopgate/config')->getConfig()->getStoreViewId();
        $shippingIncludesTax = Mage::helper('tax')->shippingPriceIncludesTax($scopeId);
        $shippingTaxClass    = Mage::helper('tax')->getShippingTaxClass($scopeId);

        $amountNet   = $shippingInfo->getAmountNet();
        $amountGross = $shippingInfo->getAmountGross();

        if ($shippingIncludesTax) {
            $shippingAmount = Mage::helper('shopgate/sales')
                                  ->getOriginalGrossAmount($scopeId, $shippingTaxClass, $amountNet, $amountGross);
        } else {
            $shippingAmount = $amountNet;
        }

        $exchangeRate = Mage::app()->getStore()->getCurrentCurrencyRate();
        $method->setPrice($shippingAmount / $exchangeRate);

        return $method;
    }
}
