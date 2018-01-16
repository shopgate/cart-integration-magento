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
if (Mage::getConfig()->getModuleConfig('Bubble_StockMovements')->is('active', 'true')) {
    class Shopgate_Framework_Model_Cataloginventory_Stock_Abstract extends Bubble_StockMovements_Model_CatalogInventory_Stock {}
} else {
    class Shopgate_Framework_Model_Cataloginventory_Stock_Abstract extends Mage_CatalogInventory_Model_Stock {}
}
class Shopgate_Framework_Model_Cataloginventory_Stock extends Shopgate_Framework_Model_Cataloginventory_Stock_Abstract
{
    /**
     * @param Varien_Object $item
     * @return $this|Mage_CatalogInventory_Model_Stock
     */
    public function registerItemSale(Varien_Object $item)
    {
        if (Mage::helper("shopgate")->isShopgateApiRequest()
            && Mage::helper("shopgate/config")->getIsMagentoVersionLower15()
        ) {
            return $this;
        }

        return parent::registerItemSale($item);
    }

}
