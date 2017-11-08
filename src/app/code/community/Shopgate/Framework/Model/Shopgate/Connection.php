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
class Shopgate_Framework_Model_Shopgate_Connection extends Mage_Core_Model_Abstract
{
    const DISCONNECT_CONNECTION_ACTION = 'connection_disconnect';
    const CONFIG_VALUE = 'value';
    const STORE_VIEW_RELATED_FIELD = 'related_store_view_ids';

    /**
     * Constructor call to set resource model
     */
    public function __construct()
    {
        parent::__construct();
        $this->_setResourceModel('core/config_data');
    }

    /**
     * Gatering connection details form different config fields
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $config = Mage::getModel('core/config_data')->setData($this->getData());
        $this->setData(array('config' => $config));
        $this->setId($config->getData('config_id'));

        $defaultStoreViewId  = $this->_getDefaultStoreViewId();
        $relatedStoreViewIds = $this->_getRelatedStoreViewIds();

        $this->setDefaultStoreViewId($defaultStoreViewId);
        $this->setRelatedStoreViews($relatedStoreViewIds);
        $this->setStatus(
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, $defaultStoreViewId)
        );
        $this->setBaseCurrency(Mage::getStoreConfig('currency/options/base', $defaultStoreViewId));
        $this->setTaxDefaultCountry(Mage::getStoreConfig('tax/defaults/country', $defaultStoreViewId));
        $this->setMobileAlias(
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ALIAS, $defaultStoreViewId)
        );
        $this->setShopNumber(
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER, $defaultStoreViewId)
        );
    }

    /**
     * Save object data
     *
     * @return Shopgate_Framework_Model_Shopgate_Connection
     */
    public function save()
    {
        return $this;
    }

    /**
     * Helper method to extract the explicit store view of the connection
     *
     * @return int
     */
    protected function _getDefaultStoreViewId()
    {
        $storeViewId = null;
        /** @var Mage_Core_Model_Config_Data $config */
        $config = $this->getData('config');
        if ($config->getScope() === Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES) {
            $collection = $this->getConfigCollection()
                               ->addFieldToFilter('scope', $config->getScope())
                               ->addFieldToFilter('scope_id', $config->getScopeId())
                               ->addFieldToFilter(
                                   'path',
                                   Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE
                               );

            if ($collection->getSize()) {
                $storeViewId = $collection->getFirstItem()->getData(self::CONFIG_VALUE);
            }
        } elseif ($config->getScope() === Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES) {
            $storeViewId = $config->getScopeId();
        }

        if ($storeViewId >= 0) {
            return $storeViewId;
        }

        Mage::throwException(
            'No explicit store view set for the shop with the shop: #'
            . $config->getData(self::CONFIG_VALUE)
        );
    }

    /**
     * Helper method to fetch all storeviewids of any by the connection affected storeview
     *
     * @return array
     */
    protected function _getRelatedStoreViewIds()
    {
        /** @var Mage_Core_Model_Config_Data $config */
        $config = $this->getData('config');
        if (is_null($this->getData(self::STORE_VIEW_RELATED_FIELD))) {
            if ($config->getScope() === Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES) {
                return array($config->getScopeId());
            } elseif ($config->getScope() === Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES) {
                /** @var Mage_Core_Model_Mysql4_Store_Collection $collection */
                $collection = Mage::getModel('core/store')->getCollection()
                                  ->addFieldToFilter('website_id', $config->getScopeId());

                $otherStoreViewsInUse = $this->getConfigCollection()
                                             ->addFieldToFilter(
                                                 'scope',
                                                 Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES
                                             )
                                             ->addFieldToFilter(
                                                 'scope_id',
                                                 array('in' => $collection->getAllIds())
                                             )
                                             ->addFieldToFilter(
                                                 'path',
                                                 Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER
                                             );

                foreach ($otherStoreViewsInUse as $config) {
                    $collection->addFieldToFilter('store_id', array('neq' => $config->getScopeId()));
                }
                $this->setData(self::STORE_VIEW_RELATED_FIELD, $collection->getAllIds());
            }
        }

        return $this->getData(self::STORE_VIEW_RELATED_FIELD);
    }

    /**
     * Sets the active flag in the core/config_data model
     *
     * @return boolean
     */
    public function activate()
    {
        return $this->_saveConfigFlag(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, 1);
    }

    /**
     * Sets the active flag in the core/config_data model
     *
     * @return boolean
     */
    public function deactivate()
    {
        return $this->_saveConfigFlag(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, 0);
    }

