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
class Shopgate_Framework_Model_Redirect_Route_Type_Page extends Shopgate_Framework_Model_Redirect_Route_Type_Generic
{
    const CONTROLLER_KEY = 'page';
    const PAGE_CACHE_KEY = 'SG_page_key';

    /** @var Shopgate_Framework_Model_Storage_Cache */
    protected $cache;
    /** @var Mage_Cms_Model_Page */
    protected $cmsPages;

    /**
     * Init tags class
     */
    public function __construct()
    {
        $this->cache    = Mage::getSingleton('shopgate/storage_cache');
        $this->cmsPages = Mage::getModel('cms/page');
        parent::__construct();
    }

    /**
     * Using caching to not load page model too many times
     *
     * @inheritdoc
     */
    public function callScriptBuilder(\Shopgate_Helper_Redirect_Type_TypeInterface $redirect)
    {
        $pageId   = $this->getSpecialId();
        $cacheKey = self::PAGE_CACHE_KEY . '_' . Mage::app()->getStore()->getId() . '_' . $pageId;
        $pageKey  = $this->cache->loadCache($cacheKey);

        if ($pageKey === false) {
            $pageKey = $this->getUrlKey();
            $this->cache->saveCache($pageKey, $cacheKey);
        }

        return $redirect->loadCms($pageKey);
    }

    /**
     * @return string - URL key to redirect to
     */
    private function getUrlKey()
    {
        $pageKey = $this->getCurrentPage()->getIdentifier();
        $map     = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_CMS_MAP);

        if (is_string($map) && strpos($map, '{') !== false) {
            $cmsMap = (array)unserialize($map);

            foreach ($cmsMap as $map) {
                if ($map[Shopgate_Framework_Block_CmsMap::MAGE_PAGE_KEY] === $pageKey) {
                    return $map[Shopgate_Framework_Block_CmsMap::SG_PAGE_KEY];
                }
            }
        }

        return $pageKey;
    }

    /**
     * Returns the ID of the page
     *
     * @inheritdoc
     */
    protected function getSpecialId()
    {
        return Mage::app()->getRequest()->getParam('page_id');
    }

    /**
     * Returns the page name
     *
     * @inheritdoc
     */
    protected function getTitle()
    {
        return $this->getCurrentPage()->getTitle();
    }

    /**
     * @return Mage_Cms_Model_Page
     */
    private function getCurrentPage()
    {
        return $this->cmsPages->load($this->getSpecialId());
    }
}
