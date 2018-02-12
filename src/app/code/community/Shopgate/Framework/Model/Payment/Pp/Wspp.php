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
 * @package Shopgate_Framework_Model_Payment_Wspp
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Pp_Wspp
    extends Shopgate_Framework_Model_Payment_Pp_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::PP_WSPP_CC;
    const MODULE_CONFIG = 'Mage_Paypal';
    const PAYMENT_MODEL = 'paypal/direct';
    const XML_CONFIG_ENABLED = 'payment/paypal_wps_express/active';

    /**
     * Possible configs to check against
     *
     * @var array
     */
    private $allKnownConfigs = array(
        'website payments pro'         => 'payment/paypal_direct/active',
        'website payments pro payflow' => 'payment/paypaluk_direct/active',
        'payflow pro hack'             => 'payment/paypaluk_express/active'
    );

    /**
     * Create new order for amazon payment
     *
     * @param $quote Mage_Sales_Model_Quote
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

        return $order;
    }

    /**
     * @param $order            Mage_Sales_Model_Order
     *                          // TODO Refund
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfos  = $this->getShopgateOrder()->getPaymentInfos();
        $paypalIpnData = $this->translateIpnData($paymentInfos['paypal_ipn_data']);
        $paypalIpnData = array_merge($paymentInfos['credit_card'], $paypalIpnData);
        $paymentStatus = $this->_getPaymentHelper()->filterPaymentStatus($paypalIpnData['payment_status']);

        $trans = Mage::getModel('sales/order_payment_transaction');
        $trans->setOrderPaymentObject($order->getPayment());
        $trans->setTxnId($paypalIpnData['txn_id']);
        $trans->setIsClosed(false);

        try {
            $invoice = $this->_getPaymentHelper()->createOrderInvoice($order);
            switch ($paymentStatus) {
                // paid
                case $this->_getPaymentHelper()->getPaypalCompletedStatus():
                    $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
                    if ($order->getPayment()->getIsTransactionPending()) {
                        $invoice->setIsPaid(false);
                    } else { // normal online capture: invoice is marked as "paid"
                        $order->getPayment()->setBaseAmountPaidOnline($invoice->getBaseGrandTotal());
                        $invoice->setIsPaid(true);
                        $invoice->pay();
                    }
                    break;
                case $this->_getPaymentHelper()->getPaypalRefundedStatus():
                    //$this->_getPaymentHelper()->registerPaymentRefund($additionalData, $order);
                    break;
                case $this->_getPaymentHelper()->getPaypalPendingStatus():
                    foreach ($paypalIpnData as $key => $value) {
                        if (strpos($key, 'fraud_management_pending_filters_') !== false) {
                            $order->getPayment()->setIsTransactionPending(true);
                            $order->getPayment()->setIsFraudDetected(true);
                        }
                    }

                    $trans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
                    $invoice->setIsPaid(false);
                    break;
                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
            $trans->save();
            $invoice->setTransactionId($paypalIpnData['txn_id']);
            $invoice->save();
            $order->addRelatedObject($invoice);
            $this->_getPaymentHelper()->importPaymentInformation($order->getPayment(), $paypalIpnData);
            $order->getPayment()->setTransactionAdditionalInfo(
                $this->_getPaymentHelper()->getTransactionRawDetails(),
                $paypalIpnData
            );
            $order->getPayment()->setCcOwner($paypalIpnData['holder']);
            $order->getPayment()->setCcType($paypalIpnData['type']);
            $order->getPayment()->setCcNumberEnc($paypalIpnData['masked_number']);
            $order->getPayment()->setLastTransId($paypalIpnData['txn_id']);
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
     * @param $quote            Mage_Sales_Model_Quote
     * @param $data             array
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $data)
    {
        $ipnData = json_decode($data['paypal_ipn_data'], true);
        $this->_getPaymentHelper()->importToPayment(
            $ipnData,
            $quote->getPayment()->getMethodInstance()->getInfoInstance()
        );
        $quote->getPayment()->setTransactionId($data['paypal_txn_id']);
        $quote->getPayment()->setCcOwner($data['credit_card']['holder']);
        $quote->getPayment()->setCcType($data['credit_card']['type']);
        $quote->getPayment()->setCcNumberEnc($data['credit_card']['masked_number']);
        $quote->setData('paypal_ipn_data', $data['paypal_ipn_data']);
        $quote->getPayment()->setLastTransId($data['paypal_txn_id']);

        return $quote;
    }

    /**
     * Set order status
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        return $this->orderStatusManager($magentoOrder);
    }

    /**
     * Rewrite to tailor for lower versions
     * of magento's PP implementation
     *
     * @return bool
     */
    public function isEnabled()
    {
        return parent::isEnabled() || $this->_checkOtherEnableConfigs();
    }

    /**
     * Checks if Website Payments Pro OR
     * Website Payment Pro Payflow OR
     * Payflow Pro are enabled
     *
     * @return bool
     */
    private function _checkOtherEnableConfigs()
    {
        $result = false;
        foreach ($this->allKnownConfigs as $config) {
            $result = Mage::getStoreConfigFlag($config);
            if ($result) {
                return $result;
            }
        }

        $debug = $this->_getHelper()->__('Neither WSPP, WSPP Payflow or Payflow Pro are enabled');
        ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);

        return $result;
    }


}
