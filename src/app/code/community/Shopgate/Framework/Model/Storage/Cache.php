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
class Shopgate_Framework_Model_Storage_Cache
{
    /**
     * Shopgate cache tag to clear
     */
    const SHOPGATE_CACHE_TAG = 'SHOPGATE_CACHE';

    /**
     * Loads the entry from the cache and un-serializes it
     *
     * @param string $cacheId - cache to load
     *
     * @return false | string - Un-serialized data is returned
     */
    public function loadCache($cacheId)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$result = Mage::app()->loadCache($cacheId)) {
            return false;
        }

        return unserialize($result);
    }

    /**
     * Saves the passed data in the Shopgate cache
     *
     * @param string $data    - un-serialized data
     * @param string $cacheId - id to save the cache in
     * @param int    $time    - how long to hold the cache in seconds
     *
     * @return false | Mage_Core_Model_App
     */
    public function saveCache($data, $cacheId, $time = 7200)
    {
        if (!$this->isEnabled() || empty($data)) {
            return false;
        }
        $tags[]     = self::SHOPGATE_CACHE_TAG;
        $serialized = serialize($data);

        return Mage::app()->saveCache($serialized, $cacheId, $tags, $time);
    }

    /**
     * Checks if Shopgate cache is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::app()->useCache('shopgate');
    }
}
