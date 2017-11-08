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
 * @coversDefaultClass Shopgate_Framework_Model_Modules_Affiliate_Packages_KProject_Validator
 * @group              Shopgate_Modules_Affiliate
 */
class Shopgate_Framework_Test_Model_Modules_Affiliate_Packages_KProject_ValidatorTest
    extends Shopgate_Framework_Test_Model_Utility
{

    public function setUp()
    {
        $this->getModuleConfig(Shopgate_Framework_Model_Modules_Affiliate_Packages_KProject_Validator::MODULE_CONFIG);
    }

    /**
     * @param array $expected
     * @param array $params
     *
     * @covers ::getValidParams
     * @covers ::assignParamsToKeys
     * @covers ::isKeySetButNoValue
     * @covers ::removeUnassignedKeys
     *
     * @dataProvider getValidParamsProvider
     */
    public function testGetValidParams($expected, $params)
    {
        $class  = Mage::getModel('shopgate/modules_affiliate_packages_kProject_validator', array($params));
        $return = $class->getValidParams();

        $this->assertEquals($expected, $return);
    }

    /**
     * @return array
     */
    public function getValidParamsProvider()
    {
        return array(
            'single param'       => array(
                'expected'   => array(
                    'userID' => '12345'
                ),
                'get params' => array(
                    array(
                        'key'   => 'userID',
                        'value' => '12345'
                    ),
                    array(
                        'key'   => 'random',
                        'value' => '903'
                    )
                )
            ),
            'both params set'    => array(
                'expected'   => array(
                    'userID' => '12345',
                    'sscid'  => '908',
                ),
                'get params' => array(
                    array(
                        'key'   => 'random',
                        'value' => '903'
                    ),
                    array(
                        'key'   => 'sscid',
                        'value' => '908'
                    ),
                    array(
                        'key'   => 'userID',
                        'value' => '12345'
                    )

                )
            ),
            'no params set'      => array(
                'expected'   => array(),
                'get params' => array(
                    array(
                        'key'   => 'random',
                        'value' => '908'
                    )
                )
            ),
            'empty value passed' => array(
                'expected'   => array(),
                'get params' => array(
                    array(
                        'key'   => 'userID',
                        'value' => ''
                    )
                )
            )
        );
    }
}
