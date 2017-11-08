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
 * Deprecating as all simple payment methods will be handled inside Simple folder
 *
 * @deprecated  v.2.9.18 - use Shopgate_Framework_Model_Payment_Simple_Mws instead
 * @package     Shopgate_Framework_Model_Payment_Amazon
 * @author      Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Amazon
{
    /**
     * Passing a fake ShopgateOrder to avoid error thrown
     *
     * @deprecated v.2.9.18
     *
     * @param $quote            Mage_Sales_Model_Quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        return Mage::getModel('shopgate/payment_simple_mws', array(new ShopgateOrder()))->createNewOrder($quote);
    }

    /**
     *
     * @deprecated v.2.9.18
     *
     * @param $order            Mage_Sales_Model_Order
     * @param $shopgateOrder    ShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order, $shopgateOrder)
    {
        return Mage::getModel('shopgate/payment_simple_mws', array($shopgateOrder))
                   ->manipulateOrderWithPaymentData($order);
    }

    /**
     * Passing a fake ShopgateOrder to avoid error thrown
     *
     * @deprecated v.2.9.18
     *
     * @param $quote    Mage_Sales_Model_Quote
     * @param $payment  Mage_Payment_Model_Method_Abstract|Creativestyle_AmazonPayments_Model_Payment_Advanced
     * @param $info     array
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $payment, $info)
    {
        return Mage::getModel('shopgate/payment_simple_mws', array(new ShopgateOrder()))->prepareQuote($quote, $info);
    }
}

