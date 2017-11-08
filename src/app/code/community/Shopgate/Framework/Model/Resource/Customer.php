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
class Shopgate_Framework_Model_Resource_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize configuration data
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('shopgate/customer', 'id');
    }

    /**
     * Load relation by customer id
     *
     * @throws Mage_Core_Exception
     *
     * @param Shopgate_Framework_Model_Customer $customer
     * @param string                            $id
     *
     * @return Shopgate_Framework_Model_Resource_Customer
     */
    public function loadByCustomerId(Shopgate_Framework_Model_Customer $customer, $id)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('customer_id' => $id);
        $select  = $adapter->select()
                           ->from($this->getMainTable())
                           ->where('customer_id = :customer_id');

        $customerId = $adapter->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customer, $customerId);
        } else {
            $customer->setData(array());
        }

        return $this;
    }

    /**
     * Load relation by customer $token
     *
     * @throws Mage_Core_Exception
     *
     * @param Shopgate_Framework_Model_Customer $customer
     * @param string                            $token
     *
     * @return Shopgate_Framework_Model_Resource_Customer
     */
    public function loadByToken(Shopgate_Framework_Model_Customer $customer, $token)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('token' => $token);
        $select  = $adapter->select()
                           ->from($this->getMainTable())
                           ->where('token = :token');

        $customerId = $adapter->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customer, $customerId);
        }

        return $this;
    }
}
