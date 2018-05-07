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
 * @author     Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright  Shopgate Inc
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @deprecated 2.9.60
 */
class Shopgate_Framework_Model_Mobile_Redirect extends Mage_Core_Model_Abstract
{

    /**
     * Caching const
     */
    const CACHE_PRODUCT_OBJECT_ID = 'shopgate_mobile_redirect_product';
    const CACHE_PAGE_IDENTIFIER = 'shopgate_mobile_redirect_category';

    /**
     * Type const
     */
    const CATEGORY = 'category';
    const PRODUCT = 'product';
    const PAGE = 'page';
    const SEARCH = 'result';
    const INDEX = 'index';

    /** @var Shopgate_Framework_Model_Config */
    protected $_config;
    /** @var Shopgate_Framework_Model_Storage_Cache */
    protected $cache;

    /**
     * Construct and define config
     */
    public function _construct()
    {
        parent::_construct();
        $this->_config = Mage::helper('shopgate/config')->getConfig();
        $this->cache   = Mage::getModel('shopgate/storage_cache');
    }

    /**
     * Redirect with 301
     */
    public function redirectWithCode()
    {
        try {
            // no redirection in admin
            if (Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml') {
                Mage::getSingleton('core/session')->setData('shopgate_header', '');

                return;
            }

            // isAjax is not available on Magento < 1.5 >> no ajax-check
            if (method_exists(Mage::app()->getRequest(), 'isAjax')
                && Mage::app()->getRequest()->isAjax()
            ) {
                Mage::getSingleton('core/session')->setData('shopgate_header', '');

                return;
            }

            if (!$this->_config->isValidConfig()
                || !Mage::getStoreConfig(
                    Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE,
                    $this->_config->getStoreViewId()
                )
            ) {
                Mage::getSingleton('core/session')->setData('shopgate_header', '');

                return;
            }

            $jsHeader = $this->_getJsHeader();

            Mage::getSingleton('core/session')->setData('shopgate_header', $jsHeader);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->setData('shopgate_header', '');
            ShopgateLogger::getInstance()->log('error in mobile redirector: ' . $e->getMessage());
        }
    }

    /**
     * Get the js redirection header code
     *
     * @return string
     */
    protected function _getJsHeader()
    {
        $objId        = Mage::app()->getRequest()->getParam('id');
        $action       = Mage::app()->getRequest()->getControllerName();
        $route        = Mage::app()->getRequest()->getRouteName();
        $redirectType = Mage::getStoreConfig(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_REDIRECT_TYPE,
            $this->_config->getStoreViewId()
        );

        /**
         * Special cases where default front URL is rewritten
         */
        if (in_array(Mage::app()->getRequest()->getPathInfo(), array('', '/', '/index.php'))
            && Mage::getStoreConfig('web/default/front', $this->_config->getStoreViewId()) !== 'cms'
        ) {
            $action = self::INDEX;
            $route  = 'cms';
        }

        $automaticRedirect = $redirectType === Shopgate_Framework_Model_Config::REDIRECTTYPE_HTTP;

        switch ($action) {
            case self::PRODUCT:
                $jsHeader = $this->_getCachedJsHeaderByType(self::PRODUCT, $objId, $automaticRedirect, $route);
                break;
            case self::CATEGORY:
                $jsHeader = $this->_getCachedJsHeaderByType(self::CATEGORY, $objId, $automaticRedirect, $route);
                break;
            case self::PAGE:
                $objId          = Mage::app()->getRequest()->getParam('page_id');
                $pageIdentifier = $this->_getPageIdentifier($objId);
                $jsHeader       = $this->_getCachedJsHeaderByType(
                    self::PAGE,
                    $pageIdentifier,
                    $automaticRedirect,
                    $route
                );
                break;
            case self::SEARCH:
                $search   = Mage::app()->getRequest()->getParam('q');
                $jsHeader = $this->_getCachedJsHeaderByType(self::SEARCH, $search, $automaticRedirect, $route);
                break;
            case self::INDEX:
                $jsHeader = $this->_getCachedJsHeaderByType(self::INDEX, null, $automaticRedirect, $route);
                break;
            default:
                $jsHeader = $this->_getCachedJsHeaderByType(null, null, $automaticRedirect, $route);
                break;
        }

        return $jsHeader;
    }

    /**
     * Determine the (cached) page identifier
     *
     * @param string $pageId
     *
     * @return string
     */
    protected function _getPageIdentifier($pageId)
    {
        $cacheKey  = md5(
            implode(
                '-',
                array(
                    self::CACHE_PAGE_IDENTIFIER,
                    $pageId,
                    $this->_config->getStoreViewId()
                )
            )
        );
        $cacheData = $this->cache->loadCache($cacheKey);
        if ($cacheData) {
            return $cacheData;
        }

        $page   = Mage::getModel('cms/page')->load($pageId);
        $result = $page->getData('identifier');

        $map = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_CMS_MAP);
        if (is_string($map) && strpos($map, '{') !== false) {
            $map = unserialize($map);
            foreach ($map as $mapEntry) {
                if ($mapEntry[Shopgate_Framework_Block_CmsMap::MAGE_PAGE_KEY] === $result) {
                    $result = $mapEntry[Shopgate_Framework_Block_CmsMap::SG_PAGE_KEY];
                }
            }
        }

