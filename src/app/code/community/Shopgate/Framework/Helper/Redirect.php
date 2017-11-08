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
class Shopgate_Framework_Helper_Redirect extends Mage_Core_Helper_Abstract
{
    /**
     * Redirect true if
     * 1) Not admin page request
     * 2) Not an ajax request
     * 3) SG Config is valid (active, api_key, shop_number, customer_number)
     * 4) SG Module enabled for this store
     *
     * @param Shopgate_Framework_Model_Config $config
     *
     * @todo-sg: maybe some superhero can figure out how to prevent this from running in admin pages
     * @return bool
     */
    public function isAllowed(Shopgate_Framework_Model_Config $config)
    {
        return !Mage::app()->getStore()->isAdmin()
               && !(method_exists(Mage::app()->getRequest(), 'isAjax') && Mage::app()->getRequest()->isAjax())
               && $config->isValidConfig()
               && $config->getShopIsActive();
    }

    /**
     * Checks if the current script redirect is
     *
     * @return bool
     */
    public function isTypeJavascript()
    {
        return Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_REDIRECT_TYPE)
               === Shopgate_Framework_Model_Config::REDIRECTTYPE_JAVASCRIPT;
    }

    /**
     * Checks if the default redirect is disabled
     * which means we should only redirect to known
     * controller names, not Generic
     *
     * @return bool
     */
    public function idDefaultRedirectDisabled()
    {
        return Mage::getStoreConfigFlag(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ENABLE_DEFAULT_REDIRECT)
               === false;
    }

    /**
     * Retrieves disabled controller names
     * from the Shopgate config page
     *
     * @return array
     */
    public function getBlockedControllers()
    {
        return $this->getConfigList(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CONTROLLERS);

    }

    /**
     * Retrieves disabled route names
     * from the Shopgate config page
     *
     * @return array
     */
    public function getBlockedRoutes()
    {
        return $this->getConfigList(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_ROUTES);
    }

    /**
     * Retrieves a list of not allowed product ID's
     * from the Shopgate config page
     *
     * @return array
     */
    public function getBlockedProducts()
    {
        return $this->getConfigList(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_PRODUCTS);
    }

    /**
     * Retrieves a list of not allowed category ID's
     * from the Shopgate config page
     *
     * @return array
     */
    public function getBlockedCategories()
    {
        return $this->getConfigList(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CATEGORIES);
    }

    /**
     * Assists retrieving a comma delimited list
     *
     * @param string $configPath - core_config_data path
     *
     * @return array
     */
    private function getConfigList($configPath)
    {
        return explode(',', Mage::getStoreConfig($configPath));
    }
}
