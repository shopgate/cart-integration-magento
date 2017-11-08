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
class Shopgate_Framework_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * Const for community
     */
    const COMMUNITY_EDITION = 'Community';

    /**
     * Const for enterprise
     */
    const ENTERPRISE_EDITION = 'Enterprise';

    /**
     * @var string
     */
    protected $_magentoVersion19 = '';

    /**
     * When native COD & Bank payments
     * were introduced
     *
     * @var string
     */
    protected $_magentoVersion1700 = '';

    /**
     * When PayPal UI was updated
     *
     * @var string
     */
    protected $_magentoVersion1701 = '';

    /**
     * @var string
     */
    protected $_magentoVersion16;

    /**
     * @var string
     */
    protected $_magentoVersion15 = '';

    /**
     * @var string
     */
    protected $_magentoVersion1410 = '';

    /**
     * @var Shopgate_Framework_Model_Config
     */
    protected $_config = null;

    /**
     * Construct for helper
     */
    public function __construct()
    {
        $this->_magentoVersion19   = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.14' : '1.9';
        $this->_magentoVersion1701 = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.12.0.2' : '1.7.0.1';
        $this->_magentoVersion1700 = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.12.0.0' : '1.7.0.0';
        $this->_magentoVersion16   = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.11.0.0' : '1.6.0.0';
        $this->_magentoVersion15   = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.9.1.0' : '1.5';
        $this->_magentoVersion1410 = ($this->getEdition() === self::ENTERPRISE_EDITION) ? '1.9.0.0' : '1.4.1.0';
    }

    /**
     * Get edition of magento
     *
     * @return string
     */
    public function getEdition()
    {
        $edition = self::COMMUNITY_EDITION;

        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            $dir = Mage::getBaseDir('code') . DS . 'core' . DS . self::ENTERPRISE_EDITION;
            if (file_exists($dir)) {
                $edition = self::ENTERPRISE_EDITION;
            }
        }

        return $edition;
    }

    /**
     * @param string $version - magento version e.g "1.9.1.1"
     *
     * @return bool
     */
    public function getIsMagentoVersionLower($version)
    {
        return version_compare(Mage::getVersion(), $version, "<");
    }

    /**
     * @return bool
     */
    public function getIsMagentoVersionLower19()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion19);
    }

    /**
     * @return bool
     */
    public function getIsMagentoVersionLower1700()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion1700);
    }

    /**
     * @return bool
     */
    public function getIsMagentoVersionLower1701()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion1701);
    }

    /**
     * @return mixed
     */
    public function getIsMagentoVersionLower16()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion16);
    }

    /**
     * @return mixed
     */
    public function getIsMagentoVersionLower15()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion15);
    }

    /**
     * @return mixed
     */
    public function getIsMagentoVersionLower1410()
    {
        return $this->getIsMagentoVersionLower($this->_magentoVersion1410);
    }

    /**
     * @param int $storeId
     *
     * @return Shopgate_Framework_Model_Config
     */
    public function getConfig($storeId = null)
    {
        if (!$this->_config) {
            $this->_config = Mage::getModel('shopgate/config');
            $this->_config->loadConfig($storeId);
        }

        return $this->_config;
    }

    /**
     * Checks if a shopnumber is already registered or a storeview has already a shopnumber set explicit
     *
     * @param int $shopnumber
     * @param int $storeViewId
     *
     * @return string
     */
    public function isOAuthShopAlreadyRegistered($shopnumber, $storeViewId)
    {
        /* has shopnumber defined in same website scope */
        if (Mage::getModel('core/config_data')->getCollection()
                ->addFieldToFilter('path', Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                ->addFieldToFilter('value', $shopnumber)
                ->count()
        ) {
            return true;
        }

        /* a shopnumber is set on store view scope with the same scope_id as the base store view for the new shopnumber */
        if (Mage::getModel('core/config_data')->getCollection()
                ->addFieldToFilter('path', Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                ->addFieldToFilter('scope', 'stores')
                ->addFieldToFilter('scope_id', $storeViewId)
                ->addFieldToFilter('value', array('nin' => array('', null)))
                ->count()
        ) {
            return true;
        }

        /* a shopnumber has a default store view set exactly like the base store view for the new shopnumber */
        $resource          = Mage::getSingleton('core/resource');
        $table_config_data = $resource->getTableName('core/config_data');
        $collection        = Mage::getModel('core/config_data')->getCollection()
                                 ->addFieldToFilter(
                                     'main_table.path',
                                     Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER
                                 )
                                 ->addFieldToFilter('main_table.scope', 'websites')
                                 ->addFieldToFilter('main_table.value', array('nin' => array('', null)))
                                 ->addFieldToFilter(
                                     'dsv.path',
                                     Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE
                                 )
                                 ->getSelect()
                                 ->joinInner(
                                     array('dsv' => $table_config_data),
                                     'dsv.scope = main_table.scope AND dsv.scope_id = main_table.scope_id',
                                     array('default_store_view' => 'value')
                                 )->query()
                                 ->fetchAll();

        foreach ($collection as $item) {
            if (isset($item['default_store_view'])
                && $item['default_store_view'] == $storeViewId
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches any shopnumber definition in a given website scope
     *
     * @param int $websiteId
     *
     * @return int
     */
    public function getShopConnectionsInWebsiteScope($websiteId)
    {
        $relatedStoreViews = Mage::getModel('core/store')
                                 ->getCollection()
                                 ->addFieldToFilter('code', array('neq' => 'admin'))
                                 ->addFieldToFilter('website_id', array('eq' => $websiteId))
                                 ->getAllIds();

        /** @var Mage_Core_Model_Mysql4_Config_Data_Collection $shopNumbers */
        $shopNumbers = Mage::getModel('core/config_data')
                           ->getCollection()
                           ->addFieldToFilter('path', Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                           ->addFieldToFilter('scope', 'stores')
                           ->addFieldToFilter('scope_id', array('in' => $relatedStoreViews))
                           ->addFieldToFilter('value', array('neq' => array('')));

        return $shopNumbers->getSize();
    }

    /**
     * Fetches any defined shopgate connection
     *
     * @return int
     */
    public function getShopgateConnections()
    {
        return Mage::getModel('core/config_data')
                   ->getCollection()
                   ->addFieldToFilter('path', Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                   ->addFieldToFilter('value', array('neq' => array('')));
    }

    /**
     * Checks if any shopgate connections persist already
     *
     * @return int
     */
    public function hasShopgateConnections()
    {
        return $this->getShopgateConnections()->getSize();
    }

    /**
     * Gets the system_config/edit url with the proper scope set
     *
     * @param int $connectionId
     *
     * @return string
     */
    public function getConfigureUrl($connectionId)
    {
        /** @var Mage_Core_Model_Config_Data $config */
        $config  = Mage::getModel('shopgate/shopgate_connection')->load($connectionId)->getData('config');
        $scope   = $config->getScope();
        $scopeId = $config->getScopeId();

        $scopeModelType = substr($scope, 0, -1);
        /** @var Mage_Core_Model_Store | Mage_Core_Model_Website $scopeEntity */
        $scopeEntity = Mage::getModel('core/' . $scopeModelType)->load($scopeId);
        $scopeCode   = $scopeEntity->getCode();

        return Mage::helper('adminhtml')
                   ->getUrl('*/system_config/edit/section/shopgate/' . $scopeModelType . '/' . $scopeCode);
    }

    /**
     * Provide website code and get Shopgate
     * store id OR magento's default store id
     *
     * @param $websiteCode
     *
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getStoreIdByWebsite($websiteCode)
    {
        $storeId = Mage::app()->getWebsite($websiteCode)->getConfig('shopgate/option/default_store');
        //use magento default store
        if (!$storeId) {
            $group = Mage::app()->getWebsite($websiteCode)->getDefaultGroup();
            if ($group) {
                $storeId = $group->getDefaultStoreId();
            } else {
                $storeId = null;
            }
        }

        return $storeId;
    }

    /**
     * Retrieves store id based on store code
     *
     * @param $storeCode
     *
     * @return int
     */
    public function getStoreIdByStoreCode($storeCode)
    {
        $storeId = Mage::getModel('core/store')->loadConfig($storeCode)->getId();
        if (!$storeId) {
            $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
        }

        return $storeId;
    }

    /**
     * Retrieves the oauth token of the store
     *
     * @param null $storeId
     *
     * @return mixed - oauth if it exists
     */
    public function getOauthToken($storeId = null)
    {
        return Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_OAUTH_ACCESS_TOKEN, $storeId);
    }
}
