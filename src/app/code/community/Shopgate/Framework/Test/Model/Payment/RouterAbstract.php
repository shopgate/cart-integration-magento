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
 * @author Konstantin Kiritsenko <konstantin.kiritsenko@shopgate.com>
 */
class Shopgate_Framework_Test_Model_Payment_RouterAbstract extends Shopgate_Framework_Test_Model_Utility
{
    /**
     * @var Shopgate_Framework_Model_Payment_Router $router
     */
    protected $router;

    /**
     * Exclude group 'empty' to avoid
     * inflating the test number
     *
     * @coversNothing
     * @group empty
     */
    public function testEmpty() { }

    /**
     * Default setup for routers
     * Avoids throwing exception when the setup
     * is ran from this class.
     *
     * @throws Shopgate_Framework_Test_Model_Payment_Router_Exception
     */
    public function setUp()
    {
        $order     = new ShopgateOrder();
        $shortName = $this->getConstant('CLASS_SHORT_NAME');
        if (!$shortName && !$this->currentClass()) {
            throw new Shopgate_Framework_Test_Model_Payment_Router_Exception(
                "'A router's short name variable needs to be provided"
            );
        }
        $this->router = Mage::getModel($shortName, array($order));
    }

    /**
     * Checks if we are calling
     * this class directly
     *
     * @return bool
     */
    private function currentClass()
    {
        return $this instanceof self;
    }

    /**
     * Garbage collection prep
     */
    public function tearDown()
    {
        unset($this->router);
    }
}