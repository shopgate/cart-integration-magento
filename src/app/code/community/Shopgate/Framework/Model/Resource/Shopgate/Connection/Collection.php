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
class Shopgate_Framework_Model_Resource_Shopgate_Connection_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'shopgate_connection_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'connection_collection';

    /**
     * Model initialization
     */
    protected function _construct()
    {
        $this->_init('core/config_data');
    }

    /**
     * Minimize usual count select
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        /* @var $countSelect Varien_Db_Select */
        $countSelect = parent::getSelectCountSql();
        $countSelect->resetJoinLeft();

        return $countSelect;
    }

    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $idsSelect;
    }

    /**
     * Loads relevant data from other core/config_data entries for the grid
     *
     * @return void
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->getItems() as $collectionItem) {
            $shopgateShopConnection = Mage::getModel('shopgate/shopgate_connection')
                                          ->load($collectionItem->getId());

            $collectionItem->setStatus($shopgateShopConnection->getStatus())
                           ->setDefaultStoreView($shopgateShopConnection->getDefaultStoreViewId())
                           ->setRelatedStoreViews($shopgateShopConnection->getRelatedStoreViews())
                           ->setCurrency($shopgateShopConnection->getBaseCurrency())
                           ->setCountry($shopgateShopConnection->getTaxDefaultCountry())
                           ->setMobileAlias($shopgateShopConnection->getMobileAlias())
                           ->setShopNumber($shopgateShopConnection->getShopNumber());
        }
    }
}
