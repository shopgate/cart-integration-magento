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
class Shopgate_Framework_Block_CmsMap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /** @var Mage_Core_Block_Abstract | Shopgate_Framework_Block_Adminhtml_Form_CmsPages */
    protected $_itemRenderer;
    const MAGE_PAGE_KEY = 'cms_page';
    const SG_PAGE_KEY = 'shopgate_page_key';

    /**
     * @inheritdoc
     */
    public function _prepareToRender()
    {
        $helper = Mage::helper('shopgate');
        $this->addColumn(
            self::MAGE_PAGE_KEY,
            array(
                'label'    => $helper->__('Cms Page'),
                'renderer' => $this->_getRenderer(),
            )
        );
        $this->addColumn(
            self::SG_PAGE_KEY,
            array(
                'label' => $helper->__('Shopgate URL key'),
                'style' => 'width:150px',
            )
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = $helper->__('Add Page');
    }

    /**
     * @return Mage_Core_Block_Abstract | Shopgate_Framework_Block_Adminhtml_Form_CmsPages
     */
    protected function _getRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'shopgate/adminhtml_form_cmsPages',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_itemRenderer;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()->calcOptionHash($row->getData(self::MAGE_PAGE_KEY)),
            'selected="selected"'
        );
    }
}
