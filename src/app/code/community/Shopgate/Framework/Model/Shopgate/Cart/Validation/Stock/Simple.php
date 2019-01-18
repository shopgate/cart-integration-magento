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
class Shopgate_Framework_Model_Shopgate_Cart_Validation_Stock_Simple
    extends Shopgate_Framework_Model_Shopgate_Cart_Validation_Stock
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
        /** @var Mage_Catalog_Model_Product $product */
        $product = $item->getProduct();
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = $product->getStockItem();
        $isBuyable = true;

        if ($product->isConfigurable()) {
            $parent  = $product;
            $product = $product->getCustomOption('simple_product')->getProduct();
            $product->setShopgateItemNumber($parent->getShopgateItemNumber());
            $product->setShopgateOptions($parent->getShopgateOptions());
            $product->setShopgateInputs($parent->getShopgateInputs());
            $product->setShhopgateAttributes($parent->getShhopgateAttributes());
            $stockItem = $item->getProduct()->getCustomOption('simple_product')->getProduct()->getStockItem();
        }

        if (null == $product->getShopgateItemNumber()) {
            $product->setShopgateItemNumber($product->getId());
        }

        $errors = array();

        if (Mage::helper('shopgate/config')->getIsMagentoVersionLower1410()) {
            $checkIncrements = Mage::helper('shopgate')->checkQtyIncrements($stockItem, $item->getQty());
        } else {
            $checkIncrements = $stockItem->checkQtyIncrements($item->getQty());
        }

        if ($stockItem->getManageStock() && !$product->isSaleable() && !$stockItem->getBackorders()) {
            $isBuyable        = false;
            $error            = array();
            $error['type']    = ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK;
            $error['message'] = ShopgateLibraryException::getMessageFor(
                ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK
            );
            $errors[]         = $error;
        } elseif ($stockItem->getManageStock() && !$stockItem->checkQty($item->getQty()) && !$stockItem->getBackorders()
        ) {
            $isBuyable        = false;
            $error            = array();
            $error['type']    = ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE;
            $error['message'] = ShopgateLibraryException::getMessageFor(
                ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE
            );
            $errors[]         = $error;
        } elseif ($stockItem->getManageStock() && $checkIncrements->getHasError()) {
            $isBuyable        = false;
            $error            = array();
            $error['type']    = ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE;
            $error['message'] = ShopgateLibraryException::getMessageFor(
                ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE
            );
            $errors[]         = $error;
            $stockItem->setQty(
                (int)($item->getQtyToAdd() / $stockItem->getQtyIncrements()) * $stockItem->getQtyIncrements()
            );
        } elseif (!$product->isAvailable()) {
            $isBuyable        = false;
            $error            = array();
            $error['type']    = ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND;
            $error['message'] = ShopgateLibraryException::getMessageFor(
                ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND
            );
            $errors[]         = $error;
        }

        $qtyBuyable = $isBuyable ? (int)$item->getQty() : (int)$stockItem->getQty();

        return Mage::helper('shopgate')->generateShopgateCartItem(
            $product,
            $isBuyable,
            $qtyBuyable,
            $priceInclTax,
            $priceExclTax,
            $errors,
            (int)$stockItem->getQty()
        );
    }
}
