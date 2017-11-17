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
 * The controller is needed only in the Cloud API integration, unfortunately
 * the /shopgate route is already used by Framework plugin.
 */
if (Mage::helper('core')->isModuleEnabled('Shopgate_Cloudapi')) {
    /** @noinspection PhpIncludeInspection */
    require_once Mage::getModuleDir('controllers', 'Shopgate_Cloudapi') . DS . 'V2Controller.php';

    class Shopgate_Framework_V2Controller extends Shopgate_Cloudapi_V2Controller
    {
    }
} else {
    class Shopgate_Framework_V2Controller extends Mage_Core_Controller_Front_Action
    {
    }
}
