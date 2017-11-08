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
class Shopgate_Framework_Model_Redirect_Type_Js_Observer
{
    /** @var Shopgate_Framework_Helper_Redirect */
    protected $helper;

    /**
     * Init properties
     */
    public function __construct()
    {
        $this->helper = Mage::helper('shopgate/redirect');
    }

    /**
     * Called for JS script
     *
     * @param Varien_Event_Observer $event
     *
     * @return void
     */
    public function execute(Varien_Event_Observer $event)
    {
        /** @var Shopgate_Framework_Model_Redirect_Route_TypeInterface $route */
        $route = $event->getData('route');
        /** @var Shopgate_Helper_Redirect_Type_Js $redirect */
        $redirect = $event->getData('redirect');

        if (!$route || !$redirect) {
            return;
        }

        $suppress       = false;
        $controllerName = $route->getKey();

        if ($controllerName === Shopgate_Framework_Model_Redirect_Route_Type_Generic::CONTROLLER_KEY
            && $this->helper->idDefaultRedirectDisabled()
        ) {
            $suppress = true;
        }

        if (in_array(Mage::app()->getRequest()->getRouteName(), $this->helper->getBlockedRoutes())
            || in_array($controllerName, $this->helper->getBlockedControllers())
        ) {
            $suppress = true;
        }

        if ($controllerName === Shopgate_Framework_Model_Redirect_Route_Type_Product::CONTROLLER_KEY
            && in_array(Mage::app()->getRequest()->getParam('id'), $this->helper->getBlockedProducts())
        ) {
            $suppress = true;
        }

        if ($controllerName === Shopgate_Framework_Model_Redirect_Route_Type_Category::CONTROLLER_KEY
            && in_array(Mage::app()->getRequest()->getParam('id'), $this->helper->getBlockedCategories())
        ) {
            $suppress = true;
        }

        $redirect->getBuilder()->suppressWebAppRedirect($suppress);
    }
}
