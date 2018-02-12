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
 * Native implementation of Authorize.net
 *
 * @package Shopgate_Framework_Model_Payment_Cc_Authn
 * @author  Konstantin Kiritenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc_Authn
    extends Shopgate_Framework_Model_Payment_Cc_AuthnAbstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const XML_CONFIG_ENABLED = 'payment/authorizenet/active';
    const MODULE_CONFIG = 'Mage_Paygate';

    /**
     * Init variables
     */
    private function _initVariables()
    {
        $paymentInfos           = $this->getShopgateOrder()->getPaymentInfos();
        $this->_transactionType = $paymentInfos['transaction_type'];
        $this->_responseCode    = $paymentInfos['response_code'];
    }

    /**
     * Use AuthnCIM as guide to refactor this class
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $this->_initVariables();
        $shopgateOrder = $this->getShopgateOrder();
        $paymentInfos  = $shopgateOrder->getPaymentInfos();

        $this->_saveToCardStorage();
        $this->getOrder()->getPayment()->setCcTransId($paymentInfos['transaction_id']);
        $this->getOrder()->getPayment()->setCcApproval($paymentInfos['authorization_code']);
        $this->getOrder()->getPayment()->setLastTransId($paymentInfos['transaction_id']);

        switch ($this->_transactionType) {
            case self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE:
                $newTransactionType      = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment capturing error.');
                break;
            case self::SHOPGATE_PAYMENT_STATUS_AUTH_ONLY:
            default:
                $newTransactionType      = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment authorization error.');
                break;
        }

        try {
            switch ($this->_responseCode) {
                case self::RESPONSE_CODE_APPROVED:
                    $this->getOrder()->getPayment()->setAmountAuthorized($this->getOrder()->getGrandTotal());
                    $this->getOrder()->getPayment()->setBaseAmountAuthorized($this->getOrder()->getBaseGrandTotal());
                    $this->getOrder()->getPayment()->setIsTransactionPending(true);
                    Mage::dispatchEvent('sales_order_place_before', array('order' => $this->getOrder()));
                    $this->_createTransaction($newTransactionType);
                    Mage::dispatchEvent('sales_order_place_after', array('order' => $this->getOrder()));

                    if ($this->_transactionType == self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE) {
                        $this->getOrder()->getPayment()->setIsTransactionPending(false);
                    }
                    break;
                case self::RESPONSE_CODE_HELD:
                    if ($this->_isOrderPendingReview()) {
                        Mage::dispatchEvent('sales_order_place_before', array('order' => $this->getOrder()));
                        $this->_createTransaction($newTransactionType, array('is_transaction_fraud' => true));
                        Mage::dispatchEvent('sales_order_place_after', array('order' => $this->getOrder()));
                        $this->getOrder()->getPayment()->setIsTransactionPending(true)->setIsFraudDetected(true);
                    }
                    break;
                case self::RESPONSE_CODE_DECLINED:
                case self::RESPONSE_CODE_ERROR:
                    if ($this->getOrder()->canCancel()) {
                        $this->getOrder()->cancel();
                    }
                    Mage::throwException($paymentInfos['response_reason_text']);
                    break;
                default:
                    Mage::throwException($defaultExceptionMessage);
            }
        } catch (Exception $x) {
            $this->getOrder()->addStatusHistoryComment(Mage::helper('sales')->__('Note: %s', $x->getMessage()));
            Mage::logException($x);
        }

        $this->_createInvoice();

        return $this->getOrder();
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($magentoOrder)
    {
        $status = Mage::getModel('paygate/authorizenet')->getConfigData('order_status');
        if ($status && $this->_responseCode === self::RESPONSE_CODE_APPROVED) {
            $state = $this->_getHelper()->getStateForStatus($status);
            $magentoOrder->setData('shopgate_status_set', true);

            return $magentoOrder->setState($state, $status);
        } else {
            return parent::setOrderStatus($magentoOrder);
        }
    }

    /**
     * @param $type
     * @param $additionalInformation
     */
    protected function _createTransaction($type, $additionalInformation = array())
    {
        $orderPayment = $this->_order->getPayment();
        $transaction  = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderPaymentObject($orderPayment);
        $transaction->setTxnId($orderPayment->getCcTransId());
        $transaction->setIsClosed(false);
        $transaction->setTxnType($type);
        $transaction->setData('is_transaciton_closed', '0');
        $transaction->setAdditionalInformation('real_transaction_id', $orderPayment->getCcTransId());
        foreach ($additionalInformation as $key => $value) {
            $transaction->setAdditionalInformation($key, $value);
        }
        $transaction->save();
    }

    /**
     * Utilize card storage if it exists
     * It does not in mage 1.4.0.0
     *
     * @throws Exception
     */
    protected function _saveToCardStorage()
    {
        $paymentAuthorize = Mage::getModel('paygate/authorizenet');

        $this->getOrder()->getPayment()->setMethod($paymentAuthorize->getCode());
        $paymentAuthorize->setInfoInstance($this->getOrder()->getPayment());
        $this->getOrder()->getPayment()->setMethodInstance($paymentAuthorize);
        $this->getOrder()->save();

        if (!method_exists($paymentAuthorize, 'getCardsStorage')) {
            return $this;
        }

        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();
        $lastFour     = substr($paymentInfos['credit_card']['masked_number'], -4);
        $cardStorage  = $paymentAuthorize->getCardsStorage($this->getOrder()->getPayment());
        $card         = $cardStorage->registerCard();
        $card->setRequestedAmount($this->getShopgateOrder()->getAmountComplete())
             ->setBalanceOnCard("")
             ->setLastTransId($paymentInfos['transaction_id'])
             ->setProcessedAmount($this->getShopgateOrder()->getAmountComplete())
             ->setCcType($this->_getCcTypeName($paymentInfos['credit_card']['type']))
             ->setCcOwner($paymentInfos['credit_card']['holder'])
             ->setCcLast4($lastFour)
             ->setCcExpMonth($paymentInfos['credit_card']['expiry_month'])
             ->setCcExpYear($paymentInfos['credit_card']['expiry_year'])
             ->setCcSsIssue("")
             ->setCcSsStartMonth("")
             ->setCcSsStartYear("");

        switch ($this->_responseCode) {
            case self::RESPONSE_CODE_APPROVED:
                if ($this->_transactionType == self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE) {
                    $card->setCapturedAmount($card->getProcessedAmount());
                }
                $cardStorage->updateCard($card);
                break;
            case self::RESPONSE_CODE_HELD:
                if ($this->_isOrderPendingReview()) {
                    if ($this->_transactionType == self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE) {
                        $card->setCapturedAmount($card->getProcessedAmount());
                    }
                    $cardStorage->updateCard($card);
                }
                break;
        }
        return $this;
    }
}
