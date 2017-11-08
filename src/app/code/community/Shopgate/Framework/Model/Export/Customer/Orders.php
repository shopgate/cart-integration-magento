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
class Shopgate_Framework_Model_Export_Customer_Orders extends Shopgate_Framework_Model_Export_Abstract
{
    /**
     * Getting orders for the customer filtered by given data
     *
     * @param string $customerToken
     * @param int    $limit
     * @param int    $offset
     * @param string $orderDateFrom
     * @param string $sortOrder
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    public function getOrders($customerToken, $limit, $offset, $orderDateFrom, $sortOrder)
    {
        $relation = Mage::getModel('shopgate/customer')->loadByToken($customerToken);
        $response = array();

        if ($relation->getId()) {
            $sort = str_replace('created_', '', $sortOrder);
            /** @var Mage_Sales_Model_Entity_Order_Collection $_orders */
            $_orders = Mage::getModel('sales/order')->getCollection()->addFieldToSelect('*');
            if ($orderDateFrom) {
                $_orders->addFieldToFilter(
                    'created_at',
                    array(
                        'from' => date('Y-m-d H:i:s', strtotime($orderDateFrom))
                    )
                );
            }
            $_orders->addFieldToFilter('customer_id', $relation->getCustomerId())
                    ->addFieldToFilter(
                        'state',
                        array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
                    )->setOrder('created_at', $sort);
            $_orders->getSelect()->limit($limit, $offset);
            if ($_orders->count() > 0) {
                /** @var Mage_Sales_Model_Order $order */
                foreach ($_orders as $order) {
                    $shopgateOrder         = $this->_getShopgateOrderNumber($order->getId());
                    $shopgateExternalOrder = new ShopgateExternalOrder();
                    $shopgateExternalOrder->setOrderNumber(
                        ($shopgateOrder) ? $shopgateOrder->getShopgateOrderNumber() : null
                    );
                    $shopgateExternalOrder->setExternalOrderId($order->getId());
                    $shopgateExternalOrder->setExternalOrderNumber($order->getIncrementId());
                    $shopgateExternalOrder->setCreatedTime(date(DateTime::ISO8601, strtotime($order->getCreatedAt())));
                    $shopgateExternalOrder->setMail($order->getCustomerEmail());
                    $shopgateExternalOrder->setCurrency($order->getOrderCurrencyCode());
                    $shopgateExternalOrder->setPaymentMethod($order->getPayment()->getMethodInstance()->getTitle());
                    $shopgateExternalOrder->setIsPaid(($shopgateOrder) ? $shopgateOrder->getIsPaid() : null);
                    $shopgateExternalOrder->setPaymentTransactionNumber($this->_getPaymentTransactionNumber($order));
                    $shopgateExternalOrder->setAmountComplete($order->getGrandTotal());
                    $shopgateExternalOrder->setInvoiceAddress(
                        $this->_getShopgateAddressFromOrderAddress($order->getBillingAddress())
                    );
                    $shopgateExternalOrder->setDeliveryAddress(
                        $this->_getShopgateAddressFromOrderAddress($order->getShippingAddress())
                    );
                    $shopgateExternalOrder->setItems($this->_getOrderItemsFormatted($order));
                    $shopgateExternalOrder->setOrderTaxes($this->_getOrderTaxFormatted($order));
                    $shopgateExternalOrder->setDeliveryNotes($this->_getDeliveryNotes($order));
                    $shopgateExternalOrder->setExternalCoupons($this->_getCouponsFormatted($order));
                    $shopgateExternalOrder->setStatusName(
                        Mage::getSingleton('sales/order_config')
                            ->getStatusLabel($order->getStatus())
                    );
                    $shopgateExternalOrder->setExtraCosts($this->_getExtraCost($order));

                    array_push($response, $shopgateExternalOrder);
                }
            }

