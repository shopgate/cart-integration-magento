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
 * This class supports magento < 1.6.0 import for Amazon Payments
 */
class Shopgate_Framework_Model_Payment_Simple_Mws_Mws15
    extends Shopgate_Framework_Model_Payment_Simple_Mws_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    /**
     * Supported payment type
     *
     * @return string
     */
    protected function _getTransactionType()
    {
        return Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT;
    }

    /**
     * Rewrite to be empty, mage 1400 does not have this function
     * Mage 1501 throws an exception as AmazonPayment plugin does not
     * support it
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     *
     * @return $this
     */
    protected function _importTransactionInfo($transaction)
    {
        return $this;
    }
}