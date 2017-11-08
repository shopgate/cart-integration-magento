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
 * @group              Shopgate_Payment_Sue
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Sue_Sue300
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Sue_Sue300 extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const MODULE_CONFIG = 'Paymentnetwork_Pnsofortueberweisung';
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_sue_sue300';
    const XML_CONFIG_ENABLED = 'payment/paymentnetwork_pnsofortueberweisung/active';

    /** @var Shopgate_Framework_Model_Payment_Simple_Sue_Sue300 $class */
    protected $class;

    /**
     * Custom rewrite for when status is paid
     *
     * @param string $state - magento order state
     *
     * @uses         ShopgateOrder::setIsPaid
     * @uses         Shopgate_Framework_Model_Payment_Simple_Sue_Sue300::getShopgateOrder
     * @uses         Shopgate_Framework_Test_Model_Payment_Abstract::setPaidStatusFixture
     * @covers       ::setOrderStatus
     *
     * @dataProvider allStateProvider
     */
    public function testSetOrderStatus($state)
    {
        $this->class->getShopgateOrder()->setIsPaid(1);

        $this->setPaidStatusFixture($state);
        $order = Mage::getModel('sales/order');
        $this->class->setOrderStatus($order);

        //todo-sg: mock it up
        if (!$order->getShopgateStatusSet()) {
            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        }

        $this->assertEquals($state, $order->getState());
    }

    /**
     * Custom rewrite for when status is not paid
     *
     * @param string $state - magento order state
     *
     * @uses         ShopgateOrder::setIsPaid
     * @uses         Shopgate_Framework_Model_Payment_Simple_Sue_Sue300::getShopgateOrder
     * @uses         Shopgate_Framework_Test_Model_Payment_Abstract::setNotPaidStatusFixture
     * @covers       ::setOrderStatus
     *
     * @dataProvider allStateProvider
     */
    public function testNotPaidStatus($state)
    {
        $this->class->getShopgateOrder()->setIsPaid(0);

        $this->setNotPaidStatusFixture($state);
        $order = Mage::getModel('sales/order');
        $this->class->setOrderStatus($order);

        //todo-sg: mock it up
        if (!$order->getShopgateStatusSet()) {
            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        }

        $this->assertEquals($state, $order->getState());
    }
}