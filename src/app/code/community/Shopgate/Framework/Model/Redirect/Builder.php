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
class Shopgate_Framework_Model_Redirect_Builder
{
    /** @var Shopgate_Framework_Model_Config */
    protected $config;
    /** @var  Shopgate_Helper_Redirect_Type_Http */
    protected $http;
    /** @var  Shopgate_Helper_Redirect_Type_Js */
    protected $js;

    /**
     * Pretend to be DI
     */
    public function __construct()
    {
        $this->config = Mage::helper('shopgate/config')->getConfig();
        $this->logger = ShopgateLogger::getInstance();
    }

    /**
     * Instantiates the HTTP redirect object
     *
     * @return Shopgate_Helper_Redirect_Type_Http | null
     */
    public function buildHttpRedirect()
    {
        if (!is_null($this->http)) {
            return $this->http;
        }

        $builder   = new ShopgateBuilder($this->config);
        $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        $cookies   = Mage::getModel('core/cookie')->get();

        try {
            $this->http = $builder->buildHttpRedirect($userAgent, Mage::app()->getRequest()->getParams(), $cookies);
        } catch (\ShopgateMerchantApiException $e) {
            $this->logger->log('HTTP > oAuth access token for store not set: ' . $e->getMessage());

            return null;
        } catch (\Exception $e) {
            $this->logger->log('HTTP > error in HTTP redirect: ' . $e->getMessage());

            return null;
        }

        return $this->http;
    }

    /**
     * Instantiates the JS script builder object
     *
     * @return Shopgate_Helper_Redirect_Type_Js | null
     */
    public function buildJsRedirect()
    {
        if (!is_null($this->js)) {
            return $this->js;
        }

        $builder = new ShopgateBuilder($this->config);
        $cookies = Mage::getModel('core/cookie')->get();

        try {
            $this->js = $builder->buildJsRedirect(Mage::app()->getRequest()->getParams(), $cookies);
        } catch (\ShopgateMerchantApiException $e) {
            $this->logger->log('JS > oAuth access token for store not set: ' . $e->getMessage());

            return null;
        } catch (\Exception $e) {
            $this->logger->log('JS > error in HTTP redirect: ' . $e->getMessage());

            return null;
        }

        return $this->js;
    }
}
