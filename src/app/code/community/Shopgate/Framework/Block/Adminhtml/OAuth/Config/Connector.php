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
class Shopgate_Framework_Block_Adminhtml_OAuth_Config_Connector extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('shopgate/oauth/config/connector.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return url for Shopgate connect button
     *
     * @return string
     */
    public function getConnectUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/shopgate/connect');
    }

    /**
     * Generate Shopgate connect button html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $storeId = $this->_getCurrentStoreId();
        $oauth   = $this->_getHelper()->getOauthToken($storeId);

        if (empty($oauth)) {
            /** @var Mage_Adminhtml_Block_Widget_Button $block */
            $block = $this->getLayout()->createBlock('adminhtml/widget_button')
                          ->setData(
                              array(
                                  'id'      => 'shopgate_connector_button',
                                  'label'   => $this->helper('shopgate')->__('Establish connection'),
                                  'onclick' => 'javascript:window.location = \'' . $this->getConnectUrl(
                                      ) . '\'; return false;'
                              )
                          );
        } else {
            /** @var Mage_Core_Block_Text $block */
            $notice = $this->helper('shopgate')->__('Connection successful');
            $block  = $this->getLayout()->createBlock('core/text', 'oauth-already-set');
            $block->setText("<p style='color: #2075C8;'>{$notice}</p>");
        }

        return $block->toHtml();
    }

    /**
     * Gets store ID of current config scope
     *
     * @return int
     * @throws Mage_Core_Exception
     */
    private function _getCurrentStoreId()
    {
        $storeName = Mage::app()->getRequest()->getParam('store');

        if (!$storeName) {
            $website = Mage::app()->getRequest()->getParam('website');
            return $this->_getHelper()->getStoreIdByWebsite($website);
        }
        return $this->_getHelper()->getStoreIdByStoreCode($storeName);
    }

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getHelper()
    {
        return $this->helper('shopgate/config');
    }
}
