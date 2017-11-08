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
 * @deprecated  v.2.9.18 - use Shopgate_Framework_Model_Payment_Cc_Usaepay instead
 * @package     Shopgate_Framework_Model_Payment_Usaepay
 * @author      Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Usaepay
{
    /**
     * Use Shopgate_Framework_Model_Payment_Cc_Usaepay instead
     *
     * @deprecated  v.2.9.18
     * @param $order            Mage_Sales_Model_Order
     * @param $shopgateOrder    ShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order, $shopgateOrder)
    {
        return Mage::getModel('shopgate/payment_cc_usaepay', array($shopgateOrder))->manipulateOrderWithPaymentData(
            $order
        );
    }

}