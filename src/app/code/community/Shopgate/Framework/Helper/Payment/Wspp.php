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
class Shopgate_Framework_Helper_Payment_Wspp extends Shopgate_Framework_Helper_Payment_Abstract
{
    // Next two fields are required for Brazil
    const BUYER_TAX_ID = 'buyer_tax_id';
    const BUYER_TAX_ID_TYPE = 'buyer_tax_id_type';
    const PAYMENT_STATUS = 'payment_status';
    const PENDING_REASON = 'pending_reason';
    const IS_FRAUD = 'is_fraud_detected';
    const PAYMENT_STATUS_GLOBAL = 'paypal_payment_status';
    const PENDING_REASON_GLOBAL = 'paypal_pending_reason';
    const IS_FRAUD_GLOBAL = 'paypal_is_fraud_detected';

    /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMap = array(
        Mage_Paypal_Model_Info::PAYER_ID       => 'paypal_payer_id',
        Mage_Paypal_Model_Info::PAYER_EMAIL    => 'paypal_payer_email',
        Mage_Paypal_Model_Info::PAYER_STATUS   => 'paypal_payer_status',
        Mage_Paypal_Model_Info::ADDRESS_ID     => 'paypal_address_id',
        Mage_Paypal_Model_Info::ADDRESS_STATUS => 'paypal_address_status',
        Mage_Paypal_Model_Info::PROTECTION_EL  => 'paypal_protection_eligibility',
        Mage_Paypal_Model_Info::FRAUD_FILTERS  => 'paypal_fraud_filters',
        Mage_Paypal_Model_Info::CORRELATION_ID => 'paypal_correlation_id',
        Mage_Paypal_Model_Info::AVS_CODE       => 'paypal_avs_code',
        Mage_Paypal_Model_Info::CVV2_MATCH     => 'paypal_cvv2_match',
        Mage_Paypal_Model_Info::CENTINEL_VPAS  => Mage_Paypal_Model_Info::CENTINEL_VPAS,
        Mage_Paypal_Model_Info::CENTINEL_ECI   => Mage_Paypal_Model_Info::CENTINEL_ECI,
        self::BUYER_TAX_ID                     => self::BUYER_TAX_ID,
        self::BUYER_TAX_ID_TYPE                => self::BUYER_TAX_ID_TYPE,
    );

    /**
     * System information map
     *
     * @var array
     */
    protected $_systemMap = array(
        self::PAYMENT_STATUS => self::PAYMENT_STATUS_GLOBAL,
        self::PENDING_REASON => self::PENDING_REASON_GLOBAL,
        self::IS_FRAUD       => self::IS_FRAUD_GLOBAL,
    );

    /**
     * PayPal payment status possible values not in 1.4 so copied here
     *
     * @var string
     */
    const PAYMENTSTATUS_NONE = 'none';
    const PAYMENTSTATUS_COMPLETED = 'completed';
    const PAYMENTSTATUS_DENIED = 'denied';
    const PAYMENTSTATUS_EXPIRED = 'expired';
    const PAYMENTSTATUS_FAILED = 'failed';
    const PAYMENTSTATUS_INPROGRESS = 'in_progress';
    const PAYMENTSTATUS_PENDING = 'pending';
    const PAYMENTSTATUS_REFUNDED = 'refunded';
    const PAYMENTSTATUS_REFUNDEDPART = 'partially_refunded';
    const PAYMENTSTATUS_REVERSED = 'reversed';
    const PAYMENTSTATUS_UNREVERSED = 'canceled_reversal';
    const PAYMENTSTATUS_PROCESSED = 'processed';
    const PAYMENTSTATUS_VOIDED = 'voided';

    /**
     * Order states
     */
    const STATE_NEW = 'new';
    const STATE_PENDING_PAYMENT = 'pending_payment';
    const STATE_PROCESSING = 'processing';
    const STATE_COMPLETE = 'complete';
    const STATE_CLOSED = 'closed';
    const STATE_CANCELED = 'canceled';
    const STATE_HOLDED = 'holded';
    const STATE_PAYMENT_REVIEW = 'payment_review';

    /**
     * @var null
     */
    protected $_request = null;

    /**
     * History message action map
     *
     * @var array
     */
    protected $_messageStatusAction = array(
        self::PAYMENTSTATUS_COMPLETED => 'Captur',
        self::PAYMENTSTATUS_PENDING   => 'Authoriz'
    );

