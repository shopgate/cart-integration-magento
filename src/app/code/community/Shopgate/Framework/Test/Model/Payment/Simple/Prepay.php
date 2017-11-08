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
 * @group              Shopgate_Payment_Prepay
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Prepay
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Prepay extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_prepay';

    /** @var Shopgate_Framework_Model_Payment_Simple_Prepay $router */
    protected $router;

    /**
     * Adding a payment method
     */
    public function setUp()
    {
        parent::setUp();
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::PREPAY);
    }

    /**
     * Test that router redirects to Native when it's
     * enabled in the database & mage v1.7+, else defaults
     * to CheckMoneyOrder if magento is lower than 1.7
     *
     * @uses    Mage::getVersion
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Prepay::getPaymentMethod
     * @covers  ::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetPrepayNativeMethod()
    {
        $configMock = $this->getHelperMock('shopgate/config', array('getIsMagentoVersionLower1700'));
        $configMock->expects($this->exactly(2))
                   ->method('getIsMagentoVersionLower1700')
                   ->willReturnOnConsecutiveCalls(
                       false,
                       true
                   );
        $this->replaceByMock('helper', 'shopgate/config', $configMock);

        $this->deactivateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Phoenix::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Native::MODULE_CONFIG);

        $this->router->getModelByPaymentMethod();
        $this->assertEquals('Native', $this->router->getPaymentMethod());

        $this->router->getModelByPaymentMethod();
        $this->assertEquals('Checkmo', $this->router->getPaymentMethod());
    }

    /**
     * Checks if phoenix module redirect works. No need for other Bank
     * modules to be disabled as it takes over.
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Prepay::getPaymentMethod
     * @covers  ::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetPrepayPhoenixMethod()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Phoenix::MODULE_CONFIG);
        $this->router->getModelByPaymentMethod();

        $this->assertContains('Phoenix', $this->router->getPaymentMethod());
    }

    /**
     * Test if all are enabled and Phoenix comes on top
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Prepay::getPaymentMethod
     * @covers  ::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetAllPrepayEnabled()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Phoenix::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Native::MODULE_CONFIG);

        $this->router->getModelByPaymentMethod();

        $this->assertContains('Phoenix', $this->router->getPaymentMethod());
    }

    /**
     * Test if all are disabled, routes to Prepay by default
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Prepay::getPaymentMethod
     * @covers  ::getModelByPaymentMethod
     */
    public function testGetNoPrepayMethod()
    {
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Phoenix::MODULE_CONFIG);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Simple_Prepay_Native::MODULE_CONFIG);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals(ShopgateCartBase::PREPAY, $this->router->getPaymentMethod());
    }
}