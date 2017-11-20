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

/** @noinspection PhpIncludeInspection */
include_once Mage::getBaseDir('lib') . '/Shopgate/cart-integration-sdk/shopgate.php';

/**
 * @method bool getMarkUnblockedOrdersAsPaid()
 * @method bool getShowOutOfStockProducts()
 * @method int getExportVariationImage()
 * @method bool getUseRootPrice()
 * @method int getExportVariationDescription()
 * @method int getDescriptionType()
 * @method string getDescriptionAttrCode()
 * @method bool getSendNewOrderMail()
 * @method int getDefaultItemRowOptionCount()
 * @method bool getIsExportStores()
 * @method string getHiddenCategoriesIds()
 * @method string getHtaccessUser()
 * @method string getHtaccessPassword()
 * @method bool getExportBundlesBeta()
 * @method bool getExportSubsWithParent()
 */
class Shopgate_Framework_Model_Config extends ShopgateConfig
{

    const REDIRECTTYPE_HTTP = "http";
    const REDIRECTTYPE_JAVASCRIPT = "js";
    const XML_PATH_SHOPGATE_ACTIVE = "shopgate/option/active";
    const XML_PATH_SHOPGATE_ALIAS = "shopgate/mobile/alias";
    const XML_PATH_SHOPGATE_ALWAYS_USE_SSL = "shopgate/hidden/always_use_ssl";
    const XML_PATH_SHOPGATE_OAUTH_ACCESS_TOKEN = "shopgate/hidden/oauth_access_token";
    const XML_PATH_SHOPGATE_API_KEY = "shopgate/option/api_key";
    const XML_PATH_SHOPGATE_API_URL = "shopgate/debug/api_url";
    const XML_PATH_SHOPGATE_CNAME = "shopgate/mobile/cname";
    const XML_PATH_SHOPGATE_CUSTOMER_NUMBER = "shopgate/option/customer_number";
    const XML_PATH_SHOPGATE_DEBUG_HTPASS = "shopgate/debug/htaccess_pass";
    const XML_PATH_SHOPGATE_DEBUG_HTUSER = "shopgate/debug/htaccess_user";
    const XML_PATH_SHOPGATE_DESCRIPTION_ATTR_CODE = "shopgate/export/description_attr_code";
    const XML_PATH_SHOPGATE_EAN_ATTR_CODE = "shopgate/export/ean_attr_code";
    const XML_PATH_SHOPGATE_ENABLE_DEFAULT_REDIRECT = "shopgate/mobile/enable_default_redirect";
    const XML_PATH_SHOPGATE_DISABLE_REDIRECT_ROUTES = "shopgate/mobile/disable_redirect_routes";
    const XML_PATH_SHOPGATE_DISABLE_REDIRECT_CONTROLLERS = "shopgate/mobile/disable_redirect_controllers";
    const XML_PATH_SHOPGATE_DISABLE_REDIRECT_PRODUCTS = "shopgate/mobile/disable_redirect_products";
    const XML_PATH_SHOPGATE_DISABLE_REDIRECT_CATEGORIES = "shopgate/mobile/disable_redirect_categories";
    const XML_PATH_SHOPGATE_CMS_MAP = 'shopgate/mobile/cms_map';
    const XML_PATH_SHOPGATE_EXPORT_AVAILABLE_TEXT_ATTRIBUTE_CODE = "shopgate/export/available_text_attribute_code";
    const XML_PATH_SHOPGATE_EXPORT_BUNDLES_BETA = "shopgate/export/bundles_beta";
    const XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_NL2BR = "shopgate/export/description_nl2br";
    const XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_TYPE = "shopgate/export/description";
    const XML_PATH_SHOPGATE_EXPORT_EXCLUDED_IMAGES = "shopgate/export/export_excluded_image";
    const XML_PATH_SHOPGATE_EXPORT_LOWRES_IMAGES = "shopgate/export/export_lowres_image";
    const XML_PATH_SHOPGATE_EXPORT_FIRST_PRODUCT_IMAGE = "shopgate/export/first_export_image";
    const XML_PATH_SHOPGATE_EXPORT_FILTER_PROPERTIES = "shopgate/export/filter_properties";
    const XML_PATH_SHOPGATE_EXPORT_FORCE_PROPERTY_EXPORT = "shopgate/export/force_property_export";
    const XML_PATH_SHOPGATE_EXPORT_HIDDEN_CATEGORIES = "shopgate/export/hidden_categories_ids";
    const XML_PATH_SHOPGATE_EXPORT_NAVIGATION_CATEGORIES_ONLY = "shopgate/export/export_navigation_categories_only";
    const XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES = "shopgate/export/is_export_stores";
    const XML_PATH_SHOPGATE_EXPORT_ITEM_SORT = "shopgate/export/item_sort";
    const XML_PATH_SHOPGATE_EXPORT_PRODUCT_TYPES = "shopgate/export/product_types";
    const XML_PATH_SHOPGATE_EXPORT_PARENT_PRODUCT_NAME = "shopgate/export/product_names";
    const XML_PATH_SHOPGATE_EXPORT_STORES = "shopgate/export/export_stores";
    const XML_PATH_SHOPGATE_EXPORT_USE_ROOT_PRICES = "shopgate/export/use_root_price";
    const XML_PATH_SHOPGATE_EXPORT_VARIATION_DESCRIPTION = "shopgate/export/export_variation_description";
    const XML_PATH_SHOPGATE_EXPORT_VARIATION_IMAGE = "shopgate/export/export_variation_image";
    const XML_PATH_SHOPGATE_EXPORT_USE_PRICE_INDEX_ON_EXPORT = "shopgate/export/use_price_index_on_export";
    const XML_PATH_SHOPGATE_MOBILE_HEADER_PARENT = "shopgate/hidden/mobile_header_parent";
    const XML_PATH_SHOPGATE_MOBILE_HEADER_PREPEND = "shopgate/hidden/mobile_header_prepend";
    const XML_PATH_SHOPGATE_ORDER_CONFIRM_SHIPPING_ON_COMPLETE = "shopgate/orders/confirm_shipping_on_complete";
    const XML_PATH_SHOPGATE_ORDER_MARK_UNBLOCKED_AS_PAID = "shopgate/orders/mark_unblocked_orders_as_paid";
    const XML_PATH_SHOPGATE_ORDER_SEND_NEW_ORDER_MAIL = "shopgate/orders/send_new_order_mail";
    const XML_PATH_SHOPGATE_ORDER_CUSTOMFIELDS_TO_STATUSHISTORY = "shopgate/orders/write_customfields_into_statushistory";
    const XML_PATH_SHOPGATE_ORDER_APPLY_CART_RULES = "shopgate/orders/apply_cart_rules";
    const XML_PATH_SHOPGATE_SHOW_CARTRULE_LABEL = "shopgate/orders/show_cartrule_label";
    const XML_PATH_SHOPGATE_ORDER_ADD_ONLY_SIMPLE = "shopgate/orders/add_only_simple";
    const XML_PATH_SHOPGATE_ORDER_FORCE_SG_SHIPPING = "shopgate/orders/force_shopgate_fix";
    const XML_PATH_SHOPGATE_ORDER_USE_SHOPGATE_PRICES = "shopgate/orders/use_shopgate_prices";
    const XML_PATH_SHOPGATE_REDIRECT_TYPE = "shopgate/mobile/redirect_type";
    const XML_PATH_SHOPGATE_SERVER = "shopgate/debug/server";
    const XML_PATH_SHOPGATE_SHOP_NUMBER = "shopgate/option/shop_number";
    const XML_PATH_SHOPGATE_SHOP_ACTIVE = "shopgate/hidden/shop_is_active";
    const XML_PATH_SHOPGATE_FIX_ONE_CENT_BUG = "shopgate/hidden/fix_one_cent_bug";
    const XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE = "shopgate/option/default_store";
    const XML_PATH_SHOPGATE_UPC_ATTR_CODE = "shopgate/export/upc_attr_code";
    const XML_PATH_SHOPGATE_WEIGHT_UNIT = "shopgate/export/weight_unit";
    const XML_PATH_SHOPGATE_NOT_PREDEFINED_CONFIG = "shopgate/not_predefined_config/";
    const XML_PATH_SHOPGATE_NET_MARKET_COUNTRIES = "shopgate/export/net_market_countries";

