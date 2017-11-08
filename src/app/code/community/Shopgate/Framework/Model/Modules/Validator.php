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
class Shopgate_Framework_Model_Modules_Validator
    extends Mage_Core_Model_Abstract
    implements Shopgate_Framework_Model_Interfaces_Modules_Validator
{
    const XML_CONFIG_ENABLED = '';
    const MODULE_CONFIG = '';

    /**
     * All around check for whether module is the one to use
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isEnabled() && $this->isModuleActive() && $this->checkGenericValid();
    }

    /**
     * Checks store config to be active
     *
     * @return bool
     */
    public function isEnabled()
    {
        $config  = $this->getConstant('XML_CONFIG_ENABLED');
        $val     = Mage::getStoreConfig($config);
        $enabled = !empty($val);
        if (!$enabled) {
            $debug = Mage::helper('shopgate')->__(
                'Enabled check by path "%s" was evaluated as empty: "%s" in class "%s"',
                $config,
                $val,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $enabled;
    }

    /**
     * Checks module node to be active
     *
     * @return mixed
     */
    public function isModuleActive()
    {
        $config = $this->getConstant('MODULE_CONFIG');
        $active = Mage::getConfig()->getModuleConfig($config)->is('active', 'true');

        if (!$active) {
            $debug = Mage::helper('shopgate')->__(
                'Module by config "%s" was not active in class "%s"',
                $config,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $active;
    }

    /**
     * Implement any custom validation
     *
     * @return bool
     */
    public function checkGenericValid()
    {
        return true;
    }

    /**
     * Added support for PHP version 5.2
     * constant retrieval
     *
     * @param string $input
     *
     * @return mixed
     */
    final protected function getConstant($input)
    {
        $configClass = new ReflectionClass($this);

        return $configClass->getConstant($input);
    }
}
