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
 * Class Shopgate_Framework_Model_Payment_Inv_Payol
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Inv_Payol
    extends Shopgate_Framework_Model_Payment_Abstract_AbstractPayol
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateCartBase::PAYOL_INV;
    const XML_CONFIG_ENABLED = 'payment/payolution_invoice/active';
    const MODULE_CONFIG = 'Payolution_Invoice';
    const PAYMENT_MODEL = 'payolution_invoice/xmlPayment';
    const XML_CONFIG_STATUS_PAID = 'payment/payolution_invoice/order_status';

    /**
     * @inheritdoc
     */
    public function manipulateOrderWithPaymentData($magentoOrder)
    {
        parent::manipulateOrderWithPaymentData($magentoOrder);
        $infos = $this->getShopgateOrder()->getPaymentInfos();

        if (!empty($infos['reference_id'])) {
            $transaction = $this->getOrder()->getPayment()->getAuthorizationTransaction();
            $transaction->setAdditionalInformation('payolutionInvoiceId', $infos['reference_id']);
            $transaction->save();
        }

        return $this->getOrder();
    }
}
