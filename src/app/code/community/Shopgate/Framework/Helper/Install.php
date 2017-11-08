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
class Shopgate_Framework_Helper_Install extends Mage_Core_Helper_Abstract
{
    /**
     * Request url
     */
    const URL_TO_UPDATE_SHOPGATE = 'https://api.shopgate.com/log';

    /**
     * Interface installation action
     */
    const INSTALL_ACTION = 'interface_install';

    /**
     * Type community string
     */
    const TYPE_COMMUNITY = 'Community';

    /**
     * Type enterprise string
     */
    const TYPE_ENTERPRISE = 'Enterprise';

    /**
     * Type magento go string
     */
    const TYPE_GO = 'Go';

    /**
     * Hidden uid field
     */
    const XML_PATH_HIDDEN_UID_FIELD_SHOPGATE = 'shopgate/uid';

    /**
     * @var null
     */
    protected $_orders = null;

    /**
     * @var null
     */
    protected $_date = null;

    /**
     * @var array
     */
    protected $_orderIds = array();

    /**
     * Read connection for DB
     *
     * @var Mage_Core_Model_Resource | null
     */
    protected $_resource = null;

    /**
     * @var null | Varien_Db_Adapter_Interface
     */
    protected $_adapter = null;

    public function updateShopgateSystem($type = self::INSTALL_ACTION)
    {
        $this->_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-1 months'));
        $subshops    = array();

        /** @var $store Mage_Core_Model_Store */
        foreach (Mage::getModel('core/store')->getCollection() as $store) {

            $storeId    = $store->getId();
            $subshops[] = array(
                'uid'                 => $storeId,
                'name'                => $store->getFrontendName(),
                'url'                 => $this->_getConfigData('web/unsecure/base_url', 'stores', $storeId),
                'contact_name'        => $this->_getConfigData('trans_email/ident_general/name', 'stores', $storeId),
                'contact_phone'       => $this->_getConfigData('general/store_information/phone', 'stores', $storeId),
                'contact_email'       => $this->_getConfigData('trans_email/ident_general/email', 'stores', $storeId),
                'stats_items'         => $this->_getItems($storeId),
                'stats_categories'    => $this->_getCategories($store->getRootCategoryId()),
                'stats_orders'        => $this->_getOrders($storeId),
                'stats_acs'           => $this->_calculateAverage($storeId),
                'stats_currency'      => $this->_getConfigData('currency/options/default', 'stores', $storeId),
                'stats_unique_visits' => $this->_getVisitors($storeId),
                'stats_mobile_visits' => 0
            );
        }

        $data = array(
            'action'             => $type,
            'uid'                => $this->_getUid(),
            'plugin_version'     => $this->_getPluginVersion(),
            'shopping_system_id' => $this->_getShopSystemType(),
            'subshops'           => $subshops
        );

        Mage::getConfig()->saveConfig(self::XML_PATH_HIDDEN_UID_FIELD_SHOPGATE, $data['uid']);

        try {
            $client = new Zend_Http_Client(self::URL_TO_UPDATE_SHOPGATE);
            $client->setParameterPost($data);
            $client->request(Zend_Http_Client::POST);
        } catch (Exception $e) {
            Mage::log(
                "Shopgate_Framework Message: " . self::URL_TO_UPDATE_SHOPGATE . " could not be reached.",
                Zend_Log::INFO,
                'shopgate.log'
            );
        }
    }

