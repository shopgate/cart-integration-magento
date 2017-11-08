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
class Shopgate_Framework_Model_Redirect_Route_Tags_Generic
{
    /**
     * Generates a default set of tags
     *
     * @param string $pageTitle - title of the page
     *
     * @return array
     */
    public function generate($pageTitle)
    {
        return array(
            Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_SITENAME       => $this->getSiteName(),
            Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_DESKTOP_URL    => $this->getShopUrl(),
            Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_MOBILE_WEB_URL => $this->getMobileUrl(),
            Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_TITLE          => $pageTitle
        );
    }

    /**
     * @return string
     */
    private function getSiteName()
    {
        return Mage::app()->getWebsite()->getName();
    }

    /**
     * @return string
     */
    private function getShopUrl()
    {
        return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
    }

    /**
     * @return string
     */
    private function getMobileUrl()
    {
        $cname = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_CNAME);

        return rtrim($cname, '/');
    }
}
