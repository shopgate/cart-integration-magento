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
 * @group              Shopgate_Payment_Router
 * @group              Shopgate_Payment_Mws
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Mws
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Mws extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_mws';

    /** @var Shopgate_Framework_Model_Payment_Simple_Mws $router */
    protected $router;

    /**
     * Check for mage < v1.6 re-route
     *
     * @uses   ShopgateOrder::setPaymentMethod
     * @uses   Shopgate_Framework_Model_Payment_Simple_Mws::getShopgateOrder
     * @uses   Shopgate_Framework_Model_Payment_Simple_Mws::getPaymentMethod
     * @uses   Shopgate_Framework_Model_Payment_Simple_Mws::setPaymentMethod
     *
     * @covers ::getModelByPaymentMethod
     */
    public function testGetMwsBelowMage16Method()
    {
        $configMock = $this->getHelperMock('shopgate/config', array('getIsMagentoVersionLower16'));
        $configMock->expects($this->exactly(2))
                   ->method('getIsMagentoVersionLower16')
                   ->willReturnOnConsecutiveCalls(false, true);
        $this->replaceByMock('helper', 'shopgate/config', $configMock);

        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::AMAZON_PAYMENT);
        $this->router->getModelByPaymentMethod();
        $this->assertEquals('MWS', $this->router->getPaymentMethod());

        $this->router->setPaymentMethod('');
        $this->router->getModelByPaymentMethod();
        $this->assertEquals('MWS15', $this->router->getPaymentMethod());
    }
}