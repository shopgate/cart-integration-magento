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
 * @coversDefaultClass Shopgate_Framework_Helper_Billsafe_Client
 */
class Shopgate_Framework_Test_Helper_Billsafe_ClientTest extends Shopgate_Framework_Test_Model_Utility
{
    /**
     * @uses Shopgate_Framework_Model_Shopgate_Order::load
     * @covers ::getShopgateOrderNumber
     */
    public function testGetShopgateOrderNumber()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Billsafe::MODULE_CONFIG);

        /**
         * Setup for load call. Will return and empty self first, then the Varien object
         */
        $varien = new Varien_Object(array('shopgate_order_number' => '01234'));
        $order  = Mage::getModel('sales/order')->setIncrementId('5678');
        $mock   = $this->getModelMock('shopgate/shopgate_order');
        $mock->expects($this->any())
             ->method('load')
             ->willReturnOnConsecutiveCalls($this->returnSelf(), $varien, $varien);
        $this->replaceByMock('model', 'shopgate/shopgate_order', $mock);

        /**
         * Client class setup as the constructor is buggy
         */
        $client     = $this->getHelperMock('shopgate/billsafe_client', array(), false, array(), '', false);
        $reflection = new ReflectionClass($client);
        $method     = $reflection->getMethod('getShopgateOrderNumber');
        $method->setAccessible(true);

        $test = $method->invoke($client, $order);
        $this->assertEquals($order->getIncrementId(), $test);

        $test2 = $method->invoke($client, $order);
        $this->assertEquals('01234', $test2);

        /**
         * Test passing in Varien_Object to method signature, as long as there is no error, we are good
         */
        $this->assertNotEmpty($method->invoke($client, $varien));
    }
}