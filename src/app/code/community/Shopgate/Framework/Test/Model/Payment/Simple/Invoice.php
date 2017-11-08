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
 * @author             Konstantin Kiritsenko <konstantin.kiritsenko@shopgate.com>
 * @group              Shopgate_Payment
 * @group              Shopgate_Payment_Invoice
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Invoice
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Invoice extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const MODULE_CONFIG = 'Mage_Payment';
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_invoice';
    const XML_CONFIG_ENABLED = 'payment/purchaseorder/active';

    /**
     * @covers       ::setOrderStatus
     *
     * @dataProvider allStateProvider
     */
    public function testSetOrderStatus($state)
    {
        parent::testSetOrderStatus($state);
    }
}