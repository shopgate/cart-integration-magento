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
 * @group              Shopgate_Payment_Shopgate
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Shopgate
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Shopgate extends Shopgate_Framework_Test_Model_Payment_Abstract
{
    const MODULE_CONFIG = 'Shopgate_Framework';
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_shopgate';

    /**
     * @var Shopgate_Framework_Model_Payment_Simple_Shopgate $class
     */
    protected $class;

    /**
     * Empty rewrite, Shopgate payment active
     * always returns true
     *
     * @group empty
     * @coversNothing
     */
    public function testIsDisabled() { }

    /**
     * State set in the database should match
     * the state that comes into the magento
     * order class
     *
     * @param string $state - mage order state, e.g. 'processing'
     *
     * @uses         ShopgateOrder::setIsShippingBlocked
     * @uses         Shopgate_Framework_Model_Payment_Simple_Shopgate::getShopgateOrder
     * @covers       ::setOrderStatus
     *
     * @dataProvider allStateProvider
     */
    public function testSetOrderStatus($state)
    {
        Mage::app()->getStore(0)->setConfig('payment/shopgate/order_status', $state);
        /** @var Mage_Sales_Model_Order $mageOrder */
        $mageOrder = Mage::getModel('sales/order');
        $this->class->getShopgateOrder()->setIsShippingBlocked(true);
        $this->class->setOrderStatus($mageOrder);

        $this->assertEquals(
            $state,
            $mageOrder->getState(),
            "State should be set to '{$state}, but it was '{$mageOrder->getState()}'"
        );
    }

    /**
     * Check that all order statuses lead to
     * "processing" when shipping is not blocked
     *
     * @param string $state - mage order state, e.g. 'processing'
     *
     * @uses         ShopgateOrder::setIsShippingBlocked
     * @uses         Shopgate_Framework_Model_Payment_Simple_Shopgate::getShopgateOrder
     * @covers       ::setOrderStatus
     *
     * @dataProvider allStateProvider
     */
    public function testGetOrderStatusWhenNotBlocked($state)
    {
        $mageOrder = Mage::getModel('sales/order');
        $mageOrder->setState($state);
        $this->class->getShopgateOrder()->setIsShippingBlocked(false);
        $this->class->setOrderStatus($mageOrder);

        $this->assertEquals(
            Mage_Sales_Model_Order::STATE_PROCESSING,
            $mageOrder->getState(),
            'Status should always be "processing" when not blocked'
        );
    }
}