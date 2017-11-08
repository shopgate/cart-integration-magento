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
 * Class to manipulate the order payment data with amazon payment data
 *
 * @deprecated  v.2.9.18 - use Shopgate_Framework_Model_Payment_Pp_Wspp instead
 * @package     Shopgate_Framework_Model_Payment_Wspp
 * @author      Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Wspp
{
    /**
     * create new order for amazon payment
     *
     * @deprecated v.2.9.18
     * @param $quote            Mage_Sales_Model_Quote
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        return Mage::getModel('shopgate/payment_pp_wspp', array(new ShopgateOrder()))->createNewOrder($quote);
    }

    /**
     * @deprecated v.2.9.18
     * @param $order            Mage_Sales_Model_Order
     * @param $shopgateOrder    ShopgateOrder
     *                          // TODO Refund
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order, $shopgateOrder)
    {
        return Mage::getModel('shopgate/payment_pp_wspp', array($shopgateOrder))->manipulateOrderWithPaymentData(
            $order
        );
    }

    /**
     * @deprecated v.2.9.18
     * @param $quote            Mage_Sales_Model_Quote
     * @param $data             array
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $data)
    {
        return Mage::getModel('shopgate/payment_pp_wspp', array(new ShopgateOrder()))->prepareQuote($quote, $data);
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Wspp
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_wspp');
    }
}