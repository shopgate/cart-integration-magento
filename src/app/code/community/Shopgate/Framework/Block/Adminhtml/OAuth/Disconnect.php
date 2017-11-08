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
class Shopgate_Framework_Block_Adminhtml_OAuth_Disconnect extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add connect Button
     */
    public function __construct()
    {
        $this->_blockGroup = 'shopgate';
        $this->_controller = 'adminhtml_shopgate_disconnect';
        $this->_headerText = Mage::helper('shopgate')->__('Disconnect Form');

        parent::__construct();
    }

    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     *
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'oauth_disconnect_form',
                'action' => $this->getUrl('*/*/unregister/totally/true'),
                'method' => 'post'
            )
        );

        $form->setData('use_container', true);
        $this->setForm($form);

        $helper   = Mage::helper('shopgate');
        $fieldset = $form->addFieldset(
            'oauth_disconnect',
            array(
                'legend' => $helper->__('Disconnect your shop from shopgate'),
                'class'  => 'fieldset-small'
            )
        );

        $fieldset->addField(
            'submit',
            'submit',
            array(
                'label'    => Mage::helper('shopgate')->__('Disconnect from shopgate'),
                'required' => false,
                'value'    => 'Submit',
            )
        );

        if (Mage::registry('shopgate_oauth_disconnect')) {
            $form->setValues(Mage::registry('shopgate_oauth_disconnect')->getData());
        }

        return parent::_prepareForm();
    }
}
