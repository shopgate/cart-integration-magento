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
class Shopgate_Framework_Helper_Billsafe_Order extends Netresearch_Billsafe_Helper_Order
{
    /** @var  Mage_Sales_Model_Order */
    protected $_order;

    /**
     * @param Mage_Sales_Model_Abstract $entity
     * @param Mage_Sales_Model_Order    $order
     * @param string                    $context
     *
     * @return mixed
     */
    protected function getAllOrderItems($entity, $order, $context)
    {
        $this->_order = $order;

        if ($context == self::TYPE_RS) {
            return $entity->getAllItems();
        }

        return $order->getAllItems();
    }

    /**
     * @param $orderItems
     * @param $amount
     * @param $taxAmount
     * @param $context
     *
     * @return array
     */
    protected function getOrderItemData($orderItems, $amount, $taxAmount, $context)
    {
        if (!$this->_order->getIsShopgateOrder()) {
            /**
             * No shopgate billsafe order - use original method
             */
            return parent::getOrderItemData($orderItems, $amount, $taxAmount, $context);
        }

        $data = array(
            'amount'     => 0,
            'tax_amount' => 0
        );

        $addedBundleProduct = array();

        foreach ($orderItems as $item) {

            if ($this->getHelper()->isFeeItem($item)) {
                $data['payment_fee_item'] = $item;
                continue;
            }
            $qty = $item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled();

            if (self::TYPE_VO == $context) {
                $qty = (int)$item->getQtyShipped();
            }

            if ($context == self::TYPE_RS) {
                $qty = $item->getQty();
                if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
                    $item = $item->getOrderItem();
                }
            }

            if ($item->isDummy() || $qty <= 0) {
                continue;
            }

            $productOptions = $item->getProductOptions();
            $skip           = false;

            if ($item->getParentItemId()) {
                $parentItem = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                $name       = $parentItem->getName();
                $grossPrice = $parentItem->getPriceInclTax();

                if ($parentItem->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    if (in_array($parentItem->getItemId(), $addedBundleProduct)) {
                        $skip = true;
                    }

                    $number = $productOptions['info_buyRequest']['product'];
                    array_push($addedBundleProduct, $parentItem->getItemId());
                }

            } else {
                if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $simpleSalesOrderItem = Mage::getModel('sales/order_item')
                                                ->getCollection()
                                                ->addFieldToFilter(
                                                    'parent_item_id',
                                                    array('eq' => $item->getItemId())
                                                )
                                                ->addFieldToFilter('order_id', array('eq' => $item->getOrderId()));

                    $number = sprintf(
                        '%d-%d',
                        $productOptions['info_buyRequest']['product'],
                        $simpleSalesOrderItem->getFirstItem()->getProductId()
                    );

                } else {
                    $number = $productOptions['info_buyRequest']['product'];
                }

                $name       = $item->getName();
                $grossPrice = $item->getPriceInclTax();
            }

            if (!$skip) {
                $data['data'][]     = array(
                    'number'          => substr($number, 0, 50),
                    'name'            => $name,
                    'type'            => 'goods',
                    'quantity'        => (int)$qty,
                    'quantityShipped' => (int)$item->getQtyShipped(),
                    'grossPrice'      => $this->getHelper()->format($grossPrice),
                    'tax'             => $this->getHelper()->format(
                        $item->getTaxPercent()
                    ),
                );
                $data['amount']     += $amount + $grossPrice * $qty;
                $data['tax_amount'] += $taxAmount + $item->getTaxAmount() * $qty;
            }
        }

        return $data;
    }
}
