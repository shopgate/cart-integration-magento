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
class Shopgate_Framework_Model_Redirect_Route_Type_Generic
    implements Shopgate_Framework_Model_Redirect_Route_TypeInterface
{

    const CONTROLLER_KEY = 'default';

    /** @var Shopgate_Framework_Model_Redirect_Route_Tags_Generic */
    protected $tags;

    /**
     * Init tags class
     */
    public function __construct()
    {
        $this->tags = Mage::getModel('shopgate/redirect_route_tags_generic');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey()
    {
        $route   = Mage::app()->getRequest()->getRouteName();
        $storeId = Mage::app()->getStore()->getId();

        return 'SG_' . $storeId . '_' . $route . '_' . $this->getKey() . '_' . $this->getSpecialId();
    }

    /**
     * Returns the ID of the page, by default
     * there is no ID
     *
     * @return string
     */
    protected function getSpecialId()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function callScriptBuilder(\Shopgate_Helper_Redirect_Type_TypeInterface $redirect)
    {
        return $redirect->loadDefault();
    }

    /**
     * @inheritdoc
     */
    public function getTags()
    {
        return $this->tags->generate($this->getTitle());
    }

    /**
     * @inheritdoc
     */
    protected function getTitle()
    {
        return Mage::app()->getWebsite()->getName();
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        $configClass = new ReflectionClass($this);

        return $configClass->getConstant('CONTROLLER_KEY');
    }
}
