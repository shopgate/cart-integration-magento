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
 * Handler for SUE versions 1.1.8 - 2.1.1
 */
class Shopgate_Framework_Model_Payment_Simple_Sue_Sue118
    extends Shopgate_Framework_Model_Payment_Simple_Sue_Sue211
    implements Shopgate_Framework_Model_Payment_Interface
{

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     */
    protected function setTransactionId(Mage_Sales_Model_Order $magentoOrder)
    {
        $payment = $this->getShopgateOrder()->getPaymentInfos();
        $info    = $magentoOrder->getPayment();
        if ($info instanceof Mage_Payment_Model_Info && isset($payment['transaction_id'])) {
            $info->setData('pn_su_transaction_id', $payment['transaction_id']);
        }
    }
}
