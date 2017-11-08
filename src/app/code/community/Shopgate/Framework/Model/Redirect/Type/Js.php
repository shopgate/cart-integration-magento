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
class Shopgate_Framework_Model_Redirect_Type_Js implements Shopgate_Framework_Model_Redirect_Type_TypeInterface
{

    /** @var Shopgate_Framework_Model_Storage_Cache */
    protected $cache;
    /** @var ShopgateLogger */
    protected $logger;
    /** @var Shopgate_Framework_Model_Storage_Session */
    protected $session;

    /**
     * As if we using a DIs
     */
    public function __construct()
    {
        $this->cache   = Mage::getModel('shopgate/storage_cache');
        $this->session = Mage::getSingleton('shopgate/storage_session');
        $this->logger  = ShopgateLogger::getInstance();
    }

    /**
     * Executes the redirect logic
     */
    public function run()
    {
        $route    = Mage::getModel('shopgate/redirect_route_utility')->getRoute();
        $cacheKey = $route->getCacheKey();
        $jsHeader = $this->cache->loadCache($cacheKey);

        if ($jsHeader === false) {
            $redirect = Mage::getModel('shopgate/redirect_builder')->buildJsRedirect();

            if (!$redirect) {
                return;
            }

            $redirect->getBuilder()->setSiteParameters($route->getTags());
            $this->dispatchEvent($route, $redirect);
            $jsHeader = $route->callScriptBuilder($redirect);
            $cached   = $this->cache->saveCache($jsHeader, $cacheKey);

            if (!$cached) {
                $this->logger->log(
                    'Could not save header to cache. It was enabled: ' . $this->cache->isEnabled() .
                    '. Header is empty: ' . empty($jsHeader),
                    ShopgateLogger::LOGTYPE_DEBUG
                );
            }
        }

        $this->session->setScript($jsHeader);
    }

    /**
     * @param Shopgate_Framework_Model_Redirect_Route_TypeInterface $route
     * @param Shopgate_Helper_Redirect_Type_Js                      $redirect
     */
    private function dispatchEvent(
        Shopgate_Framework_Model_Redirect_Route_TypeInterface $route,
        Shopgate_Helper_Redirect_Type_Js $redirect
    ) {
        Mage::dispatchEvent('shopgate_redirect_type_js', array('route' => $route, 'redirect' => $redirect));
    }
}
