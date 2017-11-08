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
 * @group              Shopgate_Payment_Cc
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Cc
 */
class Shopgate_Framework_Test_Model_Payment_Cc extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_cc';

    /**
     * @var Shopgate_Framework_Model_Payment_Cc $router
     */
    protected $router;

    /**
     * Check that regular Authorize will run when the other authorize
     * plugins are disabled
     *
     * @uses   ShopgateOrder::setPaymentMethod
     * @uses   Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses   Shopgate_Framework_Model_Payment_Cc::getShopgateOrder
     * @uses   Shopgate_Framework_Model_Payment_Cc::getPaymentMethod
     *
     * @covers ::getModelByPaymentMethod
     */
    public function testGetAuthnCcPaymentMethod()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Authn::MODULE_CONFIG);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Cc_Authncim::MODULE_CONFIG);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Cc_Chargeitpro::MODULE_CONFIG);

        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::AUTHN_CC);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals(ShopgateCartBase::AUTHN_CC, $this->router->getPaymentMethod());
    }

    /**
     * Checks that we route to Autorize CIM when it's enabled and API returns true.
     * API calls is mocked and forced to return true.
     *
     * @uses   ShopgateOrder::setPaymentMethod
     * @uses   Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::enableModule
     * @uses   Shopgate_Framework_Model_Payment_Cc::getShopgateOrder
     * @uses   Shopgate_Framework_Model_Payment_Cc::getPaymentMethod
     *
     * @covers ::getModelByPaymentMethod
     */
    public function testGetAuthCimPaymentMethod()
    {
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::AUTHN_CC);
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Authn::MODULE_CONFIG);
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Authncim::MODULE_CONFIG);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Cc_Chargeitpro::MODULE_CONFIG);
        $this->enableModule(Shopgate_Framework_Model_Payment_Cc_Authncim::XML_CONFIG_ENABLED);

        $mock = $this->getModelMock(
            'shopgate/payment_cc_authncim',
            array('checkGenericValid'),
            false,
            array(array($this->router->getShopgateOrder()))
        );
        $mock->expects($this->once())
             ->method('checkGenericValid')
             ->will($this->returnValue(true));

        $this->replaceByMock('model', 'shopgate/payment_cc_authncim', $mock);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals('AUTHNCIM_CC', $this->router->getPaymentMethod());
    }

    /**
     * When ChargeItPro is disabled, we default to USAePay
     *
     * @uses   ShopgateOrder::setPaymentMethod
     * @uses   Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::enableModule
     * @uses   Shopgate_Framework_Model_Payment_Cc::getShopgateOrder
     * @uses   Shopgate_Framework_Model_Payment_Cc::getPaymentMethod
     *
     * @covers ::getModelByPaymentMethod
     */
    public function testGetUsaEpayPaymentMethod()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Usaepay::MODULE_CONFIG);
        $this->enableModule(Shopgate_Framework_Model_Payment_Cc_Usaepay::XML_CONFIG_ENABLED);
        $this->deactivateModule(Shopgate_Framework_Model_Payment_Cc_Chargeitpro::MODULE_CONFIG);
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::USAEPAY_CC);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals(ShopgateCartBase::USAEPAY_CC, $this->router->getPaymentMethod());
    }

    /**
     * Even if Mage_USAePay is enabled, ignore it and redirect to ChargeItPro
     *
     * @uses   ShopgateOrder::setPaymentMethod
     * @uses   Shopgate_Framework_Test_Model_Utility::activateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::deactivateModule
     * @uses   Shopgate_Framework_Test_Model_Utility::enableModule
     * @uses   Shopgate_Framework_Model_Payment_Cc::getShopgateOrder
     * @uses   Shopgate_Framework_Model_Payment_Cc::getPaymentMethod
     *
     * @covers ::getModelByPaymentMethod
     */
    public function testGetChargeItProPaymentMethod()
    {
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Usaepay::MODULE_CONFIG);
        $this->enableModule(Shopgate_Framework_Model_Payment_Cc_Usaepay::XML_CONFIG_ENABLED);
        $this->activateModule(Shopgate_Framework_Model_Payment_Cc_Chargeitpro::MODULE_CONFIG);
        $this->enableModule(Shopgate_Framework_Model_Payment_Cc_Chargeitpro::XML_CONFIG_ENABLED);
        $this->router->getShopgateOrder()->setPaymentMethod(ShopgateCartBase::USAEPAY_CC);
        $this->router->getModelByPaymentMethod();

        $this->assertEquals('CHARGEITPRO_CC', $this->router->getPaymentMethod());
    }
}