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
class Shopgate_Framework_Model_Search_Shopgate_Order extends Varien_Object
{
    /**
     * Load search results
     *
     * @return Shopgate_Framework_Model_Search_Shopgate_Order
     */
    public function load()
    {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);

            return $this;
        }

        $collection = Mage::getModel('shopgate/shopgate_order')->getCollection()
                          ->addFieldToFilter("shopgate_order_number", array("like" => "%{$this->getQuery()}%"))
                          ->setCurPage($this->getStart())
                          ->setPageSize($this->getLimit());

        foreach ($collection as $sgOrder) {
            /** @var Mage_Sales_Model_Order $order */
            $order = $sgOrder->getOrder();

            $arr[] = array(
                'id'               => 'order/1/' . $order->getId(),
                'type'             => Mage::helper('adminhtml')->__('Order'),
                'name'             => Mage::helper('adminhtml')->__(
                    'Shopgate Order #%s',
                    $sgOrder->getShopgateOrderNumber()
                ),
                'description'      => $order->getBillingFirstname() . ' ' . $order->getBillingLastname(),
                'form_panel_title' => Mage::helper('adminhtml')->__(
                    'Order #%s (%s)',
                    $order->getIncrementId(),
                    $order->getBillingFirstname() . ' ' . $order->getBillingLastname()
                ),
                'url'              => Mage::helper('adminhtml')->getUrl(
                    '*/sales_order/view',
                    array('order_id' => $order->getId())
                ),
            );
        }

        $this->setResults($arr);

        return $this;
    }
}
