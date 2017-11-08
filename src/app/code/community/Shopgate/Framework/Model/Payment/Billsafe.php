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
 * @deprecated v2.9.18 - use Shopgate_Framework_Model_Payment_Simple_Billsafe instead
 * @package    Shopgate_Framework_Model_Payment_Billsafe
 * @author     Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Billsafe extends Shopgate_Framework_Model_Payment_Simple
{
    /**
     * Backward compatible forwarder to new systmem
     *
     * @deprecated v2.9.18
     * @param Mage_Sales_Model_Order $order
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order, $shopgateOrder)
    {
        return Mage::getModel('shopgate/payment_simple_billsafe', array($shopgateOrder))
                   ->manipulateOrderWithPaymentData($order);
    }
}