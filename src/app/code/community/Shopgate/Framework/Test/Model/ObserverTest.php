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
 * @coversDefaultClass Shopgate_Framework_Model_Observer
 */
class Shopgate_Framework_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Returns true that we are making a Shopgate API request
     */
    public function setUp()
    {
        $helper = $this->getHelperMock('shopgate', array('isShopgateApiRequest'));
        $helper->expects($this->any())->method('isShopgateApiRequest')->willReturn(true);
        $this->replaceByMock('helper', 'shopgate', $helper);
    }

    /**
     * @param bool $expected      - whether Sales Rules are blocked from applying to cart/order
     * @param bool $config        - config value of "Apply Sales Rules"
     * @param bool $registryValue - registry value to disable sales rules from applying, true - disable
     *
     * @covers ::beforeSalesrulesLoaded
     * @dataProvider salesRuleDataProvider
     */
    public function testBeforeSalesrulesLoaded($expected, $config, $registryValue)
    {
        $observer       = new Varien_Event_Observer();
        $event          = new Varien_Event();
        $collectionMock = $this->getResourceModelMock('salesrule/rule_collection', array('getReadConnection'));
        $event->setData('collection', $collectionMock);
        $observer->setEvent($event);
        $registryValue ? Mage::register('shopgate_disable_sales_rules', $registryValue) : false;

        Mage::app()->getStore(0)->setConfig('shopgate/orders/apply_cart_rules', (int)$config);
        Mage::getModel('shopgate/observer')->beforeSalesrulesLoaded($observer);

        /** @var Mage_SalesRule_Model_Resource_Rule_Collection $collectionMock */
        $where  = $collectionMock->getSelect()->getPart(Zend_Db_Select::WHERE);
        $actual = isset($where[0]) && strpos($where[0], 'coupon_type') !== false;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function salesRuleDataProvider()
    {
        return array(
            array(
                'Sales Rules are blocked'          => true,
                'Apply Sales Rules in cfg'         => true,
                'Sales Rules Disabled in registry' => true,
            ),
            array(
                'Sales Rules are blocked'          => false,
                'Apply Sales Rules in cfg'         => true,
                'Sales Rules Disabled in registry' => false,
            ),
            array(
                'Sales Rules are blocked'          => true,
                'Apply Sales Rules in cfg'         => false,
                'Sales Rules Disabled in registry' => true,
            ),
            array(
                'Sales Rules are blocked'          => true,
                'Apply Sales Rules in cfg'         => false,
                'Sales Rules Disabled in registry' => false,
            ),
        );
    }

    /**
     * @after
     */
    public function tearDown()
    {
        Mage::unregister('shopgate_disable_sales_rules');
    }
}
