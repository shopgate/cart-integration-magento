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
class Shopgate_Framework_Model_Resource_Shopgate_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('shopgate/shopgate_order');
    }

    /**
     * Filter orders which are not synced for cron use
     *
     * @return $this
     */
    public function getUnsyncedOrders()
    {
        $this->getSelect()->where('is_sent_to_shopgate=?', '0');

        return $this;
    }

    /**
     * @return $this
     */
    public function getAlreadyCancelledOrders()
    {
        $this->getSelect()->where('is_cancellation_sent_to_shopgate=?', '0');

        return $this;
    }
}
