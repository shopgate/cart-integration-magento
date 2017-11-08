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
 * @coversDefaultClass Shopgate_Framework_Model_Modules_Affiliate_Router
 * @group              Shopgate_Modules_Affiliate
 */
class Shopgate_Framework_Test_Model_Modules_Affiliate_RouterTest extends Shopgate_Framework_Test_Model_Utility
{
    /**
     * @param string $expected - expected value of the parameter
     * @param array  $params   - parameter list passed to setTrackingParameters
     *
     * @uses         ShopgateOrder::setTrackingGetParameters
     * @covers       ::getValidParams
     *
     * @dataProvider affiliateParamDataProvider
     */
    public function testMagestoreGetAffiliateParameter($expected, $params)
    {
        $this->getModuleConfig(Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator::MODULE_CONFIG);
        $router     = $this->getRouter($params);
        $parameters = $router->setDirectoryName('Magestore')->getValidator()->getValidParams();
        $this->assertEquals($expected, array_pop($parameters));
    }

    /**
     * @param string $expected - class name returned from method call result
     * @param array  $params   - parameter list passed to setTrackingParameters
     * @covers ::getValidator
     *
     * @dataProvider validatorDataProvider
     */
    public function testGetValidator($expected, $params)
    {
        $this->getModuleConfig(Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator::MODULE_CONFIG);
        //core_config_data key rewrite of a parameter that maps to Magestore
        Mage::app()->getStore(0)->setConfig('affiliateplus/refer/url_param_array', ',acc,account');
        $router    = $this->getRouter($params)->setDirectoryName('Magestore');
        $validator = $router->getValidator();

        $this->assertInstanceOf($expected, $validator);
    }

    /**
     * @expectedException Exception
     */
    public function testBadConstructorCall()
    {
        Mage::getModel('shopgate/modules_affiliate_router', array());
    }

    /**
     * Simple data sets
     *
     * @return array
     */
    public function affiliateParamDataProvider()
    {
        return array(
            array(
                'expected' => '12345',
                'params'   => array(
                    array('key' => 'test key', 'value' => 'test value'),
                    array('key' => 'acc', 'value' => '12345'),
                    array('key' => 'test_key2', 'value' => 'test_value2'),
                ),
            ),
            array(
                'expected' => 'hello',
                'params'   => array(
                    array('key' => 'acc', 'value' => 'hello'),
                    array('key' => 'test key', 'value' => 'test value'),
                    array('key' => 'test_key2', 'value' => 'test_value2'),
                ),
            ),
            array(
                'expected' => false,
                'params'   => array(),
            ),
        );
    }

    /**
     * @return array
     */
    public function validatorDataProvider()
    {
        return array(
            array(
                'Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator',
                'testing default param mapping' => array(
                    array('key' => 'test key', 'value' => 'test value'),
                    array('key' => 'acc', 'value' => '12345'),
                    array('key' => 'test_key2', 'value' => 'test_value2'),
                ),
            ),
            array(
                'Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator',
                'param rewrite in db config' => array(
                    array('key' => 'test key', 'value' => 'test value'),
                    array('key' => 'account', 'value' => '12345'),
                    array('key' => 'test_key2', 'value' => 'test_value2'),
                ),
            ),
            array(
                'Shopgate_Framework_Model_Modules_Validator',
                'param rewrite in db config' => array(
                    array('key' => 'test key', 'value' => 'test value'),
                ),
            ),
        );
    }

    /**
     * Router retriever
     *
     * @param $params - parameter list passed to setTrackingParameters
     *
     * @return Shopgate_Framework_Model_Modules_Affiliate_Router
     */
    private function getRouter($params)
    {
        $sgOrder = new ShopgateOrder();
        $sgOrder->setTrackingGetParameters($params);

        return Mage::getModel('shopgate/modules_affiliate_router', array($sgOrder));
    }

    /**
     * Unset the validator as it is used as a singleton, so it will
     * be returning the same set of parameters in the same test
     *
     * @after
     */
    public function tearDown()
    {
        Mage::unregister('_singleton/shopgate/modules_affiliate_packages_magestore_validator');
    }
}
