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
class Shopgate_Framework_Model_System_Config_Source_Attribute
{
    protected $_attributes;

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty Add empty option to array
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (!$this->_attributes) {
            $attributes = array();
            $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                              ->addFieldToFilter(
                                  'entity_type_id',
                                  Mage::getResourceModel('catalog/product')->getEntityType()->getId()
                              )
                              ->load();
            foreach ($collection->getItems() as $_attribute) {
                $attribute          = array();
                $attribute['value'] = $_attribute->getAttributeCode();
                $attribute['label'] = $_attribute->getAttributeCode();
                $attributes[]       = $attribute;
            }
            $this->_attributes = $attributes;
        }

        $attributes = $this->_attributes;
        if ($withEmpty) {
            array_unshift(
                $attributes,
                array(
                    'value' => '',
                    'label' => '-- Not Selected --',
                )
            );
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
