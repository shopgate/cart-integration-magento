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
class Shopgate_Framework_Helper_Coupon_Attribute
{
    /**
     * @return null|Mage_Eav_Model_Entity_Attribute_Set
     */
    public function getShopgateAttributeSet()
    {
        /** @var Mage_Eav_Model_Mysql4_Entity_Attribute_Set_Collection $collection */
        $collection = Mage::getModel('eav/entity_attribute_set')->getCollection();
        $item       = $collection->addFieldToFilter(
            'attribute_set_name',
            Shopgate_Framework_Helper_Coupon::COUPON_ATTRIUBTE_SET_NAME
        )->getFirstItem();

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $item->getId() ? $item : null;
    }

    /**
     * @param null | int | string                          $setId
     * @param null | Mage_Eav_Model_Entity_Attribute_Group $group
     *
     * @return Mage_Core_Model_Abstract|null
     */
    public function createGroup($setId, $group)
    {
        if ($group->getId() && $setId) {
            return Mage::getModel('eav/entity_attribute_group')
                       ->setAttributeSetId($setId)
                       ->setAttributeGroupName($group->getAttributeGroupName())
                       ->setSortOrder(1)
                       ->setDefaultId(1)
                       ->save();
        }

        return null;
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Group
     */
    public function getGeneralGroup()
    {
        /** @var Mage_Eav_Model_Mysql4_Entity_Attribute_Group_Collection $collection */
        $collection = Mage::getModel('eav/entity_attribute_group')->getCollection();
        /** @var Mage_Eav_Model_Entity_Attribute_Group $item */
        $item = $collection->addFieldToFilter('attribute_group_name', 'General')
                           ->getFirstItem();

        return $item;
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Set|null
     */
    public function createShopgateCouponAttributeSet()
    {
        $entityTypeId = Mage::getModel('catalog/product')
                            ->getResource()->getEntityType()->getId();

        /** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSet */
        $attributeSet = Mage::getModel('eav/entity_attribute_set')
                            ->setEntityTypeId($entityTypeId)
                            ->setAttributeSetName(Shopgate_Framework_Helper_Coupon::COUPON_ATTRIUBTE_SET_NAME);

        $attributeSet->validate();
        $attributeSet->save();

        return $attributeSet;
    }
}
