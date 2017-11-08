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
 * Handler for all online bank transfer payment interfaces
 *
 * Note: any online bank transfer config needs to be enabled for
 * Giro, Ideal, SUE to work, they don't actually need to be selected.
 *
 * @package Class Shopgate_Framework_Model_Payment_Payone_BankAbstract
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Payone_BankAbstract extends Shopgate_Framework_Model_Payment_Payone_Abstract
{
    const PAYONE_CORE_MODEL_CONFIG_IDENTIFIER = 'payone_online_bank_transfer';
    const PAYMENT_MODEL = 'payone_core/payment_method_onlineBankTransfer';

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $this->getOrder()->getPayment()->setPayoneOnlinebanktransferType($this->_getConfigCode());

        return parent::manipulateOrderWithPaymentData($order);
    }

}