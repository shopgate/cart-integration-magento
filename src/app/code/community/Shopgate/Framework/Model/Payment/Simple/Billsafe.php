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
 * Class to manipulate the order payment data with BillSAFE payment data
 *
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Billsafe
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::BILLSAFE;
    const XML_CONFIG_ENABLED = 'payment/billsafe/active';
    const XML_CONFIG_STATUS_PAID = 'payment/billsafe/order_status';
    const MODULE_CONFIG = 'Netresearch_Billsafe';

    /**
     * @param $order Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentBillsafe = Mage::getModel('billsafe/payment');
        $order->getPayment()->setMethod($paymentBillsafe->getCode());
        $paymentBillsafe->setInfoInstance($order->getPayment());
        $order->getPayment()->setMethodInstance($paymentBillsafe);
        $order->save();
        $orderObject = new Varien_Object(array('increment_id' => $this->getShopgateOrder()->getOrderNumber()));
        $data        = Mage::getSingleton('billsafe/client')->getPaymentInstruction($orderObject);
        if ($data) {
            $order->getPayment()->setAdditionalInformation(
                'BillsafeStatus',
                Netresearch_Billsafe_Model_Payment::BILLSAFE_STATUS_ACTIVE
            );
            $order->getPayment()->setAdditionalInformation('Recipient', $data->recipient);
            $order->getPayment()->setAdditionalInformation('BankCode', $data->bankCode);
            $order->getPayment()->setAdditionalInformation('AccountNumber', $data->accountNumber);
            $order->getPayment()->setAdditionalInformation('BankName', $data->bankName);
            $order->getPayment()->setAdditionalInformation('Bic', $data->bic);
            $order->getPayment()->setAdditionalInformation('Iban', $data->iban);
            $order->getPayment()->setAdditionalInformation('Reference', $data->reference);
            $order->getPayment()->setAdditionalInformation('Amount', $data->amount);
            $order->getPayment()->setAdditionalInformation('CurrencyCode', $data->currencyCode);
            $order->getPayment()->setAdditionalInformation('Note', $data->note);
            $order->getPayment()->setAdditionalInformation('legalNote', $data->legalNote);
        } else {
            $order->getPayment()->setAdditionalInformation(
                'BillsafeStatus',
                Netresearch_Billsafe_Model_Payment::BILLSAFE_STATUS_CANCELLED
            );
        }
        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();
        $orderTrans   = Mage::getModel('sales/order_payment_transaction');
        $orderTrans->setOrderPaymentObject($order->getPayment());
        $orderTrans->setIsClosed(false);
        $orderTrans->setTxnId($paymentInfos['billsafe_transaction_id']);
        $orderTrans->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        $orderTrans->save();

        /**
         * Support for magento version 1.4.0.0
         */
        if (!$this->_getConfigHelper()->getIsMagentoVersionLower1410()) {
            $order->getPayment()->importTransactionInfo($orderTrans);
            $order->getPayment()->setDataChanges(true);
        }
        return $order;
    }
}