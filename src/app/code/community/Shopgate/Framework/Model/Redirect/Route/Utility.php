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
class Shopgate_Framework_Model_Redirect_Route_Utility
{
    private $mappings;

    /**
     * Instantiate mapping of classes
     */
    public function __construct()
    {
        $this->mappings = array(
            Shopgate_Framework_Model_Redirect_Route_Type_Home::CONTROLLER_KEY     => 'home',
            Shopgate_Framework_Model_Redirect_Route_Type_Category::CONTROLLER_KEY => 'category',
            Shopgate_Framework_Model_Redirect_Route_Type_Product::CONTROLLER_KEY  => 'product',
            Shopgate_Framework_Model_Redirect_Route_Type_Page::CONTROLLER_KEY     => 'page',
            Shopgate_Framework_Model_Redirect_Route_Type_Search::CONTROLLER_KEY   => 'search',
            Shopgate_Framework_Model_Redirect_Route_Type_Generic::CONTROLLER_KEY  => 'generic'
        );
    }

    /**
     * @return false | Shopgate_Framework_Model_Redirect_Route_TypeInterface
     */
    public function getRoute()
    {
        $controllerName = $this->getControllerName();
        if (!isset($this->mappings[$controllerName])) {
            $controllerName = 'default';
        }

        return Mage::getModel('shopgate/redirect_route_type_' . $this->mappings[$controllerName]);
    }


    /**
     * Retrieve controller name while running it through events
     *
     * @return string
     */
    private function getControllerName()
    {
        $info = new Varien_Object(array('controller_name' => Mage::app()->getRequest()->getControllerName()));
        Mage::dispatchEvent('shopgate_routing_controller_name', array('info' => $info));

        return $info->getData('controller_name');
    }
}
