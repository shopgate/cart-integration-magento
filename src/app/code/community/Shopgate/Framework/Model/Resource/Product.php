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
class Shopgate_Framework_Model_Resource_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
{
    const INFLATE_SORT_ORDER = 100000;

    /**
     * Retrieve product category identifiers with position and max_position from category.
     * Added an inflate value because in magento the products assigned to a category have
     * higher priority over the products that are inherited via Anchor setting.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getCategoryIdsAndPosition($product)
    {
        $select = $this->_getReadAdapter()->select()
                       ->from(
                           array('c' => $this->_productCategoryTable),
                           array('category_id', 'position')
                       )->joinLeft(
                array('c2' => $this->_productCategoryTable),
                'c.category_id = c2.category_id',
                array('max_position' => new Zend_Db_Expr('max(c2.position)'))
            )->where('c.product_id=?', $product->getId())
                       ->group('c.category_id');

        $catAndPos = $this->_getReadAdapter()->fetchAll($select);
        $result    = array();

        foreach ($catAndPos as $cat) {
            $cat['max_position']         += self::INFLATE_SORT_ORDER;
            $result[$cat['category_id']] = $cat;
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($cat['category_id']);
            $anchors  = $category->getAnchorsAbove();
            if (($key = array_search(Mage::app()->getStore()->getRootCategoryId(), $anchors)) !== false) {
                unset($anchors[$key]);
            }

            foreach ($anchors as $anchor) {
                if (array_key_exists($anchor, $result)) {
                    continue;
                }
                $result[$anchor] = array(
                    'category_id'  => $anchor,
                    'position'     => $cat['position'],
                    'max_position' => $cat['max_position'] - self::INFLATE_SORT_ORDER
                );
            }
        }

        return $result;
    }
}
