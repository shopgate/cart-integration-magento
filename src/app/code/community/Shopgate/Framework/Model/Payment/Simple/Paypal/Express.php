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
class Shopgate_Framework_Model_Payment_Simple_Paypal_Express
    extends Shopgate_Framework_Model_Payment_Pp_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::PAYPAL;
    const XML_CONFIG_ENABLED = 'payment/paypal_express/active';
    const PAYMENT_MODEL = 'paypal/express';

    /**
     * Create new order for paypal express (type wspp)
     *
     * @param $quote            Mage_Sales_Model_Quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        $convert     = Mage::getModel('sales/convert_quote');
        $transaction = Mage::getModel('core/resource_transaction');

        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $quote->setTotalsCollectedFlag(true);
        $transaction->addObject($quote);
        if ($quote->isVirtual()) {
            $order = $convert->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $convert->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($convert->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$quote->isVirtual()) {
            $order->setShippingAddress($convert->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
            }
        }

        $order->setPayment($convert->paymentToOrderPayment($quote->getPayment()));
        $order->getPayment()->setAmountOrdered($order->getTotalDue());
        $order->getPayment()->setShippingAmount($order->getShippingAmount());
        $order->getPayment()->setAmountAuthorized($order->getTotalDue());
        $order->getPayment()->setBaseAmountOrdered($order->getBaseTotalDue());
        $order->getPayment()->setBaseShippingAmount($order->getBaseShippingAmount());
        $order->getPayment()->setBaseAmountAuthorized($order->getBaseTotalDue());
        $order->getPayment()->setTransactionId($quote->getPayment()->getTransactionId());

        foreach ($quote->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $orderItem = $convert->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }
        $order->setQuote($quote);
        $order->setCanSendNewEmailFlag(false);
        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'save'));

        try {
            Mage::dispatchEvent('sales_order_place_before', array(self::TYPE_ORDER => $order));
            $transaction->save();
            Mage::dispatchEvent('sales_order_place_after', array(self::TYPE_ORDER => $order));
            Mage::dispatchEvent(
                'sales_model_service_quote_submit_success',
                array(
                    self::TYPE_ORDER => $order,
                    self::TYPE_QUOTE => $quote
                )
            );
        } catch (Exception $e) {
            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent(
                'sales_model_service_quote_submit_failure',
                array(
                    self::TYPE_ORDER => $order,
                    self::TYPE_QUOTE => $quote
                )
            );
            throw $e;
        }
        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }

    /**
     * @param $order            Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfos  = $this->getShopgateOrder()->getPaymentInfos();
        $paymentStatus = $this->_getPaymentHelper()->filterPaymentStatus($paymentInfos['payment_status']);
        $trans         = Mage::getModel('sales/order_payment_transaction');
        $trans->setOrderPaymentObject($order->getPayment());
        $trans->setTxnId($paymentInfos[self::TYPE_TRANS_ID]);
        $trans->setIsClosed(false);

        try {
            switch ($paymentStatus) {
                // paid
                case $this->_getPaymentHelper()->getPaypalCompletedStatus():
                    $invoice = $this->_getPaymentHelper()->createOrderInvoice($order);
                    $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);

                    if ($order->getPayment()->getIsTransactionPending()) {
                        $invoice->setIsPaid(false);
                    } else {
                        // normal online capture: invoice is marked as "paid"
                        $order->getPayment()->setBaseAmountPaidOnline($invoice->getBaseGrandTotal());
                        $invoice->setIsPaid(true);
                        $invoice->pay();
                    }
                    $invoice->setTransactionId($paymentInfos[self::TYPE_TRANS_ID]);
                    $invoice->save();
                    $order->addRelatedObject($invoice);
                    break;
                case $this->_getPaymentHelper()->getPaypalPendingStatus():
                    if (isset($paymentInfos['reason_code'])) {
                        $order->getPayment()->setIsTransactionPending(true);
                        $order->getPayment()->setIsFraudDetected(true);
                    }
                    $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
                    break;
                default:
                    Mage::throwException("Cannot handle payment status '{$paymentStatus}'.");
            }
            $trans->save();
            $this->_getPaymentHelper()->importPaymentInformation($order->getPayment(), $paymentInfos);
            /** @noinspection PhpParamsInspection */
            $order->getPayment()->setTransactionAdditionalInfo(
                $this->_getPaymentHelper()->getTransactionRawDetails(),
                $paymentInfos
            );
            $order->getPayment()->setLastTransId($paymentInfos[self::TYPE_TRANS_ID]);
        } catch (Exception $x) {
            $comment = $this->_getPaymentHelper()->createIpnComment(
                $order,
                Mage::helper('paypal')->__('Note: %s', $x->getMessage()),
                true
            );
            $comment->save();
            Mage::logException($x);
        }

        return $order;
    }

    /**
     * Set order status, rewrite if matches conditions
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $state        = Mage_Sales_Model_Order::STATE_PROCESSING;
        $magentoOrder = $this->orderStatusManager($magentoOrder);

        if ($this->getShopgateOrder()->getIsPaid() == 1 && $magentoOrder->getState() !== $state) {
            $magentoOrder->setState(
                $state,
                $state,
                $this->_getPaymentHelper()
                     ->__('[SHOPGATE] Import received as paid, forcing state: ' . $state)
            );
        }
        $magentoOrder->setShopgateStatusSet(true);

        return $magentoOrder;
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Wspp
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_wspp');
    }

}
