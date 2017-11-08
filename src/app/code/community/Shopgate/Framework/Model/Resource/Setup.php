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
class Shopgate_Framework_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Get shop data
     */
    protected function _getShopSystemData()
    {
        Mage::helper('shopgate/install')->updateShopgateSystem();
    }

    /**
     * @param string $tableName
     *
     * @return array|bool|false
     */
    protected function _checkTable($tableName)
    {
        if (method_exists($this->getConnection(), 'isTableExists')) {
            return $this->getConnection()->isTableExists($tableName);
        } else {
            return $this->getConnection()->showTableStatus($tableName);
        }
    }
}
