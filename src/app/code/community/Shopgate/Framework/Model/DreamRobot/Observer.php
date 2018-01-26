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
class Shopgate_Framework_Model_DreamRobot_Observer
{

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function sendOrder(Varien_Event_Observer $observer)
    {
        /* @var ShopgateOrder $shopgateOrder */
        /* @var Mage_Sales_Model_Order $magentoOrder */
        $shopgateOrder = $observer->getShopgateOrder();
        $magentoOrder  = $observer->getOrder();

        if (class_exists("DreamRobot_Checkout_Model_Observer", false) && !$shopgateOrder->getIsShippingBlocked()) {
            $msg = "Start to send order to DreamRobot\n";
            $msg .= "\tOrderID: {$magentoOrder->getId()}\n";
            $msg .= "\tOrderNumber: {$magentoOrder->getIncrementId()}\n";
            $msg .= "\tShopgateOrderNumber: {$shopgateOrder->getOrderNumber()}\n";

            ShopgateLogger::getInstance()->log($msg, ShopgateLogger::LOGTYPE_REQUEST);

            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastOrderId($magentoOrder->getId());
            $c = new DreamRobot_Checkout_Model_Observer();
            $c->getSaleOrder();
        }

        Mage::dispatchEvent('inventory_assignation', array('order' => $magentoOrder));

        return true;
    }
}
