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
 * Class Shopgate_Framework_Model_Payment_Cc_AuthnAbstract
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc_AuthnAbstract extends Shopgate_Framework_Model_Payment_Cc_Abstract
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::AUTHN_CC;

    /**
     * const for transaction types of shopgate
     */
    const SHOPGATE_PAYMENT_STATUS_AUTH_ONLY = 'auth_only';
    const SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE = 'auth_capture';

    /**
     * const for response codes
     */
    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD = 4;

    const RESPONSE_REASON_CODE_APPROVED = 1;
    const RESPONSE_REASON_CODE_NOT_FOUND = 16;
    const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    protected $_transactionType = '';
    protected $_responseCode = '';

    /**
     * Checks if the order response is pending review
     *
     * @return bool
     */
    protected function _isOrderPendingReview()
    {
        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();

        return array_key_exists('response_reason_code', $paymentInfos)
               && (
                   $paymentInfos['response_reason_code'] == self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED
                   || $paymentInfos['response_reason_code'] == self::RESPONSE_REASON_CODE_PENDING_REVIEW
               );
    }

    /**
     * Sets order status
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus($order)
    {
        $this->setOrder($order);
        $captured = $this->getOrder()->getBaseCurrency()->formatTxt($this->getOrder()->getBaseTotalInvoiced());
        $state    = Mage_Sales_Model_Order::STATE_PROCESSING;
        $status   = $this->_getHelper()->getStatusFromState($state);
        $message  = '';

        switch ($this->_responseCode) {
            case self::RESPONSE_CODE_APPROVED:
                $duePrice = $this->getOrder()->getBaseCurrency()->formatTxt($this->getOrder()->getTotalDue());
                $message  = Mage::helper('paypal')->__('Authorized amount of %s.', $duePrice);

                if ($this->_transactionType == self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE) {
                    $message = Mage::helper('sales')->__('Captured amount of %s online.', $captured);
                }
                break;
            case self::RESPONSE_CODE_HELD:
                $state  = $this->_getHelper()->getStateForStatus('payment_review');
                $status = $this->_getHelper()->getStatusFromState($state);

                if ($this->_isOrderPendingReview()) {
                    $message = Mage::helper('sales')->__(
                        'Capturing amount of %s is pending approval on gateway.',
                        $captured
                    );
                } else {
                    $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();
                    if (isset($paymentInfos['response_reason_code'], $paymentInfos['response_reason_text'])) {
                        $message = $this->_getHelper()->__(
                            '[SHOPGATE] Response code: %s, response reason: %s',
                            $paymentInfos['response_reason_code'],
                            $paymentInfos['response_reason_text']
                        );
                        ShopgateLogger::getInstance()->log($message, ShopgateLogger::LOGTYPE_ERROR);
                    }
                }
                break;
            case self::RESPONSE_CODE_ERROR:
            case self::RESPONSE_CODE_DECLINED:
            default:
                $state   = $this->getOrder()->getState();
                $status  = $this->getOrder()->getStatus();
                $message = $this->_getHelper()->__('[SHOPGATE] Received response code: %s', $this->_responseCode);
                ShopgateLogger::getInstance()->log($message, ShopgateLogger::LOGTYPE_ERROR);
        }
        $this->getOrder()->setState($state, $status, $message);
        $this->getOrder()->setData('shopgate_status_set', true);

        return $this->getOrder();
    }

    /**
     *  Handles invoice creation
     *
     * @return $this
     * @throws Exception
     */
    protected function _createInvoice()
    {
        $paymentInfos = $this->getShopgateOrder()->getPaymentInfos();

        if ($this->_responseCode === self::RESPONSE_CODE_APPROVED
            && $this->_transactionType == self::SHOPGATE_PAYMENT_STATUS_AUTH_CAPTURE
        ) {
            $invoice = $this->_getPaymentHelper()->createOrderInvoice($this->_order);
            $invoice->setTransactionId($paymentInfos['transaction_id']);
            $this->_order->getPayment()->setBaseAmountPaidOnline($invoice->getBaseGrandTotal());
            $invoice->setIsPaid(true);
            $invoice->pay();
            $invoice->save();
            $this->_order->addRelatedObject($invoice);
        } elseif (in_array($this->_responseCode, array(self::RESPONSE_CODE_HELD, self::RESPONSE_CODE_ERROR))
                  && $this->_isOrderPendingReview()
        ) {
            $invoice = $this->_getPaymentHelper()->createOrderInvoice($this->_order);
            $invoice->setTransactionId($paymentInfos['transaction_id']);
            $invoice->setIsPaid(false);
            $invoice->save();
            $this->_order->addRelatedObject($invoice);
        }

        return $this;
    }
}
