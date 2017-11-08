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
 * Fallback handler for all SUE payment method needs.
 *
 * Class Shopgate_Framework_Model_Payment_Simple_Sue_Abstract
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Sue_Abstract extends Shopgate_Framework_Model_Payment_Abstract
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::SUE;
    const MODULE_CONFIG = 'Paymentnetwork_Pnsofortueberweisung';

    /**
     * Add invoice to a paid order
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function manipulateOrderWithPaymentData($magentoOrder)
    {
        $shopgateOrder = $this->getShopgateOrder();

        if ($shopgateOrder->getIsPaid()) {
            $invoice = $this->_getPaymentHelper()->createOrderInvoice($magentoOrder);
            $invoice->setIsPaid(true);
            $invoice->pay();
            $invoice->save();
            $magentoOrder->addRelatedObject($invoice);
        }

        $this->setTransactionId($magentoOrder);

        return parent::manipulateOrderWithPaymentData($magentoOrder);
    }

    /**
     * Sets order status backup implementation
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $magentoOrder = parent::setOrderStatus($magentoOrder);

        /**
         * Old versions where status is not set by default
         */
        if (!$magentoOrder->getShopgateStatusSet()) {
            $state   = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $status  = $this->_getHelper()->getStatusFromState($state);
            $message = $this->_getHelper()->__('[SHOPGATE] Using default status as no native plugin status is set');
            $magentoOrder->setState($state, $status, $message);
            $magentoOrder->setShopgateStatusSet(true);
        }

        return $magentoOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     */
    protected function setTransactionId(Mage_Sales_Model_Order $magentoOrder)
    {
    }
}
