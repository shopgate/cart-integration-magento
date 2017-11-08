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
class Shopgate_Framework_Model_System_Config_Source_Product_Types
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                'label' => Mage::helper('shopgate')->__('Configurable')
            ),
            array(
                'value' => Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                'label' => Mage::helper('shopgate')->__('Grouped')
            ),
            array(
                'value' => Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
                'label' => Mage::helper('shopgate')->__('Bundle')
            ),
            array(
                'value' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'label' => Mage::helper('shopgate')->__('Simple')
            ),
            array(
                'value' => Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
                'label' => Mage::helper('shopgate')->__('Virtual')
            )
        );
    }
}