    /**
     * getStoreConfig not working in installer, so need to read from db
     *
     * @param string $path
     * @param string $scope
     * @param int    $scopeId
     *
     * @return mixed
     */
    protected function _getConfigData($path, $scope = 'default', $scopeId = 0)
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getSingleton('core/resource');
            $this->_adapter  = $this->_resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
        }

        $table = $this->_resource->getTableName('core/config_data');

        $select = $this->_adapter->select()
                                 ->from($table)
                                 ->columns('value')
                                 ->where(
                                     'path = "' . $path . '" and scope="' . $scope . '" and scope_id="' . $scopeId . '"'
                                 );

        $result = $this->_adapter->fetchRow($select);

        if (!$result['value'] && $scope != 'default') {
            $result['value'] = $this->_getConfigData($path);
        }

        return $result['value'];
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    protected function _getPluginVersion()
    {
        return (string)Mage::getConfig()->getModuleConfig('Shopgate_Framework')->version;
    }

    /**
     * Get shop system number ( internal usage )
     *
     * @return int|null
     */
    protected function _getShopSystemType()
    {
        switch ($this->_getEdition()) {
            case self::TYPE_COMMUNITY:
                $type = 76;
                break;
            case self::TYPE_ENTERPRISE:
                $type = 228;
                break;
            case self::TYPE_GO:
                $type = 229;
                break;
            default:
                $type = null;
        }

        return $type;
    }

    /**
     * Return magento type (edition)
     *
     * @return string
     */
    protected function _getEdition()
    {
        $edition = self::TYPE_COMMUNITY;

        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            $dir = Mage::getBaseDir('code') . DS . 'core' . DS . self::TYPE_ENTERPRISE;
            if (file_exists($dir)) {
                $edition = self::TYPE_ENTERPRISE;
            }
        }

        return $edition;
    }

    /**
     * Get product count unfiltered
     *
     * @param int $storeId
     *
     * @return int
     */
    protected function _getItems($storeId)
    {
        return Mage::getModel('catalog/product')
                   ->getCollection()
                   ->addStoreFilter($storeId)
                   ->addAttributeToSelect('id')
                   ->getSize();
    }

    /**
     * Get categories count
     *
     * @param int $rootCatId
     *
     * @return int
     */
    protected function _getCategories($rootCatId)
    {
        return Mage::getResourceModel('catalog/category')->getChildrenCount($rootCatId);
    }

    /**
     * Get amount of orders
     *
     * @param int $storeId
     *
     * @return int|null
     */
    protected function _getOrders($storeId)
    {
        /** @var Mage_Eav_Model_Entity_Collection_Abstract $collection */
        $collection = Mage::getResourceModel('sales/order_collection')
                          ->addFieldToFilter('store_id', $storeId)
                          ->addFieldToFilter(
                              'created_at',
                              array(
                                  array(
                                      'gteq' => $this->_date
                                  )
                              )
                          )->addAttributeToSelect('grand_total');

        return $collection->getSize();
    }

    /**
     * @param int $storeId
     *
     * @return float result
     */
    protected function _calculateAverage($storeId)
    {
        $collection = Mage::getResourceModel('sales/order_collection')
                          ->addFieldToFilter('store_id', $storeId)
                          ->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_COMPLETE)
                          ->addAttributeToSelect('grand_total');
        $collection->getSelect()->from(null, array('average' => 'AVG(grand_total)'));
        $result = $this->_adapter->fetchRow($collection->getSelect()->assemble());
        if (!$result['average']) {
            $result['average'] = 0;
        }

        return round($result['average'], 2);
    }

    /**
     * Get visitor data unfiltered
     *
     * @param int $storeId
     *
     * @return int
     */
    protected function _getVisitors($storeId)
    {
        $result = Mage::getResourceModel('log/aggregation')->getCounts($this->_date, date('Y-m-d H:i:s'), $storeId);
        if (!$result['visitors']) {
            $result['visitors'] = 0;
        }

        return $result['visitors'];
    }

    /**
     * Get uid to clarify identification in home system
     *
     * @return string
     */
    protected function _getUid()
    {
        $key  = (string)Mage::getConfig()->getNode('global/crypt/key');
        $salt = $this->_getShopSystemType();
        if (!$salt) {
            $salt = '5h0p6473.c0m';
        }

        return md5($key . $salt);
    }

    /**
     * Public wrapper method for _getUid()
     *
     * @return string
     */
    public function getUid()
    {
        return $this->_getUid();
    }
}
