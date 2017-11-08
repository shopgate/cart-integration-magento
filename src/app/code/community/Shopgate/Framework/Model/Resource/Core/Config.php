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
class Shopgate_Framework_Model_Resource_Core_Config extends Mage_Core_Model_Config
{
    /**
     * Get config data by website scope
     *
     * @param string $path
     * @param array  $allowValues
     * @param string $useAsKey
     *
     * @return array
     */
    public function getConfigDataByWebsite($path, $allowValues = array(), $useAsKey = 'id')
    {
        $storeValues = array();
        $stores      = Mage::app()->getConfig()->getNode('websites');
        foreach ($stores->children() as $code => $store) {
            switch ($useAsKey) {
                case 'id':
                    $key = (int)$store->descend('system/website/id');
                    break;
                case 'code':
                    $key = $code;
                    break;
                case 'name':
                    $key = (string)$store->descend('system/website/name');
                    break;
                default:
                    $key = false;
            }
            if ($key === false) {
                continue;
            }

            $pathValue = (string)$store->descend($path);

            if (empty($allowValues)) {
                $storeValues[$key] = $pathValue;
            } else {
                if (in_array($pathValue, $allowValues)) {
                    $storeValues[$key] = $pathValue;
                }
            }
        }

        return array_filter($storeValues);
    }
}
