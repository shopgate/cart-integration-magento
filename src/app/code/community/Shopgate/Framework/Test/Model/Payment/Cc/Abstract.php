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
 * @group              Shopgat_Payment
 * @group              Shopgat_Payment_Cc
 *
 * @coversDefaultClass Shopgate_Framework_Model_Payment_Cc_Abstract
 */
class Shopgate_Framework_Test_Model_Payment_Cc_Abstract extends EcomDev_PHPUnit_Test_Case
{
    /** @var Shopgate_Framework_Test_Model_Payment_Cc_Abstract $class */
    protected $class;

    public function setUp()
    {
        $this->class = Mage::getModel('shopgate/payment_cc_abstract', array(new ShopgateOrder()));
    }

    /**
     * Get type by name
     *
     * @param $value
     * @param $expected
     *
     * @uses         ReflectionClass::getMethod
     * @covers ::_getCcTypeName
     *
     * @dataProvider dataProvider
     */
    public function testGetCcTypeName($value, $expected)
    {
        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_getCcTypeName');
        $method->setAccessible(true);
        $result = $method->invoke($this->class, $value);
        $this->assertEquals($expected, $result);
    }
}