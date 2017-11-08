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
 * @group              Shopgate_Payment_Paypal
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Paypal
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Paypal extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_paypal';

    /** @var Shopgate_Framework_Model_Payment_Simple_Paypal $router */
    protected $router;

    /**
     * Adding a payment method
     */
    public function setUp()
    {
        parent::setUp();
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::PAYPAL);
    }

    /**
     * Test Simple that router directs to Standard Pp when it's
     * enabled in the database & module is enabled as well
     *
     * @uses   Shopgate_Framework_Model_Payment_Simple_Paypal::getPaymentMethod
     * @covers ::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetPpStandardMethod()
    {
        $configMock = $this->getHelperMock('shopgate/config', array('getIsMagentoVersionLower1410'));
        $configMock->expects($this->exactly(2))
                   ->method('getIsMagentoVersionLower1410')
                   ->willReturnOnConsecutiveCalls(
                       false,
                       true
                   );
        $this->replaceByMock('helper', 'shopgate/config', $configMock);
        $this->activateModule('Mage_Paypal');

        $this->router->setPaymentMethod('');
        $this->router->getModelByPaymentMethod();
        $this->assertContains('STANDARD', $this->router->getPaymentMethod());

        $this->router->setPaymentMethod('');
        $this->router->getModelByPaymentMethod();
        $this->assertContains('STANDARD1400', $this->router->getPaymentMethod());
    }

    /**
     * Simpler router check of express. Defaults to it if
     * Standard PP is not enabled.
     *
     * @uses   Shopgate_Framework_Model_Payment_Simple_Paypal::getPaymentMethod
     * @covers ::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetPpExpressMethod()
    {
        $this->router->getModelByPaymentMethod();
        $this->assertContains('EXPRESS', $this->router->getPaymentMethod());
    }
}