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
 * Handler for SUE versions 3.0.0+
 *
 * Class Shopgate_Framework_Model_Payment_Simple_Sue_Sue300
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Sue_Sue300
    extends Shopgate_Framework_Model_Payment_Simple_Sue_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const XML_CONFIG_ENABLED = 'payment/paymentnetwork_pnsofortueberweisung/active';
    const PAYMENT_MODEL = 'sofort/method_sofort';
    const XML_CONFIG_STATUS_PAID = 'payment/paymentnetwork_pnsofortueberweisung/order_status_received_credited';
    const XML_CONFIG_STATUS_NOT_PAID = 'payment/paymentnetwork_pnsofortueberweisung/order_status_pending_not_credited_yet';
}