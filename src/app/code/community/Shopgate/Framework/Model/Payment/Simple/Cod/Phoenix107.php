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
 * Support for Phoenix_CashOnDelivery younger than v1.0.8 (not inclusive)
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Cod_Phoenix107
    extends Shopgate_Framework_Model_Payment_Simple_Cod_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const MODULE_CONFIG = 'Phoenix_CashOnDelivery';
    const PAYMENT_MODEL = 'cashondelivery/cashOnDelivery';
    const XML_CONFIG_ENABLED = 'payment/cashondelivery/active';
    const XML_CONFIG_FEE_LOCAL = 'payment/cashondelivery/inlandcosts';
    const XML_CONFIG_FEE_FOREIGN = 'payment/cashondelivery/foreigncountrycosts';

    /**
     * There is an issue in Phoenix_CashOnDelivery_Model_Quote_Total::collect()
     * in versions below 1.0.6 where it checks for methodInstance without
     * initializing it
     *
     * @inheritdoc
     */
    public function prepareQuote($quote, $info)
    {
        $quote->getPayment()->getMethodInstance();

        return $quote;
    }
}
