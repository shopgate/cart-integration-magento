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
 * @group              Shopgate_Payment_Cod
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Simple_Cod
 */
class Shopgate_Framework_Test_Model_Payment_Simple_Cod extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_simple_cod';

    /**
     * @var Shopgate_Framework_Model_Payment_Simple_Cod $router
     */
    protected $router;

    /**
     * Adding a payment method
     */
    public function setUp()
    {
        parent::setUp();
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::COD);
    }

    /**
     * Native method can only be ran if magento is v1.7.0.0+
     * and all the other modules are disabled
     *
     * @uses    Mage::getVersion
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::setPaymentMethod
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::getPaymentMethod
     * @covers  Shopgate_Framework_Model_Payment_Simple_Cod::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetCodNativeMethod()
    {
        $configMock = $this->getHelperMock('shopgate/config', array('getIsMagentoVersionLower1700'));
        $configMock->expects($this->exactly(2))
                   ->method('getIsMagentoVersionLower1700')
                   ->willReturnOnConsecutiveCalls(
                       false,
                       true
                   );
        $this->replaceByMock('helper', 'shopgate/config', $configMock);

        $this->deactivateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Phoenix108::MODULE_CONFIG);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Msp::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Native::MODULE_CONFIG);

        $this->router->getModelByPaymentMethod();
        $this->assertEquals('Native', $this->router->getPaymentMethod());

        $this->router->setPaymentMethod('');
        $this->router->getModelByPaymentMethod();
        $this->assertEquals(ShopgateCartBase::COD, $this->router->getPaymentMethod());
    }

    /**
     * Checks if phoenix module redirect works.
     *
     * Intentionally left COD Native to be enabled
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::getPaymentMethod
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::setPaymentMethod
     * @covers  Shopgate_Framework_Model_Payment_Simple_Cod::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetCodPhoenixMethod()
    {
        $routerMock = $this->getModelMock(
            self::CLASS_SHORT_NAME,
            array('_getVersion'),
            false,
            array(array(new ShopgateOrder()))
        );
        $routerMock->expects($this->exactly(2))
                   ->method('_getVersion')
                   ->willReturnOnConsecutiveCalls(
                       '1.0.7',
                       '1.0.8'
                   );

        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Native::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Phoenix108::MODULE_CONFIG);

        /** @var Shopgate_Framework_Model_Payment_Simple_Cod $routerMock */
        $routerMock->setPaymentMethod('');
        $routerMock->getModelByPaymentMethod();
        $this->assertContains('Phoenix107', $routerMock->getPaymentMethod());

        $routerMock->setPaymentMethod('');
        $routerMock->getModelByPaymentMethod();
        $this->assertContains('Phoenix108', $routerMock->getPaymentMethod());
    }

    /**
     * MSP takes priority over others even when all others are enabled
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::getPaymentMethod
     * @covers  Shopgate_Framework_Model_Payment_Simple_Cod::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetCodMspMethod()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Msp::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Native::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Simple_Cod_Phoenix108::MODULE_CONFIG);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals('Msp', $this->router->getPaymentMethod());
    }

    /**
     * Test if all are disabled, routes to COD.
     *
     * @uses    Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses    Shopgate_Framework_Model_Payment_Simple_Cod::getPaymentMethod
     * @covers  Shopgate_Framework_Model_Payment_Simple_Cod::getModelByPaymentMethod
     *
     * @loadFixture
     */
    public function testGetNoCodMethod()
    {
        /** @var Shopgate_Framework_Model_Payment_Simple_Cod | EcomDev_PHPUnit_Mock_Proxy $mock */
        $mock = $this->getModelMock(
            'shopgate/payment_simple_cod',
            array('isModuleActive'),
            false,
            array(array($this->router->getShopgateOrder()))
        );
        $mock->expects($this->once())
             ->method('isModuleActive')
             ->will($this->returnValue(false));

        $mock->getModelByPaymentMethod();

        $this->assertEquals(ShopgateCartBase::COD, $mock->getPaymentMethod());
    }
}