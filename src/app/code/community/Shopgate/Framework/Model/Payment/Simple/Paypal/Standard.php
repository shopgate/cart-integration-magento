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
class Shopgate_Framework_Model_Payment_Simple_Paypal_Standard
    extends Shopgate_Framework_Model_Payment_Pp_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::PAYPAL;
    const XML_CONFIG_ENABLED = 'payment/paypal_standard/active';
    const PAYMENT_MODEL = 'paypal/standard';

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($magentoOrder)
    {
        $magentoOrder = parent::manipulateOrderWithPaymentData($magentoOrder);

        $info        = $this->getShopgateOrder()->getPaymentInfos();

        $transaction = $this->_createTransaction($magentoOrder);
        $magentoOrder->getPayment()->importTransactionInfo($transaction);
        $magentoOrder->getPayment()->setLastTransId($info[self::TYPE_TRANS_ID]);

        return $magentoOrder;
    }


    /**
     * Handles magento transaction creation
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return mixed
     */
    protected function _createTransaction($magentoOrder)
    {
        $info        = $this->getShopgateOrder()->getPaymentInfos();
        $transaction = Mage::getModel('sales/order_payment_transaction');
        try {
            $transaction->setOrderPaymentObject($magentoOrder->getPayment());
            if ($magentoOrder->getBaseTotalDue()) {
                $transaction->setIsClosed(false);
            } else {
                $transaction->setIsClosed(true);
            }
            $transaction->setTxnId($info[self::TYPE_TRANS_ID]);
            if ($this->getShopgateOrder()->getIsPaid()) {
                $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
            } else {
                $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
            }
        } catch (Exception $x) {
            ShopgateLogger::getInstance()->log($x->getMessage());
        }

        Mage::dispatchEvent('sales_order_place_before', array(self::TYPE_ORDER => $magentoOrder));
        $transaction->save();
        Mage::dispatchEvent('sales_order_place_after', array(self::TYPE_ORDER => $magentoOrder));

        return $transaction;
    }

    /**
     * Get status with gateway
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $info = $this->getShopgateOrder()->getPaymentInfos();

        return $this->orderStatusManager($magentoOrder, $info['payment_status']);
    }
}