    /**
     * Removes all through oauth registration saved config entries for the current model
     *
     * @return Varien_Object
     */
    public function unregister()
    {
        ShopgateLogger::getInstance()->log(
            'Unregister OAuth Shop Connection with shop # ' . $this->getData('shop_number'),
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $result      = Mage::getModel('varien/object');
        $deletedKeys = Mage::getModel('varien/object');

        $errors = array();

        foreach ($this->_getRelatedConfigDataEntries() as $config) {
            $deleted = $config->delete();

            if (!$deleted instanceof Mage_Core_Model_Config_Data) {
                $errors[] =
                    "Could not delete Config Field for '" . $config->getPath() . "' in scope (" . $config->getScope()
                    . "," . $config->getScopeId() . ")";
                continue;
            }

            $deletedKeys->setData($config->getPath(), $deleted);
        }

        $result->setData('errors', $errors);
        $result->setData('deleted_keys', $deletedKeys);

        $cacheResult = Mage::app()->getCacheInstance()->clean(Mage_Core_Model_Config::CACHE_TAG);
        ShopgateLogger::getInstance()->log(
            ' Config cache cleared with result: ' . ($cacheResult ? '[OK]' : '[ERROR]'),
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $this->_notifyBackendAboutConnectionDisconnect();

        return $result;
    }

    /**
     * Internal helper to notify shopgate about connection disbanding
     *
     * @return void
     */
    protected function _notifyBackendAboutConnectionDisconnect()
    {
        $data = array(
            'action'     => self::DISCONNECT_CONNECTION_ACTION,
            'subaction'  => 'connection_disconnect',
            'uid'        => Mage::helper('shopgate/install')->getUid(),
            'shopnumber' => $this->getData('shop_number')
        );

        try {
            $client = new Zend_Http_Client(Shopgate_Framework_Helper_Install::URL_TO_UPDATE_SHOPGATE);
            $client->setParameterPost($data);
            $client->request(Zend_Http_Client::POST);
        } catch (Exception $e) {
            Mage::log(
                "Shopgate_Framework Message: " . Shopgate_Framework_Helper_Install::URL_TO_UPDATE_SHOPGATE
                . " could not be reached.",
                Zend_Log::INFO,
                'shopgate.log'
            );
        }
    }

    /**
     * Helper method to get all config entries for a given shop number
     *
     * @return Mage_Core_Model_Config_Data[]
     */
    protected function _getRelatedConfigDataEntries()
    {
        /** @var Mage_Core_Model_Config_Data $config */
        $config     = $this->getData('config');
        $collection = $this->getConfigCollection()
                           ->addFieldToFilter('path', array('like' => 'shopgate%'))
                           ->addFieldToFilter('scope', array('eq' => $config->getScope()))
                           ->addFieldToFilter('scope_id', array('eq' => $config->getScopeId()));

        if ($config->getScope() == 'websites'
            && Mage::helper('shopgate/config')->getShopConnectionsInWebsiteScope($config->getScopeId())
        ) {
            $collection->addFieldToFilter(
                'path',
                array(
                    'nin' => array(
                        Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE,
                        Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_CUSTOMER_NUMBER,
                        Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_API_KEY
                    )
                )
            );
        }

        return $collection->getItems();
    }

    /**
     * Helper method to save core/config_data fields to the database related to a given shop number
     *
     * @param string $path
     * @param mixed  $value
     *
     * @return boolean
     */
    protected function _saveConfigFlag($path, $value)
    {
        //todo-sg: if config modified is set only in website scope it will not save
        $collection = $this->getConfigCollection()
                           ->addFieldToFilter('scope', $this->getConfig()->getScope())
                           ->addFieldToFilter('scope_id', $this->getConfig()->getScopeId())
                           ->addFieldToFilter('path', $path);

        if ($collection->getSize()) {
            /** @var Mage_Core_Model_Config_Data $config */
            $config           = $collection->getFirstItem();
            $defaultStoreView = $this->_getDefaultStoreViewId();

            if (Mage::getStoreConfig($path, $defaultStoreView) == (string)$value) {
                return false;
            }

            /* check if a sub scope has an alternating value and correct this instead */
            if (Mage::getStoreConfig($path, $defaultStoreView) != $config->getValue()) {
                Mage::getConfig()->saveConfig(
                    $path,
                    $value,
                    Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES,
                    $defaultStoreView
                );

                return true;
            }

            $config->setValue($value)->save();

            return true;
        }

        return false;
    }

    /**
     * Getter method for the oAuth
     *
     * @return string $oAuth
     */
    public function getOauth()
    {
        return $this->getConfig()->getValue();
    }

    /**
     * Loads a shopgate connection by an storeViewId
     *
     * @param int $storeViewId
     *
     * @return Shopgate_Framework_Model_Shopgate_Connection
     */
    public function loadByStoreViewId($storeViewId)
    {
        $collection = $this->getConfigCollection()
                           ->addFieldToFilter(
                               'path',
                               array('eq' => Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                           )
                           ->addFieldToFilter(
                               'scope',
                               array('eq' => Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES)
                           )
                           ->addFieldToFilter('scope_id', array('eq' => $storeViewId));

        if ($collection->count()) {
            return $this->load($collection->getFirstItem()->getId());
        }

        $collection = $this->getConfigCollection()
                           ->addFieldToFilter(
                               'path',
                               array('eq' => Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE)
                           )
                           ->addFieldToFilter(
                               'scope',
                               array('eq' => Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES)
                           )
                           ->addFieldToFilter('value', array('eq' => $storeViewId));

        if ($collection->count()) {
            return $this->load($collection->getFirstItem()->getId());
        }

        return null;
    }

    /**
     * @return Mage_Core_Model_Mysql4_Config_Data_Collection
     */
    public function getConfigCollection()
    {
        return Mage::getModel('core/config_data')->getCollection();
    }
}
