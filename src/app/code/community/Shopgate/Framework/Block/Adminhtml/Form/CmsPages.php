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
class Shopgate_Framework_Block_Adminhtml_Form_CmsPages extends Mage_Core_Block_Html_Select
{

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        $collection = $this->getCollection();
        $options    = method_exists($collection, 'toOptionIdArray') ?
            $collection->toOptionIdArray() : $this->toOptionIdArray($collection);

        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     *
     * @return Shopgate_Framework_Block_Adminhtml_Form_CmsPages
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }

    /**
     * Returns the collection based on current page scope
     *
     * @return Mage_Cms_Model_Mysql4_Page_Collection | Mage_Cms_Model_Resource_Page_Collection
     */
    public function getCollection()
    {
        $store   = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');
        if ($store) {
            /** @var Mage_Core_Model_Config_Element $cfg */
            $storeId = Mage::getConfig()->getNode('stores')->{$store}->{'system'}->{'store'}->{'id'}->asArray();
        } elseif ($website) {
            /** @var Mage_Core_Model_Config_Element $cfg */
            $storeId =
                array_values(Mage::getConfig()->getNode('websites')->{$website}->{'system'}->{'stores'}->asArray());
        } else {
            $storeId = 0;
        }

        return Mage::getModel('cms/mysql4_page_collection')
                   ->addStoreFilter($storeId)
                   ->addFieldToFilter('is_active', 1)
                   ->addFieldToFilter('identifier', array(array('nin' => array('no-route', 'enable-cookies'))));

    }

    /**
     * To option array support for mage 1.4-1.5
     *
     * @param Mage_Cms_Model_Mysql4_Page_Collection | Mage_Cms_Model_Resource_Page_Collection $collection
     *
     * @return array
     */
    public function toOptionIdArray($collection)
    {
        $res                 = array();
        $existingIdentifiers = array();
        foreach ($collection as $item) {
            $identifier = $item->getData('identifier');

            $data['value'] = $identifier;
            $data['label'] = $item->getData('title');

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getData('page_id');
            } else {
                $existingIdentifiers[] = $identifier;
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    protected function _optionToHtml($option, $selected = false)
    {
        if (!Mage::helper('shopgate/config')->getIsMagentoVersionLower15()) {
            return parent::_optionToHtml($option, $selected);
        }

        $selectedHtml = $selected ? ' selected="selected"' : '';
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->getIsRenderToJsTemplate() === true) {
            $selectedHtml .= ' #{option_extra_attr_' . self::calcOptionHash($option['value']) . '}';
        }

        $params = '';
        if (!empty($option['params']) && is_array($option['params'])) {
            foreach ($option['params'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $keyMulti => $valueMulti) {
                        $params .= sprintf(' %s="%s" ', $keyMulti, $valueMulti);
                    }
                } else {
                    $params .= sprintf(' %s="%s" ', $key, $value);
                }
            }
        }

        return sprintf(
            '<option value="%s"%s %s>%s</option>',
            $this->escapeHtml($option['value']),
            $selectedHtml,
            $params,
            $this->escapeHtml($option['label'])
        );
    }

    /**
     * Calculate CRC32 hash for option value.
     * Moved from higher magento versions
     *
     * @param string $optionValue Value of the option
     *
     * @return string
     */
    public function calcOptionHash($optionValue)
    {
        if (!Mage::helper('shopgate/config')->getIsMagentoVersionLower15()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::calcOptionHash($optionValue);
        }

        return sprintf('%u', crc32($this->getId() . $optionValue));
    }
}
