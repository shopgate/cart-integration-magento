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
class Shopgate_Framework_Model_Shopgate_Cart_Validation_Stock extends Mage_Core_Model_Abstract
{
    /**
     * Validate stock of a quoteItem
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param float                       $priceInclTax
     * @param float                       $priceExclTax
     *
     * @return ShopgateCartItem $result
     */
    public function validateStock(Mage_Sales_Model_Quote_Item $item, $priceInclTax, $priceExclTax)
    {
        if ($item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $model = Mage::getModel('shopgate/shopgate_cart_validation_stock_bundle');
        } else {
            $model = Mage::getModel('shopgate/shopgate_cart_validation_stock_simple');
        }

        return $model->validateStock($item, $priceInclTax, $priceExclTax);
    }
}