    /**
     * @var string key - affiliate key
     */
    private $affiliateParameters;

    /**
     * blacklist for config keys
     */
    protected $_blacklistedConfigKeys = array("is_active");

    /**
     * store view id
     *
     * @var integer
     */
    protected $_storeViewId;

    /**
     * Fix one cent bug
     *
     * @var integer|bool
     */
    protected $fix_one_cent_bug;

    /**
     * @param $value
     */
    public function setFixOneCentBug($value)
    {
        $this->fix_one_cent_bug = $value;
    }

    /**
     * @return bool|int
     */
    public function getFixOneCentBug()
    {
        return $this->fix_one_cent_bug;
    }

    /**
     * Sets the given fields with the given values in ShopgateConfig Model
     *
     * @param array $settings
     */
    public function load(array $settings = null)
    {
        Mage::dispatchEvent('shopgate_load_config_before', array('config' => $this, 'settings' => $settings));

        if (is_null($settings)) {
            $this->loadConfig(Mage::app()->getStore()->getId());
        }

        $classVars    = array_keys(get_class_vars(get_class($this)));
        $classMethods = get_class_methods(get_class($this));

        foreach ($settings as $name => $value) {
            if (in_array($name, $this->_blacklistedConfigKeys)) {
                continue;
            }

            if (in_array($name, $classVars)) {
                $setter = $this->_getSetterMethod($name);
                if (in_array($setter, $classMethods)) {
                    $this->{$setter}($this->_castToType($value, $name));
                } else {
                    Mage::logException(
                        new Exception(
                            'The evaluated setter method "' . $setter . '" is not available in class ' . __CLASS__
                        )
                    );
                }
            } else {
                if (array_key_exists($name, $this->additionalSettings)) {
                    $this->additionalSettings[$name] = $value;
                } else {
                    Mage::logException(
                        new Exception(
                            'The given setting property "' . $name . '" is not available in class ' . __CLASS__
                        )
                    );
                }
            }
        }

        Mage::dispatchEvent('shopgate_load_config_after', array('config' => $this, 'settings' => $settings));
    }

