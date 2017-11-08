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
class Shopgate_Framework_Model_System_Config_Source_Validation_Config extends Mage_Core_Model_Config_Data
{

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getHelper()
    {
        return Mage::helper('shopgate/config');
    }

    /**
     * oAuth config validation
     *
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function save()
    {
        $storeName = Mage::app()->getRequest()->getParam('store');
        $devParam  = Mage::app()->getRequest()->getParam('dev');

        $storeId = $this->_getStoreIdByName($storeName);
        $oauth   = $this->_getHelper()->getOauthToken($storeId);

        if ($devParam) {
            return parent::save();
        }
        //when saving config on website level
        if (!$storeName && !$oauth) {
            $this->setValue(0);
            Mage::getSingleton('core/session')->addWarning(
                Mage::helper('shopgate')->__('Store disabled. Please connect to Shopgate first.')
            );
        } elseif (!$oauth) {
            Mage::throwException(
                Mage::helper('shopgate')->__('You need to connect to Shopgate before saving the configuration')
            );
        }

        return parent::save();
    }

    /**
     * @param null|string $storeCode
     * @return int
     */
    private function _getStoreIdByName($storeCode = null)
    {
        if (!$storeCode) {
            $storeId = $this->getFieldsetDataValue('default_store');
            if (!$storeId) {
                $website = Mage::app()->getRequest()->getParam('website');
                $storeId = $this->_getHelper()->getStoreIdByWebsite($website);
            }
        } else {
            $storeId = $this->_getHelper()->getStoreIdByStoreCode($storeCode);
        }
        return (int)$storeId;
    }

}
