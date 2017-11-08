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

/**
 * General payment interface functions required to exist
 */
interface Shopgate_Framework_Model_Payment_Interface
{
    /**
     * Used for setup of any kind
     */
    public function setUp();

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order);

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     */
    public function createNewOrder($quote);

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param array                  $data
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $data);

    /**
     * Used to set magento order status
     *
     * @param $magentoOrder
     * @return mixed
     */
    public function setOrderStatus($magentoOrder);
}