    /**
     * Load general information and values
     *
     * @param null $storeId
     */
    public function loadConfig($storeId = null)
    {
        $this->plugin_name = 'magento';

        if ($storeId == null) {
            $storeId = $this->_getStoreId();
        }
        $this->setStoreViewId($storeId);
        $this->loadArray($this->toArray());
        $this->_setExportTmpAndLogSettings();
        $this->_setGeneralPermissions();
    }

    /**
     * Fetches the store id
     *
     * @throws ShopgateLibraryException
     * @return int
     */
    protected function _getStoreId()
    {

        if (Mage::helper("shopgate")->isShopgateApiRequest()
            && Mage::app()->getRequest()->getParam("shop_number")
        ) {
            // If  Shopnumber ist given in the request find the StoreID by shop_number
            // Check if several entries with the same shopnumber are available
            $shopNumber    = Mage::app()->getRequest()->getParam('shop_number');
            $storeIdByPath = Mage::getConfig()->getStoresConfigByPath(
                self::XML_PATH_SHOPGATE_SHOP_NUMBER,
                array($shopNumber)
            );
            $storeId       = null;

            if (!empty($storeIdByPath)) {
                $storeIds     = array_keys($storeIdByPath);
                $storeId      = array_shift($storeIds);
                $defaultStore = Mage::helper('shopgate')->getStoreIdByShopNumber($shopNumber);
                if (!empty($defaultStore)) {
                    $storeId = $defaultStore;
                }
            }

            if (!$storeId) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                    "Cannot find config for shopnumber!",
                    true
                );
            }
            $this->setShopNumber($shopNumber);
            Mage::app()->setCurrentStore($storeId);
        }

        return Mage::app()->getStore()->getId();
    }

    /**
     * Overwrite to array function to remove all the useless getter and setter which should work dynamically
     * based on Mage::getStoreConfig() which is already cached
     *
     * @return array
     */
    public function toArray()
    {
        $result    = array();
        $classVars = get_class_vars(get_class($this));
        foreach ($classVars as $configKey => $value) {
            $method = 'get' . uc_words($configKey, '');
            if (method_exists($this, $method)) {
                $result[$configKey] = $this->$method();
            }
        }

        $mapping = array_merge(
            self::getConfigMapping(),
            self::getHiddenConfigMapping(),
            self::getNotPredefinedPropertiesMapping()
        );
        foreach ($mapping as $key => $path) {
            $value = Mage::getStoreConfig($path, $this->getStoreViewId());
            if (!is_null($value)) {
                $result[$key] = $this->_castToType($value, $key);
            }
        }

        return $result;
    }

    /**
     * Wrapper method for static mapping array to evaluate config path against API called key
     *
     * @return array
     */
    public static function getConfigMapping()
    {
        return array(
            "is_active"                    => self::XML_PATH_SHOPGATE_ACTIVE,
            "apikey"                       => self::XML_PATH_SHOPGATE_API_KEY,
            "shop_number"                  => self::XML_PATH_SHOPGATE_SHOP_NUMBER,
            "customer_number"              => self::XML_PATH_SHOPGATE_CUSTOMER_NUMBER,
            "alias"                        => self::XML_PATH_SHOPGATE_ALIAS,
            "cname"                        => self::XML_PATH_SHOPGATE_CNAME,
            "redirect_type"                => self::XML_PATH_SHOPGATE_REDIRECT_TYPE,
            "enable_default_redirect"      => self::XML_PATH_SHOPGATE_ENABLE_DEFAULT_REDIRECT,
            "disable_redirect_routes"      => self::XML_PATH_SHOPGATE_DISABLE_REDIRECT_ROUTES,
            "disable_redirect_controllers" => self::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CONTROLLERS,
            "disable_redirect_products"    => self::XML_PATH_SHOPGATE_DISABLE_REDIRECT_PRODUCTS,
            "disable_redirect_categories"  => self::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CATEGORIES,
            "server"                       => self::XML_PATH_SHOPGATE_SERVER,
            "api_url"                      => self::XML_PATH_SHOPGATE_API_URL,
            "export_product_types"         => self::XML_PATH_SHOPGATE_EXPORT_PRODUCT_TYPES,
            "export_variation_description"
                                           => self::XML_PATH_SHOPGATE_EXPORT_VARIATION_DESCRIPTION,
            "export_variation_image"       => self::XML_PATH_SHOPGATE_EXPORT_VARIATION_IMAGE,
            "description_type"             => self::XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_TYPE,
            "convert_description"          => self::XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_NL2BR,
            "use_root_price"               => self::XML_PATH_SHOPGATE_EXPORT_USE_ROOT_PRICES,
            "weight_unit"                  => self::XML_PATH_SHOPGATE_WEIGHT_UNIT,
            "ean_attr_code"                => self::XML_PATH_SHOPGATE_EAN_ATTR_CODE,
            "description_attr_code"        => self::XML_PATH_SHOPGATE_DESCRIPTION_ATTR_CODE,
            "is_export_stores"             => self::XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES,
            "export_stores"                => self::XML_PATH_SHOPGATE_EXPORT_STORES,
            "item_sort"                    => self::XML_PATH_SHOPGATE_EXPORT_ITEM_SORT,
            "filter_properties"            => self::XML_PATH_SHOPGATE_EXPORT_FILTER_PROPERTIES,
            "hidden_categories_ids"        => self::XML_PATH_SHOPGATE_EXPORT_HIDDEN_CATEGORIES,
            "use_price_index_on_export"    => self::XML_PATH_SHOPGATE_EXPORT_USE_PRICE_INDEX_ON_EXPORT,
            "mark_unblocked_orders_as_paid"
                                           => self::XML_PATH_SHOPGATE_ORDER_MARK_UNBLOCKED_AS_PAID,
            "send_new_order_mail"          => self::XML_PATH_SHOPGATE_ORDER_SEND_NEW_ORDER_MAIL,
            "confirm_shipping_on_complete"
                                           => self::XML_PATH_SHOPGATE_ORDER_CONFIRM_SHIPPING_ON_COMPLETE,
            "htaccess_user"                => self::XML_PATH_SHOPGATE_DEBUG_HTUSER,
            "htaccess_password"            => self::XML_PATH_SHOPGATE_DEBUG_HTPASS,
            "currency"                     => Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT,
            "show_out_of_stock_products"
                                           => Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK,
            "available_text_attribute_code"
                                           => self::XML_PATH_SHOPGATE_EXPORT_AVAILABLE_TEXT_ATTRIBUTE_CODE,
            "apply_cart_rules"             => self::XML_PATH_SHOPGATE_ORDER_APPLY_CART_RULES,
            "add_only_simple"              => self::XML_PATH_SHOPGATE_ORDER_ADD_ONLY_SIMPLE,
            "fix_one_cent_bug"             => self::XML_PATH_SHOPGATE_FIX_ONE_CENT_BUG,
            "use_shopgate_prices"          => self::XML_PATH_SHOPGATE_ORDER_USE_SHOPGATE_PRICES,
            "upc_attr_code"                => self::XML_PATH_SHOPGATE_UPC_ATTR_CODE
        );
    }

    /**
     * Wrapper method for static mapping array of non backend configurable values
     *
     * @return array
     */
    public static function getHiddenConfigMapping()
    {
        return array(
            "shop_is_active"        => self::XML_PATH_SHOPGATE_SHOP_ACTIVE,
            "oauth_access_token"    => self::XML_PATH_SHOPGATE_OAUTH_ACCESS_TOKEN,
            "always_use_ssl"        => self::XML_PATH_SHOPGATE_ALWAYS_USE_SSL,
            "mobile_header_parent"  => self::XML_PATH_SHOPGATE_MOBILE_HEADER_PARENT,
            "mobile_header_prepend" => self::XML_PATH_SHOPGATE_MOBILE_HEADER_PREPEND,
            "fix_one_cent_bug"      => self::XML_PATH_SHOPGATE_FIX_ONE_CENT_BUG
        );
    }

    /**
     * Wrapper method for all properties which are mapped by a generic rule to the magento database
     */
    public static function getNotPredefinedPropertiesMapping()
    {
        $properties = array_keys(get_class_vars(__CLASS__));

        $properties =
            array_diff($properties, array_keys(self::getConfigMapping()), array_keys(self::getHiddenConfigMapping()));

        $mapping = array();
        foreach ($properties as $property) {
            $mapping[$property] = self::XML_PATH_SHOPGATE_NOT_PREDEFINED_CONFIG . $property;
        }

        return $mapping;
    }

    /**
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param int $value
     */
    public function setStoreViewId($value)
    {
        $this->_storeViewId = $value;
    }

    /**
     * Setting basic permissions
     */
    protected function _setGeneralPermissions()
    {
        $this->setEnableAddOrder(true);
        $this->setEnableCheckCart(true);
        $this->setEnableCheckStock(true);
        $this->setEnableClearLogfile(true);
        $this->setEnableClearCache(true);
        $this->setEnableCron(true);
        $this->setEnableGetCategories(true);
        $this->setEnableGetCategoriesCsv(true);
        $this->setEnableGetCustomer(true);
        $this->setEnableGetDebugInfo(true);
        $this->setEnableGetItems(true);
        $this->setEnableGetItemsCsv(true);
        $this->setEnableGetLogFile(true);
        $this->setEnableGetOrders(true);
        $this->setEnableGetReviews(true);
        $this->setEnableGetReviewsCsv(true);
        $this->setEnableGetSettings(true);
        $this->setEnablePing(true);
        $this->setEnableReceiveAuthorization(true);
        $this->setEnableRedeemCoupons(false);
        $this->setEnableRedirectKeywordUpdate(true);
        $this->setEnableRegisterCustomer(true);
        $this->setEnableUpdateOrder(true);
        $this->setSmaAuthServiceClassName(ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH);
        $this->setSupportedFieldsCheckCart(
            array(
                "internal_cart_info",
                "currency",
                "customer",
                "shipping_methods",
                "payment_methods",
                "items",
                "external_coupons"
            )
        );
        $this->setSupportedFieldsGetSettings(
            array(
                "customer_groups",
                "allowed_shipping_countries",
                "allowed_address_countries",
                "tax"
            )
        );
    }

    /**
     * Setup export, log and tmp folder and check if need to create them
     */
    protected function _setExportTmpAndLogSettings()
    {
        $this->setExportFolderPath(
            Mage::getBaseDir("export") . DS . "shopgate" . DS . $this->getShopNumber()
        );
        if (!file_exists($this->getExportFolderPath())) {
            @mkdir($this->getExportFolderPath(), 0777, true);
        }

        $this->setLogFolderPath(
            Mage::getBaseDir("log") . DS . "shopgate" . DS . $this->getShopNumber()
        );
        if (!file_exists($this->getLogFolderPath())) {
            @mkdir($this->getLogFolderPath(), 0777, true);
        }

        $this->setCacheFolderPath(
            Mage::getBaseDir("tmp") . DS . "shopgate" . DS . $this->getShopNumber()
        );
        if (!file_exists($this->getCacheFolderPath())) {
            @mkdir($this->getCacheFolderPath(), 0777, true);
        }
    }

    /**
     * Writes the given fields to magento
     *
     * @param array   $fieldList
     * @param boolean $validate
     */
    public function save(array $fieldList, $validate = true)
    {
        Mage::dispatchEvent('shopgate_save_config_before', array('config' => $this, 'fieldlist' => $fieldList));

        ShopgateLogger::getInstance()->log('# setSettings save start', ShopgateLogger::LOGTYPE_DEBUG);

        if ($validate) {
            $this->validate($fieldList);
        }

        $mapping = array_merge(
            self::getConfigMapping(),
            self::getHiddenConfigMapping(),
            self::getNotPredefinedPropertiesMapping()
        );

        foreach ($fieldList as $property) {
            if (in_array($property, $this->_blacklistedConfigKeys)) {
                continue;
            }

            if (in_array($property, array_keys($mapping))) {
                $this->_saveField($mapping[$property], $property, $this->getStoreViewId());
            }
        }

        $this->_cleanupSavings($fieldList);
        $this->_clearCache();

        Mage::dispatchEvent('shopgate_save_config_after', array('config' => $this, 'fieldlist' => $fieldList));

        ShopgateLogger::getInstance()->log('# setSettings save end', ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * Saves the value given in property to all associated
     * core/config_data entries for the related store_views
     *
     * @param string $path
     * @param string $property
     * @param int    $store
     */
    protected function _saveField($path, $property, $store)
    {
        $scopes = $this->_getScopeForSave($store);
        $this->_solveSavingCollisions($scopes, $property, $path, $store);

        foreach ($scopes as $scope) {
            $scopeId = ($scope == 'websites') ? Mage::app()->getStore($store)->getWebsiteId() : $store;
            $this->_saveParticularField($path, $property, $scope, $scopeId);
        }
    }

    /**
     * Retrieves the scope/collection for a property to use on save
     * Its based on the shopnumber configuration
     *
     * @param      $store
     * @param null $virtualShopnumber
     *
     * @return array
     */
    protected function _getScopeForSave($store, $virtualShopnumber = null)
    {
        ShopgateLogger::getInstance()->log('   setSettings getScopeForSave', ShopgateLogger::LOGTYPE_DEBUG);

        $shopnumber = $virtualShopnumber ? $virtualShopnumber : $this->getShopNumber();

        $shopnumberRelations = $this->_fetchShopnumberStoreRelations()->getShopNumber();

        if ($virtualShopnumber && !isset($shopnumberRelations[$virtualShopnumber])) {
            $shopnumberRelations[$virtualShopnumber] = array();
        }

        $associatedStores = isset($shopnumberRelations[$shopnumber]) ? $shopnumberRelations[$shopnumber] : array();

        $scope = 'websites';
        // There is only one registered shop / shopnumber
        if (count($shopnumberRelations) == 1) {
            return array($store => $scope);
        }

        // The current shopnumber is not stored yet, check if there are already stores configured in the same website scope
        if (count($associatedStores) == 0) {
            $websiteId = Mage::app()->getStore($store)->getWebsiteId();

            if (Mage::getModel('core/config_data')
                    ->getCollection()
                    ->addFieldToFilter('path', self::XML_PATH_SHOPGATE_SHOP_NUMBER)
                    ->addFieldToFilter('scope', 'websites')
                    ->addFieldToFilter('scope_id', $websiteId)
                    ->count()
            ) {
                $scope = 'stores';
            }

            return array($store => $scope);
        } // There are no other storeviews set for the shopnumber fetch the scope directly from the database entry
        else {
            if (count($associatedStores) == 1) {

                $collection = Mage::getModel('core/config_data')
                                  ->getCollection()
                                  ->addFieldToFilter('path', self::XML_PATH_SHOPGATE_SHOP_NUMBER)
                                  ->addFieldToFilter(
                                      'value',
                                      Mage::getStoreConfig(self::XML_PATH_SHOPGATE_SHOP_NUMBER, $store)
                                  );

                foreach ($collection->getItems() as $entry) {
                    /** @var Mage_Core_Model_Config_Data $entry */
                    if ($entry->getScope() == 'stores') {
                        $scope = 'stores';
                    }
                }

                return array($store => $scope);
            } else {
                $result = array();

                // Check the other storeviews which are related to the current shopnumber
                foreach ($associatedStores as $storeId) {
                    $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

                    if (Mage::getModel('core/config_data')
                            ->getCollection()
                            ->addFieldToFilter('path', self::XML_PATH_SHOPGATE_SHOP_NUMBER)
                            ->addFieldToFilter('value', $shopnumber)
                            ->addFieldToFilter('scope', 'stores')
                            ->addFieldToFilter('scope_id', $storeId)
                            ->count()
                    ) {
                        $scope = 'stores';
                    } else {
                        if (Mage::getModel('core/config_data')
                                ->getCollection()
                                ->addFieldToFilter('path', self::XML_PATH_SHOPGATE_SHOP_NUMBER)
                                ->addFieldToFilter('value', $shopnumber)
                                ->addFieldToFilter('scope', 'websites')
                                ->addFieldToFilter('scope_id', $websiteId)
                                ->count()
                        ) {
                            $scope = 'websites';
                        } else {
                            continue;
                        }
                    }

                    $result[$storeId] = $scope;
                }

                return $result;
            }
        }
    }

    /**
     * Prepares an object with information to the shopnumber and storeview relations
     * also to the available store view under a given website scope
     *
     * @return Varien_Object
     */
    protected function _fetchShopnumberStoreRelations()
    {
        if (!Mage::registry('shopgate/shopnumber_store_relation')) {
            $shopnumberConfigs = Mage::getModel('core/config_data')
                                     ->getCollection()
                                     ->addFieldToFilter('path', self::XML_PATH_SHOPGATE_SHOP_NUMBER)
                                     ->addFieldToFilter('value', $this->getShopNumber());

            $storeViewsAvailable = Mage::getModel("core/store")
                                       ->getCollection()
                                       ->addFieldToFilter('code', array('neq' => 'admin'));

            $shopnumbers = array();
            foreach ($shopnumberConfigs as $shopnumberConfig) {
                $shopnumbers[$shopnumberConfig['value']] = array();
            }

            foreach ($storeViewsAvailable as $storeView) {
                $shopnumber = Mage::getStoreConfig(
                    self::XML_PATH_SHOPGATE_SHOP_NUMBER,
                    $storeView['store_id']
                );
                if ($shopnumber) {
                    $shopnumbers[$shopnumber][] = $storeView['store_id'];
                }
            }

            $storeViews = array();
            foreach ($storeViewsAvailable as $storeView) {
                $storeViews[$storeView['store_id']] = Mage::getStoreConfig(
                    self::XML_PATH_SHOPGATE_SHOP_NUMBER,
                    $storeView['store_id']
                );
            }

            $websites = array();
            foreach ($storeViewsAvailable as $storeView) {
                $websiteId              = Mage::app()->getStore($storeView)->getWebsiteId();
                $websites[$websiteId][] = $storeView['store_id'];
            }

            $obj = new Varien_Object();
            $obj->setData('shop_number', $shopnumbers);
            $obj->setData('store', $storeViews);
            $obj->setData('website', $websites);

            Mage::register('shopgate/shopnumber_store_relation', $obj);
        }

        return Mage::registry('shopgate/shopnumber_store_relation');
    }

    /**
     * Sets values onto stores scope if a websites scope value is threatening to overwrite them
     *
     * @param array  $scopeCollection
     * @param string $property
     * @param string $path
     * @param int    $store
     */
    protected function _solveSavingCollisions($scopeCollection, $property, $path, $store)
    {
        ShopgateLogger::getInstance()->log('   setSettings solveSavingCollisions start', ShopgateLogger::LOGTYPE_DEBUG);

        $websites    = $this->_fetchShopnumberStoreRelations()->getWebsite();
        $shopnumbers = $this->_fetchShopnumberStoreRelations()->getShopNumber();
        $shopnumber  = Mage::getStoreConfig(self::XML_PATH_SHOPGATE_SHOP_NUMBER, $store);

        foreach ($scopeCollection as $storeId => $scope) {
            if ($scope == 'websites') {
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                $intersect = array_intersect($websites[$websiteId], $shopnumbers[$shopnumber]);

                foreach (array_diff($websites[$websiteId], $intersect) as $storeView) {
                    $value = Mage::getStoreConfig($path, $storeView);
                    $value = !is_null($value) ? $value : '';

                    ShopgateLogger::getInstance()->log(
                        '    Preserve field \'' . $property . '\' on scope {\'stores\':\'' . $storeView . '\'}',
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                    $this->_saveParticularField($path, $property, 'stores', $storeView, $value);
                }
            }
        }

        ShopgateLogger::getInstance()->log('   setSettings solveSavingCollisions end', ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * Saves the in this model given property to magento core/config_data
     *
     * @param string $path
     * @param string $property
     * @param string $scope
     * @param int    $scopeId
     * @param null   $value
     */
    protected function _saveParticularField($path, $property, $scope, $scopeId, $value = null)
    {
        $possibleProperties = array_keys(
            array_merge(
                self::getConfigMapping(),
                self::getHiddenConfigMapping(),
                self::getNotPredefinedPropertiesMapping()
            )
        );

        $getter = $this->_getGetterMethod($property);
        if (!is_null($value)) {
            Mage::getConfig()->saveConfig($path, $value, $scope, $scopeId);
        } else {
            if (in_array($property, $possibleProperties)) {
                if (!method_exists($this, $getter)
                    && isset($this->additionalSettings[$property])
                ) {
                    $value = $this->additionalSettings[$property];
                } else {
                    if (method_exists($this, $getter)) {
                        $value = $this->{$getter}();
                    } else {
                        $value = null;
                    }
                }

                if ($value !== null) {
                    ShopgateLogger::getInstance()->log(
                        '    Saving config field \'' . $property . '\' with value \'' . $value . '\' to scope {\''
                        . $scope . '\':\'' . $scopeId . '\'}',
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                    $value = $this->_prepareForDatabase($property, $value);
                    Mage::getConfig()->saveConfig($path, $value, $scope, $scopeId);
                }
            } else {
                Mage::logException(
                    new Exception(
                        'The evaluated getter method "' . $getter .
                        '" is not available in class ' . __CLASS__
                    )
                );
            }
        }
    }

    /**
     * Simple setter fetcher
     *
     * @param string $classVar
     *
     * @return string
     */
    protected function _getSetterMethod($classVar)
    {
        $source = explode('_', $classVar);
        $target = array();
        foreach ($source as $value) {
            $target[] = uc_words($value);
        }

        return 'set' . implode('', $target);
    }

    /**
     * Simple getter fetcher
     *
     * @param string $classVar
     *
     * @return string
     */
    protected function _getGetterMethod($classVar)
    {
        $source = explode('_', $classVar);
        $target = array();
        foreach ($source as $value) {
            $target[] = uc_words($value);
        }

        return 'get' . implode('', $target);
    }

    /**
     * Checks stores scope values and deletes them if a websites scope value of the same type is already given
     *
     * @param array $fieldList
     */
    protected function _cleanupSavings($fieldList)
    {
        ShopgateLogger::getInstance()->log('   setSettings cleanup start', ShopgateLogger::LOGTYPE_DEBUG);

        $mapping = array_merge(
            self::getConfigMapping(),
            self::getHiddenConfigMapping(),
            self::getNotPredefinedPropertiesMapping()
        );

        foreach ($fieldList as $property) {
            $path = $mapping[$property];

            // Remove already in website scope set store scope values
            $collection = Mage::getModel('core/config_data')
                              ->getCollection()
                              ->addFieldToFilter('path', $path)
                              ->addFieldToFilter('scope', 'stores');

            foreach ($collection as $entry) {
                /** @var Mage_Core_Model_Config_Data $entry */
                $storeId   = $entry->getScopeId();
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

                if (Mage::getModel('core/config_data')
                        ->getCollection()
                        ->addFieldToFilter('path', $path)
                        ->addFieldToFilter('scope', 'websites')
                        ->addFieldToFilter('scope_id', $websiteId)
                        ->addFieldToFilter('value', $entry->getValue())
                        ->count()
                ) {
                    $entry->delete();

                    ShopgateLogger::getInstance()->log(
                        'Removing config db entry for path "' . $path .
                        '" in scope "stores" with id "' . $entry->getId() . '"',
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                }
            }

            // Remove entries with empty value and set in website scope
            $collection = Mage::getModel('core/config_data')
                              ->getCollection()
                              ->addFieldToFilter('path', $path)
                              ->addFieldToFilter('scope', 'websites')
                              ->addFieldToFilter('value', '');

            foreach ($collection as $entry) {
                $entry->delete();
                ShopgateLogger::getInstance()->log(
                    '    Removing config db entry for path \'' . $path . '\' in scope \'stores\' with id \''
                    . $entry->getId() . '\' which had an empty value for preservation',
                    ShopgateLogger::LOGTYPE_DEBUG
                );
            }
        }

        ShopgateLogger::getInstance()->log('   setSettings cleanup end', ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * Clears config cache after saving altered configuration
     */
    protected function _clearCache()
    {
        $result = Mage::app()->getCacheInstance()->clean(Mage_Core_Model_Config::CACHE_TAG);
        ShopgateLogger::getInstance()->log(
            ' Config cache cleared with result: ' . ($result ? '[OK]' : '[ERROR]'),
            ShopgateLogger::LOGTYPE_DEBUG
        );
    }

    /**
     * Fetches the property type described in phpdoc annotation
     *
     * @param string $property
     *
     * @return string
     */
    protected function _getPropertyType($property)
    {
        if (!in_array($property, array_keys(get_class_vars('ShopgateConfig')))) {
            return "string";
        }

        $r   = new ReflectionProperty('ShopgateConfig', $property);
        $doc = $r->getDocComment();
        preg_match_all('#@var ([a-zA-Z-_]*(\[\])?)(.*?)\n#s', $doc, $annotations);

        $value = 'string';
        if (count($annotations) > 0 && isset($annotations[1][0])) {
            $value = $annotations[1][0];
        }

        return $value;
    }

    /**
     * Cast a given property value to the matching property type
     *
     * @param mixed  $value
     * @param string $property
     *
     * @return boolean|number|string|integer
     */
    protected function _castToType($value, $property)
    {
        $type = $this->_getPropertyType($property);

        switch ($type) {
            case 'array':
                $decoded = $this->jsonDecode($value);
                $value   = is_array($decoded) ? $decoded : $value;

                return is_array($value) ? $value : explode(",", $value);
            case 'bool':
            case 'boolean':
                return (boolean)$value;
            case 'int':
            case 'integer':
                return (int)$value;
            case 'string':
                return (string)$value;
            default:
                return $value;
        }
    }

    /**
     * Converts values into a core_config_data compatible format
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function _prepareForDatabase($property, $value)
    {
        $type = $this->_getPropertyType($property);

        if ($type == 'array' && is_array($value)) {
            return implode(",", $value);
        }
        if (is_bool($value)) {
            $value = (int)$value;
        }

        return $value;
    }

    /**
     * Validate config
     *
     * @return bool
     */
    public function isValidConfig()
    {
        return !empty($this->shop_number) && !empty($this->customer_number) && !empty($this->apikey);
    }

    /**
     * Not underscore for protected cause default stuff from lib
     *
     * @return bool
     */
    protected function startup()
    {
        return true;
    }

    /**
     * @param bool $asArray
     *
     * @return array
     */
    public function getExportStores($asArray = false)
    {
        if ($asArray) {
            return explode(",", Mage::getStoreConfig(self::XML_PATH_SHOPGATE_EXPORT_STORES));
        } else {
            return Mage::getStoreConfig(self::XML_PATH_SHOPGATE_EXPORT_STORES);
        }
    }

    /**
     * @param string|string[] $values
     */
    public function setExportStores($values)
    {
        if (is_array($values)) {
            $values = implode(",", $values);
        }

        if ($values != null) {
            Mage::getConfig()->saveConfig(self::XML_PATH_SHOPGATE_EXPORT_STORES, $values);
        }
    }


    /**
     * Return product types
     *
     * @return array|mixed
     */
    public function getExportProductTypes()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_SHOPGATE_EXPORT_PRODUCT_TYPES));
    }

    /**
     * @param bool $explode
     *
     * @return array|mixed
     */
    public function getConvertDescription($explode = false)
    {
        if ($explode) {
            return explode(",", Mage::getStoreConfig(self::XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_NL2BR));
        } else {
            return Mage::getStoreConfig(self::XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_NL2BR);
        }
    }

    /**
     * Saves shop number for new shop from oauth registration to the proper scope
     *
     * @param int $shopnumber
     * @param int $storeViewId
     *
     * @return bool
     */
    public function oauthSaveNewShopNumber($shopnumber, $storeViewId)
    {
        $scopes = $this->_getScopeForSave($storeViewId, $shopnumber);

        /* free _fetchShopnumberStoreRelations cache */
        Mage::unregister('shopgate/shopnumber_store_relation');

        if (!isset($scopes[$storeViewId])) {
            return false;
        }

        $scope    = $scopes[$storeViewId];
        $scope_id = $scope == 'websites'
            ? Mage::app()->getStore($storeViewId)->getWebsiteId()
            : $storeViewId;

        Mage::getConfig()->saveConfig(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER,
            $shopnumber,
            $scope,
            $scope_id
        );

        Mage::app()->getStore($storeViewId)->resetConfig();

        if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, $storeViewId)) {
            Mage::getConfig()
                ->saveConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, 1, $scope, $scope_id);
        }

        $this->setShopNumber($shopnumber);

        return true;
    }

    /**
     * Fetches the net market countries from core_config_data and wrap it into an array
     *
     * @return array|mixed
     */
    public function getNetMarketCountries()
    {
        $countries = Mage::getStoreConfig(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_NET_MARKET_COUNTRIES,
            $this->getStoreViewId()
        );

        return explode(",", $countries);
    }

    /**
     * Returns if cart rules should be applied to shopgate carts
     *
     * @return mixed
     */
    public function applyCartRulesToCart()
    {
        return Mage::getStoreConfigFlag(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_APPLY_CART_RULES,
            $this->getStoreViewId()
        );
    }

    /**
     * Returns if only the simple product should be added to the cart,
     * in case the order contains config/simple prdocuts.
     *
     * @return mixed
     */
    public function addOnlySimplesToCart()
    {
        return Mage::getStoreConfigFlag(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_ADD_ONLY_SIMPLE,
            $this->getStoreViewId()
        );
    }

    /**
     * Returns if prices from shopgate should be used to overwrite the magento ones
     *
     * @return mixed
     */
    public function useShopgatePrices()
    {
        return Mage::getStoreConfigFlag(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_USE_SHOPGATE_PRICES,
            $this->getStoreViewId()
        );
    }

    /**
     * @inheritdoc
     */
    public function getRedirectableGetParams()
    {
        if (!Mage::helper('shopgate')->isShopgateApiRequest()) {
            $this->addAffiliateParameterToRedirectable();
        }

        return parent::getRedirectableGetParams();
    }

    /**
     * Adds affiliate parameter to allowed redirect parameters
     * Cached for page load efficiency.
     */
    private function addAffiliateParameterToRedirectable()
    {
        if (is_null($this->affiliateParameters)) {
            $params = $this->translateMagentoGetParameters(Mage::app()->getRequest()->getParams());
            if (!empty($params)) {
                $sgOrder = new ShopgateOrder();
                $sgOrder->setTrackingGetParameters($params);
                $parameters                    = Mage::getModel('shopgate/factory')
                                                     ->getAffiliate($sgOrder)
                                                     ->getModuleTrackingParameters();
                $this->affiliateParameters     = $parameters;
                $this->redirectable_get_params = array_merge($this->redirectable_get_params, $parameters);
            } else {
                $this->affiliateParameters = array();
            }
        }
    }

    /**
     * Helps translate magento GET parameters into Shopgate GET parameters
     *
     * @param array $parameters - array('get_key' => 'get_value')
     *
     * @return array( array('key'=>'get_key', 'value'=>'get_value'), ...)
     */
    private function translateMagentoGetParameters($parameters)
    {
        $sgGetParams = array();
        foreach ($parameters as $key => $value) {
            $sgGetParams[] = array('key' => $key, 'value' => $value);
        }

        return $sgGetParams;
    }
}
