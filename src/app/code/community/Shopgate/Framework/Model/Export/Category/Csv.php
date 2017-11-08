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
class Shopgate_Framework_Model_Export_Category_Csv extends Shopgate_Framework_Model_Export_Category
{
    /**
     * @var null
     */
    protected $_actionCache = null;

    /**
     * @var null
     */
    protected $_defaultRow = null;

    /**
     * @var null
     */
    protected $_maxPosition = null;

    /**
     * @return array
     */
    public function generateData()
    {
        foreach (array_keys($this->_defaultRow) as $key) {
            $action = "_set" . uc_words($key, '', '_');
            if (empty($this->_actionCache[$action])) {
                $this->_actionCache[$action] = true;
            }
        }

        foreach (array_keys($this->_actionCache) as $_action) {
            $this->{$_action}($this->item);
        }

        return $this->_defaultRow;
    }

    /**
     * @param $defaultRow
     *
     * @return Shopgate_Framework_Model_Export_Category_Csv
     */
    public function setDefaultRow($defaultRow)
    {
        $this->_defaultRow = $defaultRow;
        return $this;
    }

    /**
     * @param $position
     *
     * @return $this
     */
    public function setMaximumPosition($position)
    {
        $this->_maxPosition = $position;
        return $this;
    }

    /**
     * @return null
     */
    public function getMaximumPosition()
    {
        return $this->_maxPosition;
    }

    /**
     * Fill the Field category_number in the given array
     * 3rd param $isRoot bool default false
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setCategoryNumber($category)
    {
        $this->_defaultRow['category_number'] = $category->getId();
    }


    /**
     * Fill the Field category_name in the given array
     * 3rd param $isRoot bool default false
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setCategoryName($category)
    {
        $this->_defaultRow['category_name'] = $category->getName();
    }


    /**
     * Fill the Field parent_id in the given array
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setParentId($category)
    {
        $this->_defaultRow['parent_id'] = $this->_parentId != $category->getParentId() ? $category->getParentId() : "";
    }

    /**
     * Fill the Field order_index in the given array
     * 3rd param $isRoot bool default false
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setOrderIndex($category)
    {
        $this->_defaultRow['order_index'] = $this->getMaximumPosition() - $category->getPosition();
    }


    /**
     * Fill the Field is_active in the given array
     * param $isRoot = false not needed here
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setIsActive($category)
    {
        $catIds      = Mage::getStoreConfig(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_HIDDEN_CATEGORIES
        );
        $catIdsArray = array();

        if (!empty($cat_ids)) {
            $catIdsArray = explode(',', $catIds);
            foreach ($catIdsArray as &$catId) {
                $catId = trim($catId);
            }
        }

        $isActive = $category->getIsActive();
        if (in_array($category->getId(), $catIdsArray) || array_intersect($catIdsArray, $category->getParentIds())) {
            $isActive = 1;
        }
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_NAVIGATION_CATEGORIES_ONLY)
            && !$category->getIncludeInMenu()
        ) {
            $isActive = 0;
        }
        $this->_defaultRow['is_active'] = $isActive;
    }

    /**
     * Fill the Field url_image in the given array
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setUrlImage($category)
    {
        $this->_defaultRow['url_image'] = $this->getImageUrl($category);
    }

    /**
     * Fill the Field url deep link in the given array
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _setUrlDeepLink($category)
    {
        $this->_defaultRow['url_deeplink'] = $this->getDeepLinkUrl($category);
    }
}
