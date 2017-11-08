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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Observer
{
    /**
     * Check if the PAP Affiliate Cookie was set, if so, make it
     * a GET parameter so that our affiliate factory can pick it
     * up, then validate it and pass it to the library redirect
     *
     * @see Shopgate_Framework_Model_Config::addAffiliateParameterToRedirectable
     */
    public function execute()
    {
        $cookieName  = Shopgate_Framework_Model_Modules_Affiliate_Packages_Qualityunit_Validator::VISITOR_COOKIE;
        $cookieValue = Mage::app()->getCookie()->get($cookieName);
        if (!empty($cookieValue)) {
            Mage::app()->getRequest()->setQuery($cookieName, $cookieValue);
        }
    }
}
