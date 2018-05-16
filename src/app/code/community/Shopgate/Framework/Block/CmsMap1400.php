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
class Shopgate_Framework_Block_CmsMap1400 extends Shopgate_Framework_Block_CmsMap
{
    /**
     * Rows cache
     *
     * @var array|null
     */
    protected $_arrayRowsCache;

    public function __construct()
    {
        $this->_prepareToRender();

        parent::__construct();
    }

    /**
     * @return Mage_Core_Block_Abstract | Shopgate_Framework_Block_Adminhtml_Form_CmsPages
     */
    protected function _getRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = Mage::app()->getLayout()->createBlock(
                'shopgate/adminhtml_form_cmsPages',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_itemRenderer;
    }

    /**
     * Calculate CRC32 hash for option value that allows
     * insertion of "selected=selected" via prototypeJs
     *
     * @param string $id
     * @param string $optionValue Value of the option
     *
     * @return string
     */
    public function calcOptionHash($id, $optionValue)
    {
        return sprintf('%u', crc32($id . $optionValue));
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $hash = $this->calcOptionHash($row->getId(), $row->getData(self::MAGE_PAGE_KEY));
        $row->setData('option_extra_attr_' . $hash, 'selected="selected"');
    }

    /**
     * @inheritdoc
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getData('element');
        if ($element->getData('value') && is_array($element->getData('value'))) {
            foreach ($element->getValue() as $rowId => $row) {
                foreach ($row as $key => $value) {
                    $row[$key] = $this->escapeHtml($value);
                }
                $row['_id']     = $rowId;
                $result[$rowId] = new Varien_Object($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->_arrayRowsCache = $result;

        return $this->_arrayRowsCache;
    }
}
