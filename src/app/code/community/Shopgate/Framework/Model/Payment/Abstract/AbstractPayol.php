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
class Shopgate_Framework_Model_Payment_Abstract_AbstractPayol extends Shopgate_Framework_Model_Payment_Abstract
{

    const TRANS_TYPE_AUTH = 'authorize';
    const TRANS_TYPE_CAPTURE = 'capture';

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        $convert = Mage::getModel('sales/convert_quote');

        if ($quote->isVirtual()) {
            $this->setOrder($convert->addressToOrder($quote->getBillingAddress()));
        } else {
            $this->setOrder($convert->addressToOrder($quote->getShippingAddress()));
        }
        $this->getOrder()->setBillingAddress($convert->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $this->getOrder()->getBillingAddress()->setCustomerAddress(
                $quote->getBillingAddress()->getCustomerAddress()
            );
        }
        if (!$quote->isVirtual()) {
            $this->getOrder()->setShippingAddress($convert->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $this->getOrder()->getShippingAddress()->setCustomerAddress(
                    $quote->getShippingAddress()->getCustomerAddress()
                );
            }
        }
        $this->getOrder()->setPayment($convert->paymentToOrderPayment($quote->getPayment()));
        $this->getOrder()->getPayment()->setTransactionId($quote->getPayment()->getTransactionId());

        foreach ($quote->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $orderItem = $convert->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($this->getOrder()->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $this->getOrder()->addItem($orderItem);
        }
        $this->getOrder()->setQuote($quote);
        $this->getOrder()->setCanSendNewEmailFlag(false);

        $this->_initTransaction($quote);

        return $this->getOrder();
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($magentoOrder)
    {
        $info = $this->getShopgateOrder()->getPaymentInfos();
        if (!empty($info['capture_id'])) {
            $this->_createTransaction(self::TRANS_TYPE_AUTH);
            $this->_createTransaction(self::TRANS_TYPE_CAPTURE);
        } elseif (!empty($info['preauth_id'])) {
            $this->_createTransaction(self::TRANS_TYPE_AUTH);
        } else {
            $this->_createTransaction();
        }

        return $this->getOrder();
    }

    /**
     * Defaulting to using Payoli's status implementation
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $this->setOrder($magentoOrder);
        $status          = '';
        $amountToCapture = $this->getShopgateOrder()->getAmountComplete();
        $payment         = $this->getOrder()->getPayment();
        if ($payment->getIsTransactionPending()) {
            $message = Mage::helper('sales')->__(
                '%sing amount of %s is pending approval on gateway.',
                $this->_getTransactionAction(),
                $this->getOrder()->getBaseCurrency()->formatTxt($amountToCapture)
            );
            $state   = $this->_getHelper()->getStateForStatus('payment_review');
            if ($payment->getIsFraudDetected()) {
                $status = Mage_Sales_Model_Order::STATE_HOLDED;
            }
        } else {
            $status  = Mage::getStoreConfig($this->getConstant('XML_CONFIG_STATUS_PAID'));
            $state   = $this->_getHelper()->getStateForStatus($status);
            $message = Mage::helper('sales')->__(
                '%sed amount of %s online.',
                $this->_getTransactionAction(),
                $this->getOrder()->getBaseCurrency()->formatTxt($amountToCapture)
            );
        }
        if (!$status || !$state) {
            $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
            $status = $this->_getHelper()->getStatusFromState($state);
        }
        $this->getOrder()->setState($state, $status, $message);

        return $this->getOrder()->setShopgateStatusSet(true);
    }

    /**
     * Checks if we have all the correct data passed.
     * Skips check if call is made via redeem_coupons
     * or check_cart.
     *
     * @return bool
     */
    public function checkGenericValid()
    {
        if ($this->getShopgateOrder() instanceof ShopgateCart) {
            return true;
        }

        $info = $this->getShopgateOrder()->getPaymentInfos();

        if (!isset($info['unique_id'])) {
            $error = $this->_getHelper()->__('Unique ID was missing in paymentInfo of add_order');
            ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Initial transaction binding to order
     *
     * @param $quote
     *
     * @return Mage_Core_Model_Resource_Transaction
     * @throws Exception
     */
    protected function _initTransaction($quote)
    {
        $transaction = Mage::getModel('core/resource_transaction');

        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }

        $transaction->addObject($quote);
        $transaction->addObject($this->getOrder());
        $transaction->addCommitCallback(array($this->getOrder(), 'save'));

        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order' => $this->getOrder(), 'quote' => $quote));
        Mage::dispatchEvent(
            'sales_model_service_quote_submit_before',
            array('order' => $this->getOrder(), 'quote' => $quote)
        );

        try {
            $transaction->save();
            Mage::dispatchEvent(
                'sales_model_service_quote_submit_success',
                array(
                    'order' => $this->getOrder(),
                    'quote' => $quote
                )
            );
        } catch (Exception $e) {
            //reset order ID's on exception, because order not saved
            $this->getOrder()->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($this->getOrder()->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent(
                'sales_model_service_quote_submit_failure',
                array(
                    'order' => $this->getOrder(),
                    'quote' => $quote
                )
            );
            throw $e;
        }
        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $this->getOrder(), 'quote' => $quote));
        Mage::dispatchEvent(
            'sales_model_service_quote_submit_after',
            array('order' => $this->getOrder(), 'quote' => $quote)
        );

        return $transaction;
    }

    /**
     * On capture creates 2 transactions: auth & capture
     * Authorization creates just one: auth
     * Nothing creates a blank transaction - not tested
     *
     * @param string | null $type
     */
    protected function _createTransaction($type = null)
    {
        $info    = $this->getShopgateOrder()->getPaymentInfos();
        $transId = $type === self::TRANS_TYPE_AUTH ? $info['preauth_id'] : $info['unique_id'];
        $trans   = Mage::getModel('sales/order_payment_transaction');
        $trans->setOrderPaymentObject($this->getOrder()->getPayment());
        $trans->setTxnId($transId);
        $trans->setIsClosed(false);

        try {
            if ($type === self::TRANS_TYPE_CAPTURE) {
                $invoice = $this->_getPaymentHelper()->createOrderInvoice($this->getOrder());
                $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
                $trans->setParentId($info['preauth_id']);

                if ($this->getOrder()->getPayment()->getIsTransactionPending()) {
                    $invoice->setIsPaid(false);
                } else {
                    $invoice->setIsPaid(true);
                    $invoice->pay();
                }
                $invoice->setTransactionId($transId);
                $invoice->save();
                $this->getOrder()->addRelatedObject($invoice);
                $this->getOrder()->getPayment()->setParentTransactionId($info['preauth_id']);
            } elseif ($type === self::TRANS_TYPE_AUTH) {
                $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
                $this->getOrder()->getPayment()->setAmountAuthorized($this->getOrder()->getTotalDue());
            }

            $trans->save();
            $this->getOrder()->getPayment()->setTransactionAdditionalInfo(
                $this->_getPaymentHelper()->getTransactionRawDetails(),
                $info
            );
            $this->getOrder()->getPayment()->setTransactionId($transId)->setIsTransactionClosed(0);
            $this->getOrder()->getPayment()->setLastTransId($transId);

        } catch (Exception $x) {
            Mage::logException($x);
        }
    }

    /**
     * Helps get the correct status action
     * based on the type of transaction
     *
     * @return string
     */
    protected function _getTransactionAction()
    {
        $info = $this->getShopgateOrder()->getPaymentInfos();

        if (!empty($info['capture_id'])) {
            return 'Captur';
        }

        return 'Authoriz';
    }
}
