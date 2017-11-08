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
class Shopgate_Framework_Test_Model_Utility extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Classes short name to initialize,
     * this is the class functions you
     * will be testing by calling $this->class
     */
    const CLASS_SHORT_NAME = '';

    /**
     * Exclude group 'empty' to avoid
     * inflating the test number
     *
     * @coversNothing
     * @group empty
     */
    public function testEmpty()
    {
    }

    /**
     * Added support for PHP version 5.2
     * constant retrieval
     *
     * @param string $input
     *
     * @return mixed
     */
    protected final function getConstant($input)
    {
        $configClass = new ReflectionClass($this);

        return $configClass->getConstant($input);
    }

    /**
     * Enable module if it's installed.
     * Throw a skip if it's not.
     *
     * @param string $modulePath - magento module as defined in modules/config.xml
     */
    protected function activateModule($modulePath)
    {
        $config = $this->getModuleConfig($modulePath);

        if (!$config) {
            return;
        }

        $config->active = 'true';
    }

    /**
     * Disable module if it's installed.
     * Throw a skip if it's not.
     *
     * @param string $modulePath - magento module as defined in modules/config.xml
     */
    protected function deactivateModule($modulePath)
    {
        $config = $this->getModuleConfig($modulePath);

        if (!$config) {
            return;
        }

        $config->active = 'false';
    }

    /**
     * Handles grabbing module configuration and
     * throwing a method skip if nothing is returned
     *
     * @param $modulePath
     *
     * @return Varien_Simplexml_Object
     */
    protected function getModuleConfig($modulePath)
    {
        $config = Mage::getConfig()->getModuleConfig($modulePath);

        if (!$config) {
            $this->markTestSkipped($modulePath . ' plugin is not installed');
        }

        return $config;
    }

    /**
     * Enables module via inline fixture
     *
     * @param $xmlPath - core_config_data path to module's activation
     */
    protected function enableModule($xmlPath)
    {
        $this->setConfig($xmlPath, 1);
    }

    /**
     * Disables module via inline fixture
     *
     * @param $xmlPath - core_config_data path to module's activation
     */
    protected function disableModule($xmlPath)
    {
        $this->setConfig($xmlPath, 0);
    }

    /**
     * Sets the default config fixture for path
     *
     * @param $xmlPath - xml path of the fixture of store 0
     * @param $value   - value to set for the fixture
     */
    private function setConfig($xmlPath, $value)
    {
        Mage::app()->getStore(0)->setConfig($xmlPath, $value);
    }
}