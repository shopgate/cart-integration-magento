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
 * @coversDefaultClass Shopgate_Framework_Helper_Payment_Wspp
 */
class Shopgate_Framework_Test_Helper_Payment_Wspp extends EcomDev_PHPUnit_Test_Case
{
    /** @var Shopgate_Framework_Helper_Payment_Wspp $helper */
    protected $helper;

    /**
     * Sets up the express class on unit load
     */
    public function setUp()
    {
        $this->helper = Mage::helper('shopgate/payment_wspp');
    }

    /**
     * Tests just the necessary status settings. Other parts of
     * the method will be tested in other functions.
     *
     * @param bool   $pending  - whether order is pending
     * @param bool   $fraud    -  whether order is fraud
     * @param string $expected - expected state of order
     *
     * @covers       ::orderStatusManager
     * @dataProvider statusProvider
     */
    public function testOrderStatusManager($pending, $fraud, $expected)
    {
        $mageOrder = Mage::getModel('sales/order');
        $payment   = Mage::getModel('sales/order_payment');

        $payment->setIsTransactionPending($pending);
        $payment->setIsFraudDetected($fraud);
        $mageOrder->setPayment($payment);
        $this->helper->orderStatusManager($mageOrder);

        $this->assertEquals($expected, $mageOrder->getState());
    }

    /**
     * @see testOrderStatusManager for details
     * @return array
     */
    public function statusProvider()
    {
        return array(
            array(true, false, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW),
            array(false, false, Mage_Sales_Model_Order::STATE_PROCESSING),
            array(true, true, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW),
            array(false, true, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW),
        );
    }

    /**
     * Retrieves the verbiage to use in order history.
     *
     * @param string $status   - PayPal status
     * @param string $expected - expected output
     *
     * @covers       ::getActionByStatus
     * @dataProvider dataProvider
     */
    public function testGetActionByStatus($status, $expected)
    {
        $action = $this->helper->getActionByStatus($status);

        $this->assertEquals($expected, $action);
    }

    /**
     * @param $expectedStatus
     *
     * @dataProvider dataProvider
     */
    public function testGetPaypalStatus($expectedStatus)
    {
        $function  = 'getPaypal' . ucfirst($expectedStatus) . 'Status';
        $newStatus = $this->helper->$function();

        $this->assertEquals($expectedStatus, $newStatus);
    }

    /**
     * Garbage collection prep
     */
    public function tearDown()
    {
        unset($this->helper);
    }
}