            return $response;
        } else {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_CUSTOMER_TOKEN_INVALID);
        }
    }

    /**
     * @param $orderId
     *
     * @return false | Shopgate_Framework_Model_Shopgate_Order
     */
    protected function _getShopgateOrderNumber($orderId)
    {
        $shopgateOrder = Mage::getModel('shopgate/shopgate_order')->load($orderId, 'order_id');
        if ($shopgateOrder->getId()) {
            return $shopgateOrder;
        } else {
            return false;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return null|string
     */
    protected function _getPaymentTransactionNumber($order)
    {
        $transactionNumber = null;
        if ($order->getPayment()->getCcTransId()) {
            $transactionNumber = $order->getPayment()->getCcTransId();
        } elseif ($order->getPayment()->getLastTransId()) {
            $transactionNumber = $order->getPayment()->getLastTransId();
        } elseif ($order->getExtOrderId()) {
            $transactionNumber = $order->getExtOrderId();
        }

        return $transactionNumber;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return array
     */
    protected function _getShopgateAddressFromOrderAddress($address)
    {
        $shopgateAddress = new ShopgateAddress();
        $shopgateAddress->setFirstName($address->getFirstname());
        $shopgateAddress->setLastName($address->getLastname());
        $shopgateAddress->setGender(
            $this->_getCustomerHelper()->getShopgateCustomerGender($address)
        );
        $shopgateAddress->setCompany($address->getCompany());
        $shopgateAddress->setPhone($address->getTelephone());
        $shopgateAddress->setStreet1($address->getStreet1());
        $shopgateAddress->setStreet2($address->getStreet2());
        $shopgateAddress->setCity($address->getCity());
        $shopgateAddress->setZipcode($address->getPostcode());
        $shopgateAddress->setCountry($address->getCountry());
        $shopgateAddress->setState($this->_getHelper()->getIsoStateByMagentoRegion($address));

        return $shopgateAddress->toArray();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getOrderItemsFormatted($order)
    {
        $items    = array();
        $prodList = $this->getOriginalAddOrderProducts($order);

        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            // avoid child and parent products in list
            if (!$item->getParentItemId()) {
                /**
                 * When simple prod. import is enabled we need to lookup parent
                 */
                if (isset($prodList[$item->getProductId()])) {
                    $item->setProductId($prodList[$item->getProductId()]->getItemNumber());
                }
                $shopgateItem = new ShopgateExternalOrderItem();
                $shopgateItem->setItemNumber($this->getProductId($item));
                $shopgateItem->setItemNumberPublic($item->getSku());
                $shopgateItem->setQuantity((int)$item->getQtyOrdered());
                $shopgateItem->setName($item->getName());
                $shopgateItem->setUnitAmount($item->getPrice());
                $shopgateItem->setUnitAmountWithTax($item->getPriceInclTax());
                $shopgateItem->setTaxPercent($item->getTaxPercent());
                $shopgateItem->setCurrency($order->getOrderCurrencyCode());
                $shopgateItem->setDescription($item->getDescription());
                array_push($items, $shopgateItem);
            }
        }

        return $items;
    }

    /**
     * Returns the proper product ID to send to Shopgate
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return int | string
     */
    private function getProductId(Mage_Sales_Model_Order_Item $item)
    {
        if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && $item->getChildrenItems()
        ) {
            /** @var Mage_Sales_Model_Order_Item[] $children */
            $children  = $item->getChildrenItems();
            $productId = $item->getProductId() . '-' . $children[0]->getProductId();
        } else {
            $productId = $item->getProductId();
        }

        return $productId;
    }

    /**
     * If only simple product import is enabled the order
     * will need to lookup the original add order to figure
     * out which parent ID to reference
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return ShopgateOrderItem[]
     */
    private function getOriginalAddOrderProducts(Mage_Sales_Model_Order $order)
    {
        $prodList = array();
        if ($this->_getConfig()->addOnlySimplesToCart()) {
            /** @var Shopgate_Framework_Model_Shopgate_Order $order */
            $order = Mage::getModel('shopgate/shopgate_order')->load($order->getId(), 'order_id');
            if ($order->getId()) {
                /** @var ShopgateOrder $sgOrder */
                $sgOrder = unserialize($order->getReceivedData());
                foreach ($sgOrder->getItems() as $product) {
                    $info                          = Zend_Json_Decoder::decode($product->getInternalOrderInfo());
                    $prodList[$info['product_id']] = $product;
                }
            }
        }

        return $prodList;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getOrderTaxFormatted($order)
    {
        $taxObjects = array();
        $info       = $order->getFullTaxInfo();
        if (!empty($info)) {
            foreach ($info as $_tax) {
                $tax = new ShopgateExternalOrderTax();
                $tax->setAmount($_tax['amount']);
                $tax->setLabel($_tax['rates'][0]['title']);
                $tax->setTaxPercent((float)$_tax['percent']);
                array_push($taxObjects, $tax);
            }
        }

        return $taxObjects;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getDeliveryNotes($order)
    {
        $deliveryNotes = array();
        foreach ($order->getShipmentsCollection() as $shipment) {
            /** @var Mage_Sales_Model_Order_Shipment $shipment */
            foreach ($shipment->getAllTracks() as $track) {
                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                $note = new ShopgateDeliveryNote();
                $note->setShippingServiceId($track->getTitle());
                $note->setTrackingNumber($track->getNumber());
                $note->setShippingTime($track->getCreatedAt());
                array_push($deliveryNotes, $note);
            }
        }

        return $deliveryNotes;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getCouponsFormatted($order)
    {
        $result = array();
        if ($order->getCouponCode()) {
            if (Mage::helper('shopgate/config')->getIsMagentoVersionLower1410()) {
                $mageRule   = Mage::getModel('salesrule/rule')->load($order->getCouponCode(), 'coupon_code');
                $mageCoupon = $mageRule;
            } else {
                $mageCoupon = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
                $mageRule   = Mage::getModel('salesrule/rule')->load($mageCoupon->getRuleId());
            }

            $externalCoupon          = new ShopgateExternalCoupon();
            $couponInfo              = array();
            $couponInfo['coupon_id'] = $mageCoupon->getId();
            $couponInfo['rule_id']   = $mageRule->getId();

            $externalCoupon->setCode($order->getCouponCode());
            $externalCoupon->setCurrency($order->getOrderCurrencyCode());
            $externalCoupon->setName($mageRule->getName());
            $externalCoupon->setDescription($mageRule->getDescription());
            $externalCoupon->setInternalInfo($this->_getConfig()->jsonEncode($couponInfo));
            $externalCoupon->setAmount($order->getDiscountAmount());
            array_push($result, $externalCoupon);
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getExtraCost($order)
    {
        $result             = array();
        $shippingCostAmount = $order->getShippingAmount();

        if ($shippingCostAmount > 0) {
            $extraCost = new ShopgateExternalOrderExtraCost();
            $extraCost->setAmount($shippingCostAmount);
            $extraCost->setType(ShopgateExternalOrderExtraCost::TYPE_SHIPPING);
            $extraCost->setTaxPercent(
                Mage::helper('shopgate')->calculateTaxRate(
                    $shippingCostAmount,
                    $order->getShippingTaxAmount()
                )
            );

            $result[] = $extraCost;
        }

        $shopgatePaymentFee = $order->getShopgatePaymentFee();
        if ($shopgatePaymentFee > 0) {
            $extraCost = new ShopgateExternalOrderExtraCost();
            $extraCost->setAmount($shopgatePaymentFee);
            $extraCost->setType(ShopgateExternalOrderExtraCost::TYPE_PAYMENT);

            $result[] = $extraCost;
        }

        $codPaymentFee = $order->getCodFee();
        if ($codPaymentFee > 0) {
            $extraCost = new ShopgateExternalOrderExtraCost();
            $extraCost->setAmount($codPaymentFee);
            $extraCost->setType(ShopgateExternalOrderExtraCost::TYPE_PAYMENT);
            $extraCost->setTaxPercent(
                Mage::helper('shopgate')->calculateTaxRate($codPaymentFee, $order->getCodTaxAmount())
            );

            $result[] = $extraCost;
        }

        return $result;
    }
}
