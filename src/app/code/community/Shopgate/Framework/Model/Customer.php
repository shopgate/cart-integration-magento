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
 * @method setToken(string $value)
 * @method setCustomerId(int $value)
 * @method string getToken()
 * @method int getCustomerId()
 */
class Shopgate_Framework_Model_Customer extends Mage_Core_Model_Abstract
{
    /**
     * init model
     */
    protected function _construct()
    {
        $this->_init('shopgate/customer');
    }

    /**
     * Load relation by customer id
     *
     * @param   string $customerId
     *
     * @return  Shopgate_Framework_Model_Customer
     */
    public function loadByCustomerId($customerId)
    {
        $this->_getResource()->loadByCustomerId($this, $customerId);

        return $this;
    }

    /**
     * Load relation by token
     *
     * @param $token
     *
     * @return $this
     */
    public function loadByToken($token)
    {
        $this->_getResource()->loadByToken($this, $token);

        return $this;
    }
}