        $this->cache->saveCache($result, $cacheKey);

        return $result;
    }

    /**
     * Get cached header js for redirect or load and save to cache
     *
     * @param $type  string
     * @param $objId string|int
     * @param $automaticRedirect
     * @param $route
     *
     * @return string
     */
    protected function _getCachedJsHeaderByType($type, $objId, $automaticRedirect, $route)
    {
        $storeViewId = $this->_config->getStoreViewId();
        switch ($type) {
            case self::CATEGORY:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_category_' . $objId . '_redirect_type_' . intval(
                        $automaticRedirect
                    );
                break;
            case self::PRODUCT:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_item_' . $objId . '_redirect_type_' . intval(
                        $automaticRedirect
                    );
                break;
            case self::PAGE:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_page_' . $objId . '_redirect_type_' . intval(
                        $automaticRedirect
                    );
                break;
            case self::SEARCH:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_catalogsearch_' . md5(
                        $objId
                    ) . '_redirect_type_' . intval(
                                $automaticRedirect
                            );
                break;
            case self::INDEX:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_index_redirect_type_' . intval(
                        $automaticRedirect
                    );
                break;
            default:
                $cacheKey = $storeViewId . '_' . $route . '_sg_mobile_default_type_' . intval($automaticRedirect);
                break;
        }

        $jsHeader = $this->cache->loadCache($cacheKey);

        if ($jsHeader === false) {
            $shopgateRedirect = $this->_createMobileRedirect($type, $objId);

            if (is_null($shopgateRedirect)) {
                return '';
            }

            if (!in_array(
                    $type,
                    array(
                        self::CATEGORY,
                        self::PRODUCT,
                        self::SEARCH,
                        self::INDEX,
                        self::PAGE
                    )
                )
                && !Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ENABLE_DEFAULT_REDIRECT)
            ) {
                $shopgateRedirect->supressRedirectTechniques(true, true);
            }

            $disabledRoutes = explode(
                ',',
                Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_ROUTES)
            );
            if (in_array($route, $disabledRoutes)) {
                $shopgateRedirect->supressRedirectTechniques(true, true);
            }

            $disabledControllers = explode(
                ',',
                Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CONTROLLERS)
            );
            $controllerName      = $type;
            if (in_array($controllerName, $disabledControllers)) {
                $shopgateRedirect->supressRedirectTechniques(true, true);
            }

            if ($controllerName == self::PRODUCT) {
                $productId        = Mage::app()->getRequest()->getParam('id');
                $disabledProducts = explode(
                    ',',
                    Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_PRODUCTS)
                );

                if (in_array($productId, $disabledProducts)) {
                    $shopgateRedirect->supressRedirectTechniques(true, true);
                }
            }

            if ($controllerName == self::CATEGORY) {
                $categoryId         = Mage::app()->getRequest()->getParam('id');
                $disabledCategories = explode(
                    ',',
                    Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DISABLE_REDIRECT_CATEGORIES)
                );
                if (in_array($categoryId, $disabledCategories)) {
                    $shopgateRedirect->supressRedirectTechniques(true, true);
                }
            }

            switch ($type) {
                case self::CATEGORY:
                    $jsHeader = $shopgateRedirect->buildScriptCategory($objId);
                    break;
                case self::PRODUCT:
                    $jsHeader = $shopgateRedirect->buildScriptItem($objId);
                    break;
                case self::PAGE:
                    $jsHeader = $shopgateRedirect->buildScriptCms($objId);
                    break;
                case self::SEARCH:
                    $jsHeader = $shopgateRedirect->buildScriptSearch($objId);
                    break;
                case self::INDEX:
                    $jsHeader = $shopgateRedirect->buildScriptShop();
                    break;
                default:
                    $jsHeader = $shopgateRedirect->buildScriptDefault();
                    break;
            }

            $this->cache->saveCache($jsHeader, $cacheKey);
        }

        return $jsHeader;
    }

    /**
     * Get cached header js for redirect or load and save to cache
     *
     * @param $type  string
     * @param $objId string|int
     *
     * @return Shopgate_Helper_Redirect_MobileRedirect | null
     */
    protected function _createMobileRedirect($type, $objId)
    {
        $redirectObj = null;
        try {
            $storeId   = $this->_config->getStoreViewId();
            $siteName  = Mage::app()->getWebsite()->getName();
            $mobileUrl = $this->_config->getCname();
            $shopUrl   = Mage::getStoreConfig('web/unsecure/base_url', $storeId);

            switch ($type) {
                case self::CATEGORY:
                    $pageTitle = Mage::getModel('catalog/category')->setStoreId($storeId)->load($objId)->getName();
                    break;
                case self::PRODUCT:
                    $pageTitle = Mage::getModel('catalog/product')->setStoreId($storeId)->load($objId)->getName();
                    break;
                case self::PAGE:
                    $pageTitle = Mage::getModel('cms/page')->setStoreId($storeId)->load($objId)->getTitle();
                    break;
                default:
                    $pageTitle = $siteName;
                    break;
            }
            if (empty($pageTitle)) {
                $pageTitle = $siteName;
            }

            $redirectVars = array(
                Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_SITENAME       => $siteName,
                Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_DESKTOP_URL    => $shopUrl,
                Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_MOBILE_WEB_URL => $mobileUrl,
                Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_TITLE          => $pageTitle
            );

            if ($type == self::PRODUCT) {
                $imageUrl = $ean = $categoryName = '';

                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($objId);

                $categoryId = Mage::app()->getRequest()->getParam('category');
                if (!empty($categoryId)) {
                    $categoryName = Mage::getModel('catalog/category')->load($categoryId)->getName();
                }

                $name          = $product->getData('name');
                $availableText = $product->isInStock() ? 'instock' : 'oos';

                $eanAttCode = Mage::getStoreConfig(
                    Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EAN_ATTR_CODE,
                    $storeId
                );
                if (is_object($eanAttCode)) {
                    $ean = $product->getData($eanAttCode);
                }

                $image = $product->getMediaGalleryImages()->getFirstItem();
                if (is_object($image)) {
                    $imageUrl = $image->getData('url');
                }

                $description = $product->getData('short_description');
                if (strlen($description) > 140) {
                    $description = substr($description, 0, 136) . ' ...';
                }

                $price           = $product->getData('price');
                $defaultCurrency = Mage::getStoreConfig('currency/options/default', $storeId);
                $baseCurrency    = Mage::getStoreConfig('currency/options/base', $storeId);
                if ($defaultCurrency != $baseCurrency) {
                    $price = Mage::helper('directory')->currencyConvert($price, $baseCurrency, $defaultCurrency);
                }
                $priceIsGross = Mage::getStoreConfig('tax/calculation/price_includes_tax', $storeId);
                $request      = new Varien_Object(
                    array(
                        'country_id'        => Mage::getStoreConfig('tax/defaults/country', $storeId),
                        'region_id'         => Mage::getStoreConfig('tax/defaults/region', $storeId),
                        'postcode'          => Mage::getStoreConfig('tax/defaults/postcode', $storeId),
                        'customer_class_id' => Mage::getModel('tax/calculation')->getDefaultCustomerTaxClass($storeId),
                        'product_class_id'  => $product->getData('tax_class_id'),
                        'store'             => Mage::app()->getStore($storeId)
                    )
                );

                /** @var Mage_Tax_Model_Calculation $model */
                $taxRate = Mage::getSingleton('tax/calculation')->getRate($request) / 100;
                if ($priceIsGross) {
                    $priceNet   = round($price / (1 + $taxRate), 2);
                    $priceGross = round($price, 2);
                } else {
                    $priceNet   = round($price, 2);
                    $priceGross = round($price * (1 + $taxRate), 2);
                }

                $varTemp = array(
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_IMAGE             => $imageUrl,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_NAME              => $name,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_DESCRIPTION_SHORT => $description,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_EAN               => $ean,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_AVAILABILITY      => $availableText,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_CATEGORY          => $categoryName,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_PRICE             => $priceGross,
                    Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_PRETAX_PRICE      => $priceNet
                );

                if ($price) {
                    $varTemp[Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_CURRENCY]        =
                        $defaultCurrency;
                    $varTemp[Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_PRETAX_CURRENCY] =
                        $defaultCurrency;
                }

                $redirectVars = array_merge($redirectVars, $varTemp);
            }
            $redirectObj = $this->parseRedirectValues($redirectVars);
        } catch (Exception $e) {
            ShopgateLogger::getInstance()->log('error on tag creation for type:' . $type . ' object ID:' . $objId);
        }

        return $redirectObj;
    }

    /**
     * Parses key values and adds them as site
     * parameters to the mobile redirect object
     *
     * @param array $redirectList
     *
     * @return Shopgate_Helper_Redirect_MobileRedirect
     */
    private function parseRedirectValues($redirectList)
    {
        $builder     = new ShopgateBuilder($this->_config);
        $userAgent   = Mage::helper('core/http')->getHttpUserAgent();
        $redirectObj = $builder->buildMobileRedirect($userAgent, Mage::app()->getRequest()->getParams(), $_COOKIE);

        foreach ($redirectList as $redirectKey => $redirectValue) {
            if ($redirectValue) {
                $redirectObj->addSiteParameter($redirectKey, $redirectValue);
            }
        }

        return $redirectObj;
    }
}
