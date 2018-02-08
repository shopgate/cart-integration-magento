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
 * This class is for the paypal mapping with Payone Plugin Version ~3.3
 *
 * Class Shopgate_Framework_Model_Payment_Payone_Pp3
 */
class Shopgate_Framework_Model_Payment_Payone_Pp3
    extends Shopgate_Framework_Model_Payment_Payone_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYONE_CORE_MODEL_CONFIG_IDENTIFIER = 'payone_wallet';
    const PAYMENT_MODEL = 'payone_core/payment_method_wallet';
    const PAYMENT_IDENTIFIER = ShopgateOrder::PAYONE_PP;

    /**
     * @return string
     */
    protected function _getConfigCode()
    {
        return Payone_Api_Enum_WalletType::PAYPAL_EXPRESS;
    }

    /**
     * Creates an invoice for the order
     *
     * @throws Exception
     */
    protected function _addInvoice()
    {
        if ($this->getShopgateOrder()->getIsPaid()) {
            parent::_addInvoice();
        } else {
            $info    = $this->getShopgateOrder()->getPaymentInfos();
            $invoice = $this->_getPaymentHelper()->createOrderInvoice($this->getOrder());
            $invoice->setIsPaid(false);
            $invoice->setTransactionId($info['txn_id']);
            $invoice->save();
            $this->getOrder()->addRelatedObject($invoice);
        }
    }
}