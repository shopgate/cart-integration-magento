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
 * Native model for USA ePay
 *
 * @package Shopgate_Framework_Model_Payment_Usaepay
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc_Usaepay extends Shopgate_Framework_Model_Payment_Cc_Abstract
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::USAEPAY_CC;
    const XML_CONFIG_ENABLED = 'payment/usaepay/active';
    const XML_CONFIG_STATUS_PAID = 'payment/usaepay/order_status';
    const MODULE_CONFIG = 'Mage_Usaepay';

    /**
     * @param $order Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfos    = $this->getShopgateOrder()->getPaymentInfos();
        $paymentInstance = $this->_getLocalPaymentModel();
        $order->getPayment()->setMethod($paymentInstance->getCode());
        $paymentInstance->setInfoInstance($order->getPayment());
        $order->getPayment()->setMethodInstance($paymentInstance);
        $order->save();

        $lastFour = substr($paymentInfos['credit_card']['masked_number'], -4);
        $order->getPayment()->setCcNumberEnc($paymentInfos['credit_card']['masked_number']);
        $order->getPayment()->setCCLast4($lastFour);
        $order->getPayment()->setCcTransId($paymentInfos['reference_number']);
        $order->getPayment()->setCcApproval($paymentInfos['authorization_number']);
        $order->getPayment()->setCcType($this->_getCcTypeName($paymentInfos['credit_card']['type']));
        $order->getPayment()->setCcOwner($paymentInfos['credit_card']['holder']);
        $order->getPayment()->setCcExpMonth(
            str_pad($paymentInfos['credit_card']['expiry_month'], 2, '0', STR_PAD_LEFT)
        );
        $order->getPayment()->setCcExpYear($paymentInfos['credit_card']['expiry_year']);

        try {
            if (isset($paymentInfos['transaction_type']) && $paymentInfos['transaction_type'] === 'sale') {
                $invoice = $this->_getPaymentHelper()->createOrderInvoice($order);
                $order->getPayment()->setAmountAuthorized($invoice->getGrandTotal());
                $order->getPayment()->setBaseAmountAuthorized($invoice->getBaseGrandTotal());
                $order->getPayment()->setBaseAmountPaidOnline($invoice->getBaseGrandTotal());
                $order->getPayment()->setLastTransId($paymentInfos['reference_number']);
                $invoice->setIsPaid(true);
                $invoice->setTransactionId($paymentInfos['reference_number']);
                $invoice->pay();
                $invoice->save();
                $order->addRelatedObject($invoice);
            } else {
                $order->getPayment()->setAmountAuthorized($order->getGrandTotal());
                $order->getPayment()->setBaseAmountAuthorized($order->getBaseGrandTotal());
                $order->getPayment()->setIsTransactionPending(true);
            }
        } catch (Exception $e) {
            $order->addStatusHistoryComment(Mage::helper('sales')->__('Note: %s', $e->getMessage()));
            Mage::logException($e);
        }
        return $order;
    }

    /**
     * Get the payment model to use withing class and direct children only
     *
     * @return Mage_Usaepay_Model_CCPaymentAction
     */
    protected function _getLocalPaymentModel()
    {
        return Mage::getModel('usaepay/CCPaymentAction');
    }
}