    /**
     * IPN request data getter
     *
     * @param string $key
     *
     * @return array|string
     */
    public function getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_request;
        }

        return isset($this->_request[$key]) ? $this->_request[$key] : null;
    }

    /**
     * Filter payment status from NVP into paypal/info format
     *
     * @param string $ipnPaymentStatus
     *
     * @return string
     */
    public function filterPaymentStatus($ipnPaymentStatus)
    {
        switch ($ipnPaymentStatus) {
            case 'Created':
            case 'Completed':
                $status = self::PAYMENTSTATUS_COMPLETED;
                break;
            case 'Denied':
                $status = self::PAYMENTSTATUS_DENIED;
                break;
            case 'Expired':
                $status = self::PAYMENTSTATUS_EXPIRED;
                break;
            case 'Failed':
                $status = self::PAYMENTSTATUS_FAILED;
                break;
            case 'Pending':
                $status = self::PAYMENTSTATUS_PENDING;
                break;
            case 'Refunded':
                $status = self::PAYMENTSTATUS_REFUNDED;
                break;
            case 'Reversed':
                $status = self::PAYMENTSTATUS_REVERSED;
                break;
            case 'Canceled_Reversal':
                $status = self::PAYMENTSTATUS_UNREVERSED;
                break;
            case 'Processed':
                $status = self::PAYMENTSTATUS_PROCESSED;
                break;
            case 'Voided':
                $status = self::PAYMENTSTATUS_VOIDED;
                break;
            default:
                $status = '';
        }

        return $status;
    }

    /**
     * Map payment information from IPN to payment object
     * Returns true if there were changes in information
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array                          $paypalIpnData
     *
     * @return bool
     */
    public function importPaymentInformation($payment, $paypalIpnData)
    {
        $this->_request      = $paypalIpnData;
        $was                 = $payment->getAdditionalInformation();
        $info                = Mage::getSingleton('paypal/info');
        $paymentInfoInstance = $payment->getMethodInstance()->getInfoInstance();
        $from                = array();
        foreach ($this->_getPaymentInfoKeys() as $privateKey => $publicKey) {
            if (is_int($privateKey)) {
                $privateKey = $publicKey;
            }
            $value = $this->getRequestData($privateKey);
            if ($value) {
                $from[$publicKey] = $value;
            }
        }
        if (isset($from['payment_status'])) {
            $from['payment_status'] = $this->filterPaymentStatus($this->getRequestData('payment_status'));
        }

        // collect fraud filters
        $fraudFilters = array();
        for ($i = 1; $value = $this->getRequestData("fraud_management_pending_filters_{$i}"); $i++) {
            $fraudFilters[] = $value;
        }
        if ($fraudFilters) {
            $from[Mage_Paypal_Model_Info::FRAUD_FILTERS] = $fraudFilters;
        }

        $info->importToPayment($from, $paymentInfoInstance);

        /**
         * Detect pending payment, frauds
         * TODO: implement logic in one place
         *
         * @see Mage_Paypal_Model_Pro::importPaymentInfo()
         */
        if ($this->_isPaymentReviewRequired($paymentInfoInstance)) {
            $paymentInfoInstance->setIsTransactionPending(true);
            if ($fraudFilters) {
                $paymentInfoInstance->setIsFraudDetected(true);
            }
        }
        if ($this->_isPaymentSuccessful($paymentInfoInstance)) {
            $paymentInfoInstance->setIsTransactionApproved(true);
        } elseif ($this->_isPaymentFailed($paymentInfoInstance)) {
            $paymentInfoInstance->setIsTransactionDenied(true);
        }

        return $was != $paymentInfoInstance->getAdditionalInformation();
    }

    /**
     * Generate an "IPN" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param Mage_Sales_Model_Order $order
     * @param string                 $comment
     * @param bool                   $addToHistory
     *
     * @return string|Mage_Sales_Model_Order_Status_History
     */
    public function createIpnComment($order, $comment = '', $addToHistory = false)
    {
        $paymentStatus = $this->getRequestData('payment_status');
        $message       = Mage::helper('paypal')->__('IPN "%s".', $paymentStatus);
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            $message = $order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }

        return $message;
    }

    /**
     * Grab data from source and map it into payment
     *
     * @param array|Varien_Object|callback $from
     * @param Mage_Payment_Model_Info      $payment
     */
    public function importToPayment($from, Mage_Payment_Model_Info $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = array($from, 'getDataUsingMethod');
        }
        Varien_Object_Mapper::accumulateByMap($from, array($payment, 'setAdditionalInformation'), $fullMap);
    }

    /**
     * @return array
     */
    protected function _getPaymentInfoKeys()
    {
        return array(
            Mage_Paypal_Model_Info::PAYER_ID,
            'payer_email' => Mage_Paypal_Model_Info::PAYER_EMAIL,
            Mage_Paypal_Model_Info::PAYER_STATUS,
            Mage_Paypal_Model_Info::ADDRESS_STATUS,
            Mage_Paypal_Model_Info::PROTECTION_EL,
            self::PAYMENT_STATUS,
            self::PENDING_REASON,
            self::SHOPGATE_CC_STRING,
            self::SHOPGATE_CC_TYPE_STRING,
            self::SHOPGATE_CC_HOLDER_STRING
        );
    }

    /**
     * Depends on Shopgate paymentInfos() to be passed
     * into the TransactionAdditionalInfo of $order.
     *
     * @param $paymentStatus String
     * @param $order         Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function orderStatusManager(Mage_Sales_Model_Order $order, $paymentStatus = null)
    {
        if (!$paymentStatus) {
            $rawData       = $order->getPayment()->getTransactionAdditionalInfo(
                self::RAW_DETAILS
            );
            $paymentStatus = strtolower($rawData['payment_status']);
        }

        $formattedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
        $state          = $status = Mage_Sales_Model_Order::STATE_PROCESSING;
        $action         = $this->getActionByStatus($paymentStatus);

        if ($order->getPayment()->getIsTransactionPending()) {
            $message = Mage::helper('paypal')->__(
                '%sing amount of %s is pending approval on gateway.',
                $action,
                $formattedPrice
            );
            $state   = $status = Mage::helper('shopgate')->getStateForStatus(self::STATE_PAYMENT_REVIEW);
        } else {
            $message = Mage::helper('paypal')->__(
                '%sed amount of %s online.',
                $action,
                $formattedPrice
            );
        }
        //test for fraud
        if ($order->getPayment()->getIsFraudDetected()) {
            $state  = Mage::helper('shopgate')->getStateForStatus(self::STATE_PAYMENT_REVIEW);
            $status = Mage::helper('shopgate')->getStatusFromState($state);
        }

        return $order->setState($state, $status, $message);
    }

    /**
     * Maps correct message action based on order status.
     * E.g. authorize if pending, capture on complete
     *
     * @param $paymentStatus
     *
     * @return string
     */
    public function getActionByStatus($paymentStatus)
    {
        return isset($this->_messageStatusAction[$paymentStatus]) ?
            $this->_messageStatusAction[$paymentStatus] : 'Authoriz';
    }

    /**
     * Get proper action status for order.
     * This is when the order was paid.
     *
     * @return string
     */
    public function getPaypalCompletedStatus()
    {
        return defined(
            'Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED'
        ) ? Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED : self::PAYMENTSTATUS_COMPLETED;
    }

    /**
     * Get proper action status for order.
     * This is when the order was refunded
     * on PayPal's side.
     *
     * @return string
     */
    public function getPaypalRefundedStatus()
    {
        return defined(
            'Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED'
        ) ? Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED : self::PAYMENTSTATUS_REFUNDED;
    }

    /**
     * Get proper status action for order.
     * Payment was obtained, but money were
     * not captured yet
     *
     * @return string
     */
    public function getPaypalPendingStatus()
    {
        return defined(
            'Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING'
        ) ? Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING : self::PAYMENTSTATUS_PENDING;
    }

    /**
     * Check whether the payment is in review state
     * Support for version 1.4.0.0 added
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    protected function _isPaymentReviewRequired(Mage_Payment_Model_Info $payment)
    {
        if (!$this->_getConfigHelper()->getIsMagentoVersionLower1410()) {
            return Mage::getSingleton('paypal/info')->isPaymentReviewRequired($payment);
        }

        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (self::PAYMENTSTATUS_PENDING === $paymentStatus) {
            $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);

            return !in_array($pendingReason, array('authorization', 'order'));
        }

        return false;
    }

    /**
     * Check whether the payment was processed successfully
     * Support for version 1.4.0.0 added
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    protected function _isPaymentSuccessful(Mage_Payment_Model_Info $payment)
    {
        if (!$this->_getConfigHelper()->getIsMagentoVersionLower1410()) {
            return Mage::getSingleton('paypal/info')->isPaymentSuccessful($payment);
        }

        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (in_array(
            $paymentStatus,
            array(
                self::PAYMENTSTATUS_COMPLETED,
                self::PAYMENTSTATUS_INPROGRESS,
                self::PAYMENTSTATUS_REFUNDED,
                self::PAYMENTSTATUS_REFUNDEDPART,
                self::PAYMENTSTATUS_UNREVERSED,
                self::PAYMENTSTATUS_PROCESSED,
            )
        )) {
            return true;
        }
        $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);

        return self::PAYMENTSTATUS_PENDING === $paymentStatus
               && in_array($pendingReason, array('authorization', 'order'));
    }

    /**
     * Check whether the payment was processed unsuccessfully or failed
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    protected function _isPaymentFailed(Mage_Payment_Model_Info $payment)
    {
        if (!$this->_getConfigHelper()->getIsMagentoVersionLower1410()) {
            return Mage::getSingleton('paypal/info')->isPaymentFailed($payment);
        }

        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);

        return in_array(
            $paymentStatus,
            array(
                self::PAYMENTSTATUS_DENIED,
                self::PAYMENTSTATUS_EXPIRED,
                self::PAYMENTSTATUS_FAILED,
                self::PAYMENTSTATUS_REVERSED,
                self::PAYMENTSTATUS_VOIDED,
            )
        );
    }

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('shopgate/config');
    }
}
