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
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Router
 */
class Shopgate_Framework_Test_Model_Payment_Router extends Shopgate_Framework_Test_Model_Payment_RouterAbstract
{
    const CLASS_SHORT_NAME = 'shopgate/payment_router';

    /** @var Shopgate_Framework_Model_Payment_Router $router */
    protected $router;

    /**
     * Check that constructor cannot be
     * initialized without a Shopgate object
     *
     * @expectedException Exception
     * @expectedExceptionMessage Incorrect type provided to: Shopgate_Framework_Model_Payment_Abstract::_construct()
     */
    public function testConstructor()
    {
        Mage::getModel('shopgate/payment_abstract');
    }

    /**
     * Test getting parts of a payment method
     *
     * @param int    $partNumber    - part number to grab from paymentMethod, .e.g "PP"
     * @param string $paymentMethod - arrives from Shopgate API, e.g. PP_WSPP_CC
     * @param string $expected      - expected payment method part returned
     *
     * @uses         ShopgateOrder::setPaymentMethod
     * @uses         Shopgate_Framework_Model_Payment_Router::getShopgateOrder
     * @uses         Shopgate_Framework_Model_Payment_Router::getClassFromMethod
     * @covers       ::_getMethodPart
     *
     * @dataProvider dataProvider
     */
    public function testMethodPart($partNumber, $paymentMethod, $expected)
    {
        $this->router->getShopgateOrder()->setPaymentMethod($paymentMethod);
        $reflection = new ReflectionClass($this->router);
        $property   = $reflection->getProperty('_payment_method_part');
        $property->setAccessible(true);
        $property->setValue($this->router, $partNumber);
        $method = $reflection->getMethod('_getMethodPart');
        $method->setAccessible(true);
        $part = $method->invoke($this->router, null);

        $this->assertEquals($expected, $part);
    }

    /**
     * Should return the correct truncated class short name
     *
     * @covers ::getCurrentClassShortName
     */
    public function testGetClassShortName()
    {
        $reflection = new ReflectionClass($this->router);
        $method     = $reflection->getMethod('getCurrentClassShortName');
        $method->setAccessible(true);
        $className = $method->invoke($this->router, null);

        $this->assertEquals('shopgate/payment', $className);
    }

    /**
     * Tests backup class name path generator
     *
     * @param string $paymentMethod - e.g AUTHN_CC
     * @param array  $expected      - array of expected combinations. Has to match exactly.
     *
     * @uses         Shopgate_Framework_Model_Payment_Router::setPaymentMethod
     * @covers       ::_getModelCombinations
     *
     * @dataProvider dataProvider
     */
    public function testGetModelCombinations($paymentMethod, $expected)
    {
        $this->router->setPaymentMethod($paymentMethod);
        $reflection = new ReflectionClass($this->router);
        $method     = $reflection->getMethod('_getModelCombinations');
        $method->setAccessible(true);
        $combinations = $method->invoke($this->router, null);
        $this->assertArraySubset($expected, $combinations);
    }
}
