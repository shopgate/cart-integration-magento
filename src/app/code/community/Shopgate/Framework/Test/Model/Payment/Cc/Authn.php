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
 * @group              Shopgate_Payment_Cc
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Cc_Authn
 */
class Shopgate_Framework_Test_Model_Payment_Cc_Authn extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_cc_authn';
    const MODULE_CONFIG = 'Mage_Paygate';
    const XML_CONFIG_ENABLED = 'payment/authorizenet/active';

    /** @var Shopgate_Framework_Model_Payment_Cc_Authn $class */
    protected $class;

    /**
     * Blank rewrite, we will test this in abstract class
     *
     * @coversNothing
     */
    public function testShopgateStatusSet() { }

    /**
     * @uses ReflectionClass::getProperty
     * @covers ::_initVariables()
     */
    public function testInitVariables()
    {
        $order = new ShopgateOrder();
        $order->setPaymentInfos(
            array(
                'transaction_type' => 'test_type',
                'response_code'    => 'test_response',
            )
        );
        $this->class->setShopgateOrder($order);
        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_initVariables');
        $method->setAccessible(true);
        $method->invoke($this->class, null);
        $type = $reflection->getProperty('_transactionType');
        $type->setAccessible(true);
        $response = $reflection->getProperty('_responseCode');
        $response->setAccessible(true);

        $this->assertEquals('test_type', $type->getValue($this->class));
        $this->assertEquals('test_response', $response->getValue($this->class));
    }

    /**
     * @uses Shopgate_Framework_Model_Payment_Cc_Authn::setOrder
     * @covers ::_createTransaction
     */
    public function testCreateTransactions()
    {
        /**
         * Setup
         */
        $transId  = '123';
        $transKey = 'payment';

        $mock = $this->getModelMock('sales/order_payment_transaction', array('save'));
        $mock->method('save')
             ->will($this->returnSelf());
        $this->replaceByMock('model', 'sales/order_payment_transaction', $mock);

        $order   = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setCcTransId($transId);
        $order->setPayment($payment);
        $this->class->setOrder($order);

        /**
         * Invoke
         *
         * @var Mage_Sales_Model_Order_Payment_Transaction $mock
         */
        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_createTransaction');
        $method->setAccessible(true);
        $method->invoke($this->class, $transKey, array('additional1' => 'Rnd'));
        $info = $mock->getAdditionalInformation();

        $this->assertEquals($transId, $mock->getTxnId());
        $this->assertEquals($transKey, $mock->getTxnType());
        $this->assertEquals(0, $mock->getIsClosed());
        $this->assertEquals($transId, $info['real_transaction_id']);
        $this->assertArrayHasKey('additional1', $info);
    }
}