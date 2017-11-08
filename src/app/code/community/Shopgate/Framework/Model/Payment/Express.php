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
 * This class is no longer needed due to the payment system changes. We are keeping this class
 * to be compatible with our own adaptions that overwrite methods in Plugin.php that still call
 * this class
 *
 * @deprecated  v.2.9.18 - use Shopgate_Framework_Model_Payment_Simple_Paypal_Express instead
 * @package     Shopgate_Framework_Model_Payment_Express
 * @author      Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Express
{

    /**
     * Create new order for paypal express (type wspp)
     *
     * @deprecated v.2.9.18 - in case adaptions still use this classes
     *
     * @param $quote            Mage_Sales_Model_Quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        return Mage::getModel('shopgate/payment_simple_paypal_express', array(new ShopgateOrder()))->createNewOrder(
            $quote
        );
    }

    /**
     * @deprecated v.2.9.18
     *
     * @param $order            Mage_Sales_Model_Order
     * @param $shopgateOrder    ShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order, $shopgateOrder)
    {
        return Mage::getModel(
            'shopgate/payment_simple_paypal_express',
            array($shopgateOrder)
        )->manipulateOrderWithPaymentData($order);
    }

    /**
     * @deprecated v.2.9.18
     *
     * @param $quote            Mage_Sales_Model_Quote
     * @param $data             array
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $data)
    {
        return Mage::getModel('shopgate/payment_simple_paypal_express', array(new ShopgateOrder()))->prepareQuote(
            $quote,
            $data
        );
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Wspp
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_wspp');
    }
}