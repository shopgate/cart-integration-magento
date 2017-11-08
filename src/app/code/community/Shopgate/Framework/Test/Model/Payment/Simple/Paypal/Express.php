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
 * @group              Shopgate_Payment_Paypal
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Paypal_Express
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Paypal_Express extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const MODULE_CONFIG = 'Mage_Paypal';
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_paypal_express';
    const XML_CONFIG_ENABLED = 'payment/paypal_express/active';

    /**
     * @var Shopgate_Framework_Model_Payment_Simple_Paypal_Express $class
     */
    protected $class;

    /**
     * Checks only the status of that function,
     * not the parent. The response should always be true
     * when order is_paid flag is true
     *
     * @param string $state - magento sale order state
     *
     * @uses         ShopgateOrder::setIsPaid
     * @uses         Shopgate_Framework_Model_Payment_Simple_Paypal_Express::getShopgateOrder
     *
     * @covers       ::setOrderStatus
     * @dataProvider allStateProvider
     */
    public function testSetOrderStatus($state)
    {
        $this->class->getShopgateOrder()->setIsPaid(true);
        $mageOrder = Mage::getModel('sales/order');
        $payment   = Mage::getModel('sales/order_payment');
        $payment->setTransactionAdditionalInfo('raw_details_info', array('payment_status' => 'completed'));
        $mageOrder->setPayment($payment);
        $mageOrder->setState($state);
        $this->class->setOrderStatus($mageOrder);

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING, $mageOrder->getState());
    }

    /**
     * Rewrites the default method to include
     * the payment data checks
     *
     * @uses   Shopgate_Framework_Test_Model_Payment_Simple_Paypal_Express::setPaidStatusFixture
     *
     * @covers ::setOrderStatus
     */
    public function testShopgateStatusSet()
    {
        $this->setPaidStatusFixture('processing');
        $order   = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setTransactionAdditionalInfo('raw_details_info', array('payment_status' => 'completed'));
        $order->setPayment($payment);
        $this->class->setOrderStatus($order);
        $this->assertTrue($order->getShopgateStatusSet());
    }

    /**
     * Makes sure we are loading express class
     * if Standard is not enabled
     *
     * @uses   ShopgateOrder::setPaymentMethod
     *
     * @covers Shopgate_Framework_Model_Payment_Factory::calculatePaymentClass
     */
    public function testModelLoad()
    {
        Mage::app()->getStore(0)->setConfig('payment/paypal_standard/active', 0);
        $order = new ShopgateOrder();
        $order->setPaymentMethod(ShopgateCartBase::PAYPAL);
        /** @var Shopgate_Framework_Model_Payment_Factory $factory */
        $factory = Mage::getModel('shopgate/payment_factory', array($order));
        $model   = $factory->calculatePaymentClass();

        $this->assertInstanceOf('Shopgate_Framework_Model_Payment_Simple_Paypal_Express', $model);
    }

    /**
     * Checks if helper returns WSPP's helper.
     * We use a reflector class to access a protected method.
     *
     * @covers ::_getPaymentHelper
     */
    public function testGetHelper()
    {
        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_getPaymentHelper');
        $method->setAccessible(true);
        $helper = $method->invoke($this->class, null);

        $this->assertInstanceOf('Shopgate_Framework_Helper_Payment_Wspp', $helper);
    }
}