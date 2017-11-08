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

include_once Mage::getBaseDir("lib") . '/Shopgate/shopgate.php';

class Shopgate_Framework_Test_Controller_FrameworkController
    extends EcomDev_PHPUnit_Test_Case_Controller
{
    protected function setUp()
    {
        Mage::register('isSecureArea', true);
        parent::setUp();
    }

    /**
     * @registry isSecureArea
     * @test
     * @doNotIndexAll
     * @loadFixture
     */
    public function testPluginNotActive()
    {
        $this->markTestSkipped(
            'Controller Test not yet available.'
        );

        $this->setCurrentStore('default');
        $this->dispatch('shopgate/framework/index');
        $this->assertResponseHttpCode('200');
        $this->assertResponseBodyContains(
            '{"error":12,"error_text":"plugin not activated: plugin not active"'
        );
    }
}