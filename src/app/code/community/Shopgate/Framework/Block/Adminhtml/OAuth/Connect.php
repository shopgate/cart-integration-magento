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
class Shopgate_Framework_Block_Adminhtml_OAuth_Connect extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Add connect Button
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'shopgate';
        $this->_controller = 'adminhtml_oAuth_connect';
        $this->_headerText = Mage::helper('shopgate')->__('Establish a new Connection to Shopgate');
        $this->_mode       = 'add';

        $this->_updateButton('back', 'onclick', "setLocation('{$this->getUrl('*/*/manage')}')");
    }
}

