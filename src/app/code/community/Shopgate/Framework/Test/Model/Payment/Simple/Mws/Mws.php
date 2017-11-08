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
 * @group              Shopgate_Payment_Mws
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Mws_Mws
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Mws_Mws extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const MODULE_CONFIG = 'Creativestyle_AmazonPayments';
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_mws_mws';
    const XML_CONFIG_ENABLED = 'amazonpayments/general/active';

    /** @var Shopgate_Framework_Model_Payment_Simple_Mws_Mws $class */
    protected $class;

    /**
     * Tests getting the correct transaction type
     * in Magento 1.6 >=
     *
     * @covers ::_getTransactionType
     */
    public function testGetTransactionType()
    {
        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_getTransactionType');
        $method->setAccessible(true);
        $result = $method->invoke($this->class, null);

        $this->assertEquals($result, 'order');
    }

    /**
     * Tests the following:
     *  When state not set - set to 'processing'
     *  When state is set, do nothing with the state
     *
     * @covers ::setOrderStatus
     */
    public function testSetOrderStatus()
    {
        $order = Mage::getModel('sales/order');
        $this->class->setOrderStatus($order);

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING, $order->getState());

        $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
        $this->class->setOrderStatus($order);

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, $order->getState());
    }

}