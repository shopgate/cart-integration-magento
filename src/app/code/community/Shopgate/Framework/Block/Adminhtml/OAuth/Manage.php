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
class Shopgate_Framework_Block_Adminhtml_OAuth_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Manage connections grid
     */
    public function __construct()
    {
        parent::__construct();

        $helper            = Mage::helper('shopgate');
        $this->_blockGroup = 'shopgate';
        $this->_controller = 'adminhtml_oAuth_manage';
        $this->_headerText = $helper->__('Manage Connections');

        $this->_removeButton('add');

        $this->addButton(
            'new_connection',
            array(
                'label'   => $helper->__('Create new connection'),
                'onclick' => "setLocation('{$this->getUrl('*/*/connect')}')",
                'class'   => 'add',
                'area'    => 'header',
            )
        );
    }
}
