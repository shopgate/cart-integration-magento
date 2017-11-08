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
class Shopgate_Framework_Test_Model_ProductUtility
{
    /** @var int $currentStore */
    protected $currentStore;
    /** @var Mage_Catalog_Model_Product[] */
    protected $productList = array();

    /**
     * @return int
     */
    public function getCurrentStore()
    {
        if (!$this->currentStore) {
            $this->currentStore = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        return $this->currentStore;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setCurrentStore($storeId)
    {
        $this->currentStore = $storeId;

        return $this;
    }

    /**
     * Creates a fake simple product, but does not save it
     *
     * @return Mage_Catalog_Model_Product
     */
    public function createSimpleProduct()
    {
        $id = rand(0, 99999);
        Mage::app()->setCurrentStore($this->getCurrentStore());
        $product = Mage::getModel('catalog/product');
        $product->setData(
            array(
                'website_ids'      => array(1),
                'type_id'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'attribute_set_id' => 9,
                'created_at'       => strtotime('now'),
                'sku'              => 'product_' . $id,
                'name'             => 'Test Product ' . $id,
                'status'           => 1,
                'tax_class_id'     => 0,
                'visibility'       => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                'price'            => '10',
                'stock_data'       => array(
                    'use_config_manage_stock' => 0,
                    'manage_stock'            => 1,
                    'min_sale_qty'            => 1,
                    'max_sale_qty'            => 999,
                    'is_in_stock'             => 1,
                    'qty'                     => 999
                ),
                'category_ids'     => array(1, 2),
                'media_gallery'    => array('images' => array(), 'values' => array())
            )
        );
        $this->productList[] = $product;

        return $product;
    }

    /**
     * Price reindex
     * todo-sg: still needs testing, may need to remove as it does not seem to work
     */
    public function reindexPrices()
    {
        $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
        $process->reindexEverything();
    }

    /**
     * Creates a fake entry for the index table to get the
     * group pricing
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float                      $price
     * @param float                      $groupPrice
     * @param int|string                 $groupId
     * @param int                        $websiteId
     */
    public function updatePriceIndexTable($product, $price, $groupPrice, $groupId, $websiteId = 0)
    {
        $sql = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql->query(
            "INSERT INTO catalog_product_index_price "
            . "(entity_id, customer_group_id, website_id, tax_class_id, price, final_price, min_price, max_price, tier_price,group_price) VALUES "
            . "({$product->getId()}, {$groupId}, {$websiteId}, 1, {$price}, {$groupPrice},{$groupPrice}, {$groupPrice}, null, {$groupPrice})"
        );
    }

    /**
     * Removes all products that were saved
     * to the unit database
     */
    public function deleteProducts()
    {
        foreach ($this->productList as $product) {
            if ($product->isDeleteable()) {
                $product->delete();
            }
        }
    }
}