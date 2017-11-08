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
 * @method int getShopgateOrderId()
 * @method Shopgate_Framework_Model_Shopgate_Order setStoreId(int $value)
 * @method int getStoreId()
 * @method int getOrderId()
 * @method Shopgate_Framework_Model_Shopgate_Order setOrderId(string $value)
 * @method string getShopgateOrderNumber()
 * @method Shopgate_Framework_Model_Shopgate_Order setShopgateOrderNumber(string $value)
 * @method bool getIsShippingBlocked()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsShippingBlocked(bool $value)
 * @method bool getIsPaid()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsPaid(bool $value)
 * @method bool getIsSentToShopgate()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsSentToShopgate(bool $value)
 * @method bool getIsCancellationSentToShopgate()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsCancellationSentToShopgate(bool $value)
 * @method string getReceivedData()
 * @method Shopgate_Framework_Model_Shopgate_Order setReceivedData(string $value)
 * @method bool getIsTest()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsTest(bool $value)
 * @method bool getIsCustomerInvoiceBlocked()
 * @method Shopgate_Framework_Model_Shopgate_Order setIsCustomerInvoiceBlocked(bool $value)
 */
class Shopgate_Framework_Model_Shopgate_Order extends Mage_Core_Model_Abstract
{
    /**
     * Init Shopgate model
     */
    protected function _construct()
    {
        $this->_init('shopgate/shopgate_order');
    }

    /**
     * @param Varien_Object $value
     *
     * @return Shopgate_Framework_Model_Shopgate_Order
     */
    public function setOrder(Varien_Object $value)
    {
        return $this->setOrderId($value);
    }

    /**
     * Retrieves the Shopgate order object from
     * the database column
     *
     * @return string | ShopgateOrder
     */
    public function getShopgateOrderObject()
    {
        $data = $this->getReceivedData();

        if ($data) {
            $data = unserialize($data);
        }

        if ($this->getId() && !$data instanceof ShopgateOrder) {
            $orderNumber = $this->getShopgateOrderNumber();
            ShopgateLogger::getInstance()->log("Could not unserialize order $orderNumber. Requesting Merchant API.");
            $config      = Mage::helper('shopgate/config')->getConfig($this->getStoreId());
            $builder     = new ShopgateBuilder($config);
            $merchantApi = $builder->buildMerchantApi();
            $response    = $merchantApi->getOrders(array('order_numbers[0]' => $orderNumber, 'with_items' => 1));
            $dataArray   = $response->getData();
            $data        = $dataArray[0];
            $this->setReceivedData(serialize($data));
            $this->save();
            ShopgateLogger::getInstance()->log("Got order $orderNumber again. Saved to database");
        }

        return $data;
    }

    /**
     * Get all shipments for the order
     *
     * @return array
     */
    public function getReportedShippingCollections()
    {
        $data = $this->getData('reported_shipping_collections');
        $data = unserialize($data);
        if (!$data) {
            $data = array();
        }

        return $data;
    }

    /**
     * @param array $collection_ids
     *
     * @return Shopgate_Framework_Model_Shopgate_Order
     */
    public function setReportedShippingCollections(array $collection_ids)
    {
        $collection_ids = serialize($collection_ids);
        $this->setData('reported_shipping_collections', $collection_ids);

        return $this;
    }

    /**
     * @param null $order
     *
     * @return bool
     */
    public function hasShippedItems($order = null)
    {
        if (!$order) {
            $order = $this->getOrder();
        }

        $shippedItems = false;
        foreach ($order->getItemsCollection() as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getQtyShipped() > 0) {
                $shippedItems = true;
                break;
            }
        }

        return $shippedItems;
    }

    /**
     * Return real order from shopgate order if exists
     *
     * @return Mage_Sales_Model_Order|NULL
     */
    public function getOrder()
    {
        if ($this->getOrderId() !== null) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return Mage::getModel('sales/order')->load($this->getOrderId());
        }

        return null;
    }

    /**
     * @param null $order
     *
     * @return bool
     */
    public function hasItemsToShip($order = null)
    {
        if (!$order) {
            $order = $this->getOrder();
        }

        $itemsToShip = false;
        foreach ($order->getItemsCollection() as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getQtyToShip() > 0 && $orderItem->getProductId() != null) {
                $itemsToShip = true;
                break;
            }
        }

        return $itemsToShip;
    }
}
