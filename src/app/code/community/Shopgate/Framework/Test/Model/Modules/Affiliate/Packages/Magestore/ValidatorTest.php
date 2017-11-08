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
 * Testing Magestore parameter grabber.
 * Should retrieve the default "acc" key if nothing
 * is set in the system > configuration
 *
 * @coversDefaultClass Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator
 * @group              Shopgate_Modules_Affiliate
 */
class Shopgate_Framework_Test_Model_Modules_Affiliate_Packages_Magestore_ValidatorTest
    extends Shopgate_Framework_Test_Model_Utility
{

    public function setUp()
    {
        $this->getModuleConfig(Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator::MODULE_CONFIG);
    }

    /**
     * @param string $expected - class name returned from method call result
     * @param array  $config   - sets system > config custom KEY to be used
     * @param array  $params
     * @covers ::getValidParams
     *
     * @dataProvider validatorDataProvider
     */
    public function testGetValidParams($expected, $config, $params)
    {
        Mage::app()->getStore(0)->setConfig('affiliateplus/refer/url_param_array', $config);
        $class  = Mage::getModel('shopgate/modules_affiliate_packages_magestore_validator', array($params));
        $return = $class->getValidParams();

        $this->assertEquals($expected, $return);
    }

    /**
     * @return array
     */
    public function validatorDataProvider()
    {
        return array(
            'default value used if config empty'   => array(
                'expected'     => array('acc' => '1234'),
                'config value' => '',
                'get params'   => array(
                    array(
                        'key'   => 'acc',
                        'value' => '1234'
                    )
                )
            ),
            'rewrites the default value to new'    => array(
                'expected'     => array('account' => '012'),
                'config value' => ',acc,account',
                'get params'   => array(
                    array(
                        'key'   => 'account',
                        'value' => '012'
                    )
                )
            ),
            'a few values in config, last matters' => array(
                'expected'     => array('test' => '567'),
                'config value' => ',acc,account,test',
                'get params'   => array(
                    array(
                        'key'   => 'test',
                        'value' => '567'
                    )
                )
            ),
            'return if value is empty'             => array(
                'expected'     => array(),
                'config value' => ',acc,account',
                'get params'   => array(
                    array(
                        'key'   => 'account',
                        'value' => ''
                    )
                )
            )
        );
    }

    /**
     * Runs check genericChecker through some scenarios
     *
     * @param string $expected - class name returned from method call result
     * @param array  $params   - array(array('key' => 'get key', 'value' => 'get value'))
     *
     * @covers       ::checkGenericValid
     *
     * @dataProvider checkGenericValidProvider
     */
    public function testCheckGenericValid($expected, $params)
    {
        $class = Mage::getModel('shopgate/modules_affiliate_packages_magestore_validator', array($params));
        $valid = $class->checkGenericValid();

        $this->assertEquals($expected, $valid);
    }

    /**
     * @return array
     */
    public function checkGenericValidProvider()
    {
        return array(
            'correct param in get'          => array(
                'expected' => true,
                'params'   => array(
                    array(
                        'key'   => 'acc',
                        'value' => '1234'
                    )
                )
            ),
            'multiple params, 1 correct'    => array(
                'expected' => true,
                'params'   => array(
                    array(
                        'key'   => 'random',
                        'value' => '1234'
                    ),
                    array(
                        'key'   => 'acc',
                        'value' => '1234'
                    )
                )
            ),
            'incorrect param in get'        => array(
                'expected' => false,
                'params'   => array(
                    array(
                        'key'   => 'account',
                        'value' => '1234'
                    )
                )
            ),
            'correct param but empty value' => array(
                'expected' => false,
                'params'   => array(
                    array(
                        'key'   => 'acc',
                        'value' => null
                    )
                )
            ),
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mage::app()->getStore(0)->setConfig('affiliateplus/refer/url_param_array', '');
    }
}
