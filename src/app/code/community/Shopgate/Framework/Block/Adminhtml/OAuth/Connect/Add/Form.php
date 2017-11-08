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
class Shopgate_Framework_Block_Adminhtml_OAuth_Connect_Add_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'oauth_connect_form',
                'action' => $this->getUrl('*/*/register'),
                'method' => 'post'
            )
        );

        $form->setData('use_container', true);
        $this->setForm($form);

        $helper   = Mage::helper('shopgate');
        $fieldset = $form->addFieldset(
            'oauth_connect',
            array(
                'legend' => $helper->__('Connect your shop to shopgate'),
                'class'  => 'fieldset-small'
            )
        );

        $fieldset->addField(
            'note',
            'note',
            array(
                'text' => $helper->__(
                    $this->getLayout()->createBlock('core/template')
                         ->setTemplate('shopgate/oauth/connect/info.phtml')->toHtml()
                ),
            )
        );

        /** @var Mage_Adminhtml_Model_System_Config_Source_Store $storeSource */
        $storeSource = Mage::getModel('adminhtml/system_config_source_store');
        $fieldset->addField(
            'store_view_id',
            'select',
            array(
                'name'     => 'store_view_id',
                'label'    => $helper->__('Store View'),
                'title'    => $helper->__('Store View'),
                'required' => true,
                'values'   => $storeSource->toOptionArray()
            )
        );

        $fieldset->addField(
            'submit-button',
            'submit',
            array(
                'required' => false,
                'value'    => $helper->__('Connect to Shopgate'),
            )
        );

        if (Mage::registry('shopgate_oauth_connect')) {
            $form->setValues(Mage::registry('shopgate_oauth_connect')->getData());
        }

        return parent::_prepareForm();
    }
}
