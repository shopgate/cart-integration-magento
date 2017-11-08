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
 * Payment handler for Creativestyle_AmazonPayments
 */
class Shopgate_Framework_Model_Payment_Simple_Mws_Abstract
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::AMAZON_PAYMENT;
    const MODULE_CONFIG = 'Creativestyle_AmazonPayments';
    const PAYMENT_MODEL = 'amazonpayments/payment_advanced';
    const XML_CONFIG_ENABLED = 'amazonpayments/general/active';

    /**
     * create new order for amazon payment
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
        $order->getPayment()->setTransactionId($quote->getPayment()->getTransactionId());
        $order->getPayment()->setAdditionalInformation(
            'amazon_order_reference_id',
            $quote->getPayment()
                  ->getTransactionId()
        );

        foreach ($quote->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $orderItem = $convert->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }
        $order->setQuote($quote);
        $order->setExtOrderId($quote->getPayment()->getTransactionId());
        $order->setCanSendNewEmailFlag(false);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'save'));

        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order' => $order, 'quote' => $quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order' => $order, 'quote' => $quote));

        try {
            $transaction->save();
            Mage::dispatchEvent(
                'sales_model_service_quote_submit_success',
                array(
                    'order' => $order,
                    'quote' => $quote
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
                    'order' => $order,
                    'quote' => $quote
                )
            );
            throw $e;
        }
        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order' => $order, 'quote' => $quote));

        return $this->setOrder($order);
    }

    /**
     * @param $order Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();
        try {
            $orderTrans = Mage::getModel('sales/order_payment_transaction');
            $orderTrans->setOrderPaymentObject($order->getPayment());
            $orderTrans->setIsClosed(false);
            $orderTrans->setTxnId($paymentInfos['mws_order_id']);
            $orderTrans->setTxnType($this->_getTransactionType());
            $orderTrans->save();
            $this->_importTransactionInfo($orderTrans);
            $order->getPayment()->setLastTransId($paymentInfos['mws_order_id']);

            if (!empty($paymentInfos['mws_auth_id'])) {
                $authTrans = Mage::getModel('sales/order_payment_transaction');
                $authTrans->setOrderPaymentObject($order->getPayment());
                $authTrans->setParentTxnId($orderTrans->getTxnId(), $paymentInfos['mws_auth_id']);
                $authTrans->setIsClosed(false);
                $authTrans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
                $authTrans->save();
                $this->_importTransactionInfo($authTrans);
                $order->getPayment()->setAmountAuthorized($order->getTotalDue());
                $order->getPayment()->setLastTransId($paymentInfos['mws_auth_id']);

                if (!empty($paymentInfos['mws_capture_id'])) {
                    $transaction = Mage::getModel('sales/order_payment_transaction');
                    $transaction->setOrderPaymentObject($order->getPayment());
                    $transaction->setParentTxnId($authTrans->getTxnId(), $paymentInfos['mws_capture_id']);
                    $transaction->setIsClosed(false);
                    $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
                    $transaction->save();
                    $this->_importTransactionInfo($transaction);
                    $order->getPayment()->capture(null);
                    /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
                    $invoiceCollection = $order->getInvoiceCollection();
                    $invoiceCollection->getFirstItem()->setTransactionId($paymentInfos['mws_capture_id']);
                    $order->getPayment()->setAmountAuthorized($order->getTotalDue());
                    $order->getPayment()->setBaseAmountAuthorized($order->getBaseTotalDue());
                    $order->getPayment()->setLastTransId($paymentInfos['mws_capture_id']);
                }
            }
        } catch (Exception $x) {
            Mage::logException($x);
        }

        return $order;
    }

    /**
     * @param $quote    Mage_Sales_Model_Quote
     * @param $info     array
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $info)
    {
        $payment = $this->getPaymentModel();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($payment->getCode() ? $payment->getCode() : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod($payment->getCode() ? $payment->getCode() : null);
        }

        $data = array(
            'method' => $payment->getCode(),
            'checks' => Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_USE_FOR_COUNTRY
                        | Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_USE_FOR_CURRENCY
                        | Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
        );

        $quote->getPayment()->importData($data);
        $quote->getPayment()->setTransactionId($info['mws_order_id']);

        return $quote;
    }

    /**
     * Set order state if non is set,
     * else ignore as Amazon plugin handles all that
     *
     * @param $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        //backup for potential plugin lower version malfunctions
        if (!$magentoOrder->getState()) {
            $magentoOrder->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING
            );
        }
        $magentoOrder->setShopgateStatusSet(true);

        return $magentoOrder;
    }

    /**
     * Gets a payment type that is supported in
     * higher versions of magento only
     *
     * @see fallback in Mws15.php
     *
     * @return mixed
     */
    protected function _getTransactionType()
    {
        return Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER;
    }

    /**
     * Imports transaction into the order
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     *
     * @return $this
     */
    protected function _importTransactionInfo($transaction)
    {
        return $this->getOrder()->getPayment()->importTransactionInfo($transaction);
    }
}