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
 * Handles SHOPGATE payment method orders
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Shopgate
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::SHOPGATE;
    const MODULE_CONFIG = 'Shopgate_Framework';
    const PAYMENT_MODEL = 'shopgate/payment_shopgate';
    const XML_CONFIG_STATUS_PAID = 'payment/shopgate/order_status';

    /**
     * If shipping is blocked, use Shopgate Payment config status.
     * If not blocked, set to Processing.
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        if ($this->getShopgateOrder()->getIsShippingBlocked()) {
            return parent::setOrderStatus($magentoOrder);
        }

        $message = $this->_getHelper()->__('[SHOPGATE] Using default order status');
        $state   = Mage_Sales_Model_Order::STATE_PROCESSING;

        $magentoOrder->setState($state, true, $message);
        $magentoOrder->setShopgateStatusSet(true);

        return $magentoOrder;
    }

    /**
     * No need to check activation, just import!
     *
     * @return true
     */
    public function isEnabled()
    {
        return true;
    }
}