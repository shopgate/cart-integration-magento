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

/** @noinspection PhpIncludeInspection */
include_once Mage::getBaseDir('lib') . '/Shopgate/cart-integration-sdk/shopgate.php';

/**
 * Produces an info block for orders
 */
class Shopgate_Framework_Block_Payment_Notice extends Mage_Core_Block_Template
{
    /** @var Mage_Sales_Model_Order | Varien_Object */
    protected $order;

    /**
     * @return Mage_Sales_Model_Order|Varien_Object
     */
    public function getOrder()
    {
        if (is_null($this->order)) {
            if (Mage::registry('current_order')) {
                $order = Mage::registry('current_order');
            } elseif (Mage::registry('order')) {
                $order = Mage::registry('order');
            } else {
                $order = new Varien_Object();
            }
            $this->order = $order;
        }
        return $this->order;
    }

    /**
     * @return Shopgate_Framework_Model_Shopgate_Order
     */
    public function getShopgateOrder()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Mage::getModel('shopgate/shopgate_order')->load($this->getOrder()->getId(), 'order_id');
    }

    /**
     * Retrieves a warning if there is a difference between
     * actual Order total and Shopgate order total passed
     *
     * @return bool
     */
    public function hasDifferentPrices()
    {
        $order         = $this->getOrder();
        $shopgateOrder = $this->getShopgateOrder()->getShopgateOrderObject();

        if (!$shopgateOrder instanceof ShopgateOrder) {
            return false;
        }

        return !Mage::helper('shopgate')->isOrderTotalCorrect($shopgateOrder, $order, $msg);
    }

    /**
     * @see Shopgate_Framework_Block_Payment_MobilePayment::printHtmlError
     * @inheritdoc
     */
    public function printHtmlError($errorMessage)
    {
        /** @var Shopgate_Framework_Block_Payment_MobilePayment $mobile */
        $mobile = Mage::getBlockSingleton('shopgate/payment_mobilePayment');

        return $mobile->printHtmlError($errorMessage);
    }
}
