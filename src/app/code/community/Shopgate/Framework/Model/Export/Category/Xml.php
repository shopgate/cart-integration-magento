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
class Shopgate_Framework_Model_Export_Category_Xml extends Shopgate_Framework_Model_Export_Category
{
    /** @var  Mage_Catalog_Model_Category */
    protected $item;
    /** @var int | null */
    protected $_maxPosition = null;

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setMaximumPosition($position)
    {
        $this->_maxPosition = $position;
        return $this;
    }

    /**
     * @return null | int
     */
    public function getMaximumPosition()
    {
        return $this->_maxPosition;
    }

    /**
     * Generate data dom object
     *
     * @return $this
     */
    public function generateData()
    {
        foreach ($this->fireMethods as $method) {
            $this->{$method}($this->item);
        }

        return $this;
    }

    /**
     * Set category id
     */
    public function setUid()
    {
        parent::setUid($this->item->getId());
    }

    /**
     * Set category sort order
     */
    public function setSortOrder()
    {
        parent::setSortOrder($this->getMaximumPosition() - $this->item->getData('position'));
    }

    /**
     * Set category name
     */
    public function setName()
    {
        parent::setName($this->item->getName());
    }

    /**
     * Set parent category id
     */
    public function setParentUid()
    {
        parent::setParentUid($this->item->getParentId() != $this->_parentId ? $this->item->getParentId() : null);
    }

    /**
     * Category link in shop
     */
    public function setDeeplink()
    {
        parent::setDeeplink($this->getDeepLinkUrl($this->item));
    }

    /**
     * Check if category is anchor
     */
    public function setIsAnchor()
    {
        parent::setIsAnchor($this->item->getData('is_anchor'));
    }

    /**
     * Set category image
     */
    public function setImage()
    {
        if ($this->item->getImageUrl()) {
            $imageItem = new Shopgate_Model_Media_Image();

            $imageItem->setUid(1);
            $imageItem->setSortOrder(1);
            $imageItem->setUrl($this->getImageUrl($this->item));
            $imageItem->setTitle($this->item->getName());

            parent::setImage($imageItem);
        }
    }

    /**
     * Set active state
     */
    public function setIsActive()
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

        $isActive = $this->item->getData('is_active');
        if (in_array($this->item->getId(), $catIdsArray)
            || array_intersect(
                $catIdsArray,
                $this->item->getParentIds()
            )
        ) {
            $isActive = 1;
        }

        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_NAVIGATION_CATEGORIES_ONLY)
            && !$this->item->getData('include_in_menu')
        ) {
            $isActive = 0;
        }

        parent::setIsActive($isActive);
    }
}
