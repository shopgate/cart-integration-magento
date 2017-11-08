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
class Shopgate_Framework_Model_Payment_Braintr_Cc extends Shopgate_Framework_Model_Payment_Braintr_Abstract
{
    const PAYMENT_IDENTIFIER = 'BRAINTR_CC'; // todo replace with ShopgateOrder::BRAINTR_CC
    const MODULE_CONFIG = 'Gene_Braintree';
    const PAYMENT_MODEL = 'gene_braintree/paymentmethod_creditcard';
    const XML_CONFIG_ENABLED = 'payment/gene_braintree_creditcard/active';
    const XML_CONFIG_STATUS_PAID = 'payment/gene_braintree_creditcard/order_status';

    const STATUS_APPROVED = 'APPROVED';

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $shopgateOrder = $this->getShopgateOrder();
        $paymentInfo   = $shopgateOrder->getPaymentInfos();
        $payment       = $this->getOrder()->getPayment();
        $payment->setStatus(self::STATUS_APPROVED);
        $payment->setIsTransactionClosed(0);
        $payment->setAmount($shopgateOrder->getAmountComplete());
        $payment->setShouldCloseParentTransaction(false);
        $payment->setCcType($paymentInfo['credit_card']['type']);
        $payment->setCcOwner($paymentInfo['credit_card']['holder']);
        $payment->setCcNumberEnc($paymentInfo['credit_card']['masked_number']);
        $payment->setCcLast4(substr($paymentInfo['credit_card']['masked_number'], -4));
        $payment->setCcExpMonth($paymentInfo['credit_card']['expiry_month']);
        $payment->setCcExpYear($paymentInfo['credit_card']['expiry_year']);
        $payment->setLastTransId($paymentInfo['transaction_id']);

        $this->_handleFraud($payment, $paymentInfo['risk_data']);
        $this->_addInvoice($paymentInfo);

        return $this->setOrder($order);
    }
}