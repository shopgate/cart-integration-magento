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
interface Shopgate_Framework_Model_Redirect_Route_TypeInterface
{
    /**
     * Returns the cache key for the JavaScript
     * html header to be stored under
     *
     * @return string
     */
    public function getCacheKey();

    /**
     * Runs the correct buildScript function of Shopgate Library
     *
     * @param Shopgate_Helper_Redirect_Type_TypeInterface $redirect
     *
     * @return string
     */
    public function callScriptBuilder(Shopgate_Helper_Redirect_Type_TypeInterface $redirect);

    /**
     * Generates tags for mobile redirect
     *
     * @return array
     */
    public function getTags();

    /**
     * Get route controller action key
     * e.g. index, page, category, etc.
     *
     * @return string
     */
    public function getKey();
}
