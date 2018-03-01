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
class Shopgate_Framework_Model_Payment_Braintr_Abstract
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const ADVANCED_FRAUD_REVIEW  = 'Review';
    const ADVANCED_FRAUD_DECLINE = 'Decline';

    /**
     * @var bool
     */
    protected $isPaid = true;

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $transaction Mage_Core_Model_Resource_Transaction */
        $transaction = Mage::getModel('core/resource_transaction');

        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }

        $transaction->addObject($quote);
        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();
        $quote->getPayment()->setTransactionId($paymentInfos['transaction_id']);

        /* @var $order Mage_Sales_Model_Order */
        if ($quote->isVirtual()) {
            $order = $convertQuote->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $convertQuote->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$quote->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
            }
        }
        $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
        $order->getPayment()->setTransactionId($quote->getPayment()->getTransactionId());

        foreach ($quote->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $orderItem = $convertQuote->itemToOrderItem($item);
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
                    'quote' => $quote,
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
                    'quote' => $quote,
                )
            );
            throw $e;
        }
        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order' => $order, 'quote' => $quote));

        return $this->setOrder($order);
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array                          $riskData
     */
    protected function _handleFraud($payment, $riskData)
    {
        if (!isset($riskData['id']) || !isset($riskData['decision'])) {

            return;
        }

        $paymentInfo = $this->getShopgateOrder()->getPaymentInfos();
        $braintreeId = $paymentInfo['transaction_id'];

        $this->_addBraintreeInfo($payment, $braintreeId, $riskData['id']);

        if ($riskData['decision'] == self::ADVANCED_FRAUD_REVIEW
            || $riskData['decision'] == self::ADVANCED_FRAUD_DECLINE
        ) {
            $this->isPaid = false;
            $payment->setIsTransactionPending(true);

            if ($riskData['decision'] == self::ADVANCED_FRAUD_DECLINE) {
                $payment->setIsFraudDetected(true);
            }
        }
        $payment->save();
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string                         $braintreeId
     * @param string                         $riskDataId
     */
    protected function _addBraintreeInfo($payment, $braintreeId, $riskDataId)
    {
        if (Mage::helper('gene_braintree')->canUpdateKount()) {
            $payment->setAdditionalInformation('kount_id', $riskDataId);
        }

        /* @var $wrapper Gene_Braintree_Model_Wrapper_Braintree */
        $wrapper = Mage::getModel('gene_braintree/wrapper_braintree')->init();
        try {
            $transaction = $wrapper->findTransaction($braintreeId);
        } catch (Exception $e) {
            ShopgateLogger::getInstance()->log(
                'Braintree transaction not found. OrderNumber: '
                . $this->getShopgateOrder()->getOrderNumber()
                . 'API message: '
                . $e->getMessage()
            );

            return;
        }

        $braintreeInfoHeadings = array(
            'avsErrorResponseCode',
            'avsPostalCodeResponseCode',
            'avsStreetAddressResponseCode',
            'cvvResponseCode',
            'gatewayRejectionReason',
            'processorAuthorizationCode',
            'processorResponseCode',
            'processorResponseText',
            'threeDSecure',
        );

        foreach ($braintreeInfoHeadings as $key) {
            if ($infoData = $transaction->{$key}) {
                $payment->setAdditionalInformation($key, $infoData);
            }
        }
    }

    /**
     * Creates an invoice if it's paid
     *
     * @param array $paymentInfo
     */
    protected function _addInvoice($paymentInfo)
    {
        if (!$this->getShopgateOrder()->getIsPaid()) {

            return;
        }

        $invoice = $this->_getPaymentHelper()->createOrderInvoice($this->getOrder());
        if ($this->isPaid) {
            $invoice->setIsPaid(true);
            $invoice->pay();
        }
        $invoice->setTransactionId($paymentInfo['transaction_id']);
        $invoice->save();
        $this->getOrder()->addRelatedObject($invoice);
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $paymentInfo = $this->getShopgateOrder()->getPaymentInfos();
        $riskData    = $paymentInfo['risk_data'];

        if ($riskData['decision'] == self::ADVANCED_FRAUD_REVIEW) {
            $state   = $this->_getHelper()->getStateForStatus('payment_review');
            $status  = $this->_getHelper()->getStatusFromState($state);
            $comment = '[SHOPGATE] Setting order status to "Review" because of pending fraud detection';
            $magentoOrder->setData('shopgate_status_set', true);

            return $magentoOrder->setState($state, $status, $comment);
        }

        return parent::setOrderStatus($magentoOrder);
    }
}
