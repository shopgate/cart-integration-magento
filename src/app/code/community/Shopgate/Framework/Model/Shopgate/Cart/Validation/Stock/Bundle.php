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
class Shopgate_Framework_Model_Shopgate_Cart_Validation_Stock_Bundle
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
        $product = $item->getProduct();
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem  = $product->getStockItem();
        $errors     = array();
        $isBuyable  = true;
        $qtyBuyable = null;

        if (null == $product->getShopgateItemNumber()) {
            $product->setShopgateItemNumber($product->getId());
        }

        foreach ($item->getChildren() as $childItem) {
            /** @var Mage_Catalog_Model_Product $childProduct */
            $childProduct = $childItem->getProduct();
            /** @var Mage_CatalogInventory_Model_Stock_Item $childStock */
            $childStock = $childProduct->getStockItem();
            if (!$childProduct->isAvailable()
                || ($childStock->getManageStock() && !$childProduct->isSaleable() && !$childStock->getBackorders())) {
                $isBuyable        = false;
                $error            = array();
                $error['type']    = ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK;
                $error['message'] = ShopgateLibraryException::getMessageFor(
                    ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK
                );
                $errors[]         = $error;
            } else {
                if ($childStock->getManageStock()
                    && !$childStock->checkQty($childItem->getQty())
                    && !$childStock->getBackorders()
                ) {
                    $isBuyable        = false;
                    $error            = array();
                    $error['type']    = ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE;
                    $error['message'] = ShopgateLibraryException::getMessageFor(
                        ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE
                    );
                    $errors[]         = $error;
                    if ($qtyBuyable == null || $qtyBuyable > $childStock->getQty()) {
                        $qtyBuyable = $childStock->getQty();
                    }
                } else {
                    if (Mage::helper('shopgate/config')->getIsMagentoVersionLower1410()) {
                        $checkIncrements = Mage::helper('shopgate')->checkQtyIncrements(
                            $childStock,
                            $childItem->getQty()
                        );
                    } else {
                        $checkIncrements = $childStock->checkQtyIncrements($childItem->getQty());
                    }

                    if ($childStock->getManageStock() && $checkIncrements->getHasError()) {
                        $isBuyable        = false;
                        $error            = array();
                        $error['type']    = ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE;
                        $error['message'] = ShopgateLibraryException::getMessageFor(
                            ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE
                        );
                        $errors[]         = $error;
                        $stockItem->setQty(
                            (int)($item->getQtyToAdd() / $stockItem->getQtyIncrements())
                            * $stockItem->getQtyIncrements()
                        );
                    }
                }
            }
        }

        $qtyBuyable = $qtyBuyable == null ? (int)$item->getQty() : (int)$qtyBuyable;

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
