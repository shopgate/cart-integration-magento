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
class Shopgate_Framework_Helper_Billsafe_Client extends Netresearch_Billsafe_Model_Client
{
    /**
     * @inheritdoc
     */
    public function reportShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $originalId = $shipment->getOrder()->getIncrementId();
        $shopgateId = $this->getShopgateOrderNumber($shipment->getOrder());
        $shipment->getOrder()->setIncrementId($shopgateId);
        parent::reportShipment($shipment);
        $shipment->getOrder()->setIncrementId($originalId);
        $shipment->getOrder()->setData('is_shopgate_order', true);

        return $this;
    }

    /**
     * Sneaky substitution on BillSAFE calls
     * as our backend uses our shopgateOrderNumber
     * when creating an order and not Magento's.
     *
     * @inheritdoc
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function getPaymentInstruction($order)
    {
        $originalId = $order->getIncrementId();
        $shopgateId = $this->getShopgateOrderNumber($order);
        $order->setIncrementId($shopgateId);
        $instruction = parent::getPaymentInstruction($order);
        $order->setIncrementId($originalId);

        return $instruction;
    }

    /**
     * @inheritdoc
     */
    public function updateArticleList(Mage_Sales_Model_Order $order, $context)
    {
        $originalId = $order->getIncrementId();
        $shopgateId = $this->getShopgateOrderNumber($order);
        $order->setIncrementId($shopgateId);
        parent::updateArticleList($order, $context);
        $order->setIncrementId($originalId);

        return $this;
    }

    /**
     * If an observer fires when we are inserting an order we will
     * use the current ShopgateOrder in session to pass to order ID
     * instead of the incrementId to BillSAFE as our backend uses that
     * as the transaction ID. The secondary shopgateOrder->load is for
     * cases like capturing and refunds when the ShopgateOrder is not
     * in session anymore.
     *
     * @param Mage_Sales_Model_Order | Varien_Object $mageOrder - comes in as Varien on import, order from ship observer
     *
     * @return string | int
     */
    private function getShopgateOrderNumber(Varien_Object $mageOrder)
    {
        $sgOrder = Mage::registry('shopgate_order');
        if ($sgOrder instanceof ShopgateOrder) {
            $id = $sgOrder->getOrderNumber();
        } elseif ($mageOrder->getId()) {
            $sgOrder = Mage::getModel('shopgate/shopgate_order')->load($mageOrder->getId(), 'order_id');
            $id      = $sgOrder->getData('shopgate_order_number');
        }

        return !empty($id) ? $id : $mageOrder->getIncrementId();
    }
}
