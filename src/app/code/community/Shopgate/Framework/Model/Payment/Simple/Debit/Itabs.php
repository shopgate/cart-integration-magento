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
class Shopgate_Framework_Model_Payment_Simple_Debit_Itabs
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::DEBIT;
    const XML_CONFIG_ENABLED = 'payment/debit/active';
    const XML_CONFIG_STATUS_PAID = 'payment/debit/order_status';
    const MODULE_CONFIG = 'Itabs_Debit';
    const PAYMENT_MODEL = 'debit/debit';

    /**
     * @inheritdoc
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfo = $this->getShopgateOrder()->getPaymentInfos();
        /** @var Itabs_Debit_Model_Debit $payment */
        $payment = $order->getPayment()->getMethodInstance();
        $payment->assignData(
            array(
                'debit_iban'     => $paymentInfo['iban'],
                'debit_swift'    => $paymentInfo['bic'],
                'debit_bankname' => $paymentInfo['bank_name'],
                'debit_cc_owner' => $paymentInfo['bank_account_holder']
            )
        );

        return $order;
    }
}
