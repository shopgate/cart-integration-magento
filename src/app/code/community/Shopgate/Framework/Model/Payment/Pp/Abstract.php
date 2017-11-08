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
 * Class Shopgate_Framework_Model_Payment_Pp_Abstract
 *
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Pp_Abstract
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = 'PP';
    const MODULE_CONFIG = 'Mage_Paypal';
    const TYPE_ORDER = 'order';
    const TYPE_QUOTE = 'quote';
    const TYPE_TRANS_ID = 'transaction_id';

    /**
     * Depends on Shopgate paymentInfos() to be passed
     * into the TransactionAdditionalInfo of $order.
     *
     * @param null | string          $paymentStatus
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function orderStatusManager(Mage_Sales_Model_Order $order, $paymentStatus = null)
    {
        $this->_getPaymentHelper()->orderStatusManager($order, $paymentStatus);
        $order->setShopgateStatusSet(true);

        return $order;
    }

    /**
     * @inheritdoc
     */
    public function prepareQuote($quote, $data)
    {
        $this->_getPaymentHelper()->importToPayment(
            $data,
            $quote->getPayment()->getMethodInstance()->getInfoInstance()
        );
        $quote->getPayment()->setTransactionId($data[self::TYPE_TRANS_ID]);
        $quote->getPayment()->setLastTransId($data[self::TYPE_TRANS_ID]);
        $quote->getPayment()->setPaypalPayerId($data['payer_id']);
        $quote->getPayment()->setPaypalPayerStatus($data['payer_status']);
        $quote->getPayment()->setAdditionalInformation('paypal_address_status', $data['address_status']);
        $quote->getPayment()->setAdditionalInformation('paypal_express_checkout_payer_id', $data[self::TYPE_TRANS_ID]);
        $quote->getPayment()->setAdditionalInformation('paypal_pending_reason', $data['pending_reason']);

        return $quote;
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Wspp
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_wspp');
    }

    /**
     * Helps retrieve IPN data from the payment info
     *
     * @param string|array $ipnData - IPN data that can come as an array or JSON
     *
     * @return array
     */
    protected function translateIpnData($ipnData)
    {
        if (empty($ipnData)) {
            $ipnData = array();
        } elseif (is_string($ipnData) && strpos($ipnData, '{') !== false) {
            $ipnData = Zend_Json::decode($ipnData);
        } elseif (is_string($ipnData)) {
            $ipnData = array($ipnData);
        }

        return $ipnData;
    }
}
