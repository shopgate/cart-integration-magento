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
class Shopgate_Framework_Model_Export_Product_Csv extends Shopgate_Framework_Model_Export_Product
{
    /**
     * Const for enterprise gift wrapping
     */
    const GIFT_WRAP_OPTION_ID = 'EE_GiftWrap';

    /**
     * Const for newest to oldest sort_order
     * => Date 1.1.3000 0:0:0
     */
    const MAX_TIMESTAMP = 32503676400;

    /**
     * @var null
     */
    protected $_defaultRow = null;

    /**
     * @var null
     */
    protected $_actionCache = null;

    /**
     * @var null | Mage_Catalog_Model_Product
     */
    protected $_parentProduct = null;

    /**
     * @var null
     */
    protected $_defaultTax = null;

    /**
     * @var array
     */
    protected $_stack = array();

    /**
     * @param Mage_Catalog_Model_Product        $product
     * @param Mage_Catalog_Model_Product | null $parentProduct
     *
     * @return array
     */
    public function generateData($product, $parentProduct = null)
    {
        $this->_parentProduct = $parentProduct;
        foreach (array_keys($this->_defaultRow) as $key) {
            /* clear values */
            $this->_defaultRow[$key] = null;

            $action = '_set' . uc_words($key, '', '_');
            if (empty($this->_actionCache[$action])) {
                $this->_actionCache[$action] = true;
            }
        }

        foreach (array_keys($this->_actionCache) as $_action) {
            if (method_exists($this, $_action)) {
                $this->{$_action}($product);
            }
        }

        $this->_setOptions($product);
        $this->_setAttributes($product);
        $this->_setBundleOptions($product);
        $this->_parentProduct = null;

        return $this->_defaultRow;
    }

    /**
     * @param $defaultRow
     *
     * @return Shopgate_Framework_Model_Export_Product_Csv
     */
    public function setDefaultRow($defaultRow)
    {
        $this->_defaultRow = $defaultRow;

        return $this;
    }

    /**
     * @param $tax
     *
     * @return Shopgate_Framework_Model_Export_Product_Csv
     */
    public function setDefaultTax($tax)
    {
        $this->_defaultTax = $tax;

        return $this;
    }


    /**
     * Get item number / id
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setItemNumber($product)
    {
        $this->_defaultRow['item_number'] = $product->getId();

        if ($this->_parentProduct != null) {
            $this->_defaultRow['item_number'] = "{$this->_parentProduct->getId()}-{$product->getId()}";
        }
    }

    /**
     * Get sku (item number public)
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setItemNumberPublic($product)
    {
        $this->_defaultRow['item_number_public'] = $product->getSku();
    }

    /**
     * Get item deep link
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setUrlDeeplink($product)
    {
        $this->_defaultRow['url_deeplink'] = $this->_getExportHelper()->getDeepLink($product, $this->_parentProduct);
    }

    /**
     * Get item currency
     */
    protected function _setCurrency()
    {
        $this->_defaultRow['currency'] = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
    }

    /**
     * Get item name
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setItemName($product)
    {
        $parentName =
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_PARENT_PRODUCT_NAME);

        if ($parentName && $this->_parentProduct != null) {
            $this->_defaultRow["item_name"] = $this->getProductName($this->_parentProduct);
        } else {
            $this->_defaultRow["item_name"] = $this->getProductName($product);
        }
    }

    /**
     * Get last update time
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setLastUpdate($product)
    {
        $date = $product->getUpdatedAt();

        if (!empty($date)) {
            $this->_defaultRow['last_update'] = date(DateTime::ISO8601, strtotime($date));
        }
    }

    /**
     * Get ean attribute if predefined
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setEan($product)
    {
        $attributeCode = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EAN_ATTR_CODE);

        if (!empty($attributeCode)) {
            $ean = $product->getData($attributeCode);
            if (!empty($ean)) {
                $this->_defaultRow['ean'] = $ean;
            }
        }
    }

    /**
     * Get product weight
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setWeight($product)
    {
        $this->_defaultRow['weight'] = $this->getWeight($product);
    }

    /**
     * Get product tags
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setTags($product)
    {
        $this->_defaultRow['tags'] = $product->getMetaKeyword();
    }

    /**
     * export marketplace
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setMarketplace($product)
    {
        $this->_defaultRow["marketplace"] = "1";

        if ($product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            $this->_defaultRow["marketplace"] = "0";
        }
    }

    /**
     * Get stock data
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setUseStock($product)
    {
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = $product->getStockItem();

        $useStock = 0;

        if ($stockItem->getManageStock()) {
            switch ($stockItem->getBackorders() && $stockItem->getIsInStock()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    $useStock = 1;
                    break;
            }
        }

        $this->_defaultRow["use_stock"] = $useStock;
    }

    /**
     * Get stock qty
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setStockQuantity($product)
    {
        $quantity = 0;
        if ($product->getIsSalable()) {
            $quantity = $product->getStockItem()->getQty();
        }
        $this->_defaultRow["stock_quantity"] = (int)$quantity;
    }

    /**
     * Get active status for the product
     */
    protected function _setActiveStatus()
    {
        $show   = Mage::getStoreConfig(Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK);
        $active = ShopgatePlugin::PRODUCT_STATUS_STOCK;
        if ($show) {
            $active = ShopgatePlugin::PRODUCT_STATUS_ACTIVE;
        }
        $this->_defaultRow["active_status"] = $active;
    }

    /**
     * Get product min order qty
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setMinimumOrderQuantity($product)
    {
        $this->_defaultRow["minimum_order_quantity"] = $product->getStockItem()->getMinSaleQty();
    }

    /**
     * Get product max order qty
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setMaximumOrderQuantity($product)
    {
        $this->_defaultRow["maximum_order_quantity"] = $product->getStockItem()->getMaxSaleQty();
    }

    /**
     * Get product manufacturer
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setManufacturer($product)
    {
        $this->_defaultRow['manufacturer'] = $this->_getExportHelper()->getManufacturer($product);
    }

    /**
     * Check if product is available by checking qty of 1 (will also trigger back orders).
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setIsAvailable($product)
    {
        $this->_defaultRow['is_available'] = $product->isSaleable();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setAvailableText($product)
    {
        $this->_defaultRow['available_text'] = $this->_getExportHelper()->getAvailableText(
            $product,
            $this->_getConfig()
                 ->getStoreViewId()
        );
    }

    /**
     * Export the image-urls with the order given in magento backend
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setUrlsImages($product)
    {
        $images                           = $this->getImages($product, $this->_parentProduct);
        $this->_defaultRow['urls_images'] = implode('||', $images);
    }

    /**
     * Get related products
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setRelatedShopItemNumbers($product)
    {
        $this->_defaultRow["related_shop_item_numbers"] = implode("||", $product->getRelatedProductIds());
    }

    /**
     * Get tax percent
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setTaxPercent($product)
    {
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_USE_ROOT_PRICES)
            && $this->_parentProduct != null
            && $this->_parentProduct->isConfigurable()
        ) {
            $product = $this->_parentProduct;
        }
        if ($product->getTaxClassId() != 0) {
            $this->_defaultRow['tax_percent'] = 0;

            $obj = new Varien_Object(
                array(
                    'country_id'        => Mage::getStoreConfig(
                        "tax/defaults/country",
                        $this->_getConfig()->getStoreViewId()
                    ),
                    'customer_class_id' => $this->_defaultTax,
                    'product_class_id'  => $product->getTaxClassId()
                )
            );

            $tax                              = Mage::getModel("tax/calculation")->getRate($obj);
            $this->_defaultRow['tax_percent'] = $tax;
        }
    }

    /**
     * Export base price of the product only needed for DerModPro Base_Price extension
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setBasicPrice($product)
    {
        if (Mage::getConfig()->getModuleConfig('DerModPro_BasePrice')->is('active', 'true')
            && Mage::getStoreConfig('catalog/baseprice/disable_ext') == 0
        ) {
            $format                           = "{{baseprice}} / {{reference_amount}} {{reference_unit_short}}";
            $basicPrice                       = Mage::helper("baseprice")->getBasePriceLabel($product, $format);
            $basicPrice                       = strip_tags($basicPrice);
            $basicPrice                       = htmlentities($basicPrice, null, "UTF-8");
            $this->_defaultRow["basic_price"] = $basicPrice;
        }
    }

    /**
     * Get product price
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setUnitAmount($product)
    {
        $price = $this->_getExportHelper()->convertPriceCurrency($this->_getUnitAmount($product, true));
        $price = $this->_formatPriceNumber($price);

        $this->_defaultRow["unit_amount"] = $price;
    }

    /**
     * Get old product price
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setOldUnitAmount($product)
    {
        $this->_setUnitAmount($product);
        $price = $this->_defaultRow['unit_amount'];

        $oldPrice = (float)$product->getPrice();
        $oldPrice = Mage::helper('tax')->getPrice($product, $oldPrice, true);

        if ($oldPrice <= $price) {
            $oldPrice = 0;
        }

        $oldPrice = $this->_getExportHelper()->convertPriceCurrency($oldPrice);
        $oldPrice = $this->_formatPriceNumber($oldPrice);

        $this->_defaultRow["old_unit_amount"] = $oldPrice;
    }

    /**
     * Get internal order info
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setInternalOrderInfo($product)
    {
        $internalOrderInfo = array(
            "store_view_id" => $product->getStoreId(),
            "product_id"    => $product->getId(),
            "item_type"     => $product->getTypeId(),
            "exchange_rate" => $this->_getExportHelper()->convertPriceCurrency(1),
        );

        $this->_defaultRow['internal_order_info'] = $this->_getConfig()->jsonEncode($internalOrderInfo);
    }

    /**
     * Get category numbers
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setCategoryNumbers($product)
    {
        $categoryIds = array();

        if ($this->_getExportHelper()->isProductVisibleInCategories($product)) {
            $orderOption = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_ITEM_SORT);
            $linkedCats  = Mage::getResourceSingleton('shopgate/product')->getCategoryIdsAndPosition($product);

            foreach ($linkedCats as $category) {
                switch ($orderOption) {
                    case Shopgate_Framework_Model_System_Config_Source_Item_Sort::SORT_TYPE_PRICE_DESC:
                        $sortIndex     = round($product->getFinalPrice() * 100, 0);
                        $categoryIds[] = $category['category_id'] . '=>' . $sortIndex;
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Item_Sort::SORT_TYPE_POSITION:
                        $categoryIds[] =
                            $category['category_id'] . '=>' . ($category['max_position'] - $category['position']);
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Item_Sort::SORT_TYPE_LAST_UPDATED:
                        $sortIndex     = Mage::getModel('core/date')->timestamp(strtotime($product->getUpdatedAt()));
                        $categoryIds[] = $category['category_id'] . '=>' . $sortIndex;
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Item_Sort::SORT_TYPE_NEWEST:
                        $sortIndex     = Mage::getModel('core/date')->timestamp(strtotime($product->getCreatedAt()));
                        $categoryIds[] = $category['category_id'] . '=>' . (self::MAX_TIMESTAMP - $sortIndex);
                        break;
                    default:
                        $categoryIds[] = $category['category_id'];
                }
            }
        }

        $this->_defaultRow['category_numbers'] = implode('||', $categoryIds);
    }

    /**
     * Get description
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setDescription($product)
    {
        $this->_defaultRow["description"] = $this->_getExportHelper()->createFullDescription(
            $product,
            $this->_parentProduct
        );
    }

    /**
     * Get parent item id
     */
    protected function _setParentItemNumber()
    {
        $this->_defaultRow["parent_item_number"] = "";

        if ($this->_parentProduct) {
            $this->_defaultRow["parent_item_number"] = $this->_parentProduct->getId();
        }
    }

    /**
     * check for children
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setHasChildren($product)
    {
        $this->_defaultRow["has_children"] = 0;

        if ($product->isConfigurable()) {
            $this->_defaultRow["has_children"] = "1";
        }
    }

    /**
     * Get manufacturer suggested retail price
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function _setMsrp($product)
    {
        if ($msrp = $this->getMsrp($product)) {
            $this->_defaultRow['msrp'] = round($msrp, 2);
        }
    }

    /**
     * Adds JSON with all upsell and crosssell products to related_shop_items column.
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setRelatedShopItems($product)
    {
        $relatedItems = array();

        /** @var Mage_Catalog_Model_Product $crosssellProduct */
        foreach ($product->getCrossSellProductIds() as $crosssellProductId) {
            $relatedItems[] = array(
                'type'        => 'crossell',
                'item_number' => $crosssellProductId,
                'restricted'  => false
            );
        }

        /** @var Mage_Catalog_Model_Product $upsellProduct */
        foreach ($product->getUpSellProductIds() as $upsellProductId) {
            $relatedItems[] = array(
                'type'        => 'upsell',
                'item_number' => $upsellProductId,
                'restricted'  => false
            );
        }

        if (!empty($relatedItems)) {
            $this->_defaultRow['related_shop_items'] = $this->_getConfig()->jsonEncode($relatedItems);
        }
    }

    /**
     * Get item properties
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setProperties($product)
    {
        $ignoredProductAttributeCodes = array("manufacturer", "model");
        $ignoredProperties            = explode(
            ",",
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_FILTER_PROPERTIES)
        );

        $forcedProperties = explode(
            ",",
            Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_FORCE_PROPERTY_EXPORT)
        );

        foreach ($ignoredProperties as &$prop) {
            $prop = trim($prop);
        }
        unset($prop);

        $ignoredProductAttributeCodes = array_merge($ignoredProductAttributeCodes, $ignoredProperties);
        $ignoredProductAttributeCodes = array_unique($ignoredProductAttributeCodes);

        $properties = $this->_getProductProperties($product, $ignoredProductAttributeCodes, $forcedProperties);
        /**
         * merge attributes in case of simple/config
         * (if attribute is present on both, data from simple will be taken, otherwise parent)
         * empty attributes will not be exported anymore
         */
        if ($this->_parentProduct != null && $this->_parentProduct->isConfigurable()) {
            $properties = array_merge(
                $this->_getProductProperties($this->_parentProduct, $ignoredProductAttributeCodes),
                $properties
            );
        }

        $this->_defaultRow["properties"] = implode("||", $properties);
    }

    /**
     * export tax class - for USA export only
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setTaxClass($product)
    {
        /* @var $taxClass Mage_Tax_Model_Class */
        $taxClass = Mage::getModel("tax/class")->load($product->getTaxClassId());

        if (!$taxClass->isEmpty()) {
            $this->_defaultRow['tax_class'] = "{$taxClass->getId()}=>{$taxClass->getClassName()}";
        }
    }

    /**
     * export unit old unit amount net - for USA export only
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setOldUnitAmountNet($product)
    {
        $this->_setUnitAmountNet($product);
        $price = $this->_defaultRow['unit_amount_net'];

        $oldPrice = floatval($product->getPrice());
        $oldPrice = Mage::helper('tax')->getPrice($product, $oldPrice, false);

        if ($oldPrice <= $price) {
            $oldPrice = 0;
        }

        $oldPrice                                 = $this->_getExportHelper()->convertPriceCurrency($oldPrice);
        $oldPrice                                 = $this->_formatPriceNumber($oldPrice, 8);
        $this->_defaultRow['old_unit_amount_net'] = $oldPrice;
    }

    /**
     * export unit amount net - only for USA export
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setUnitAmountNet($product)
    {
        $price                                = $this->_getExportHelper()->convertPriceCurrency(
            $this->_getUnitAmount($product, false)
        );
        $price                                = $this->_formatPriceNumber($price, 8);
        $this->_defaultRow['unit_amount_net'] = $price;
    }

    /**
     * returning unit amount
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool                       $includingTax
     *
     * @return float|int
     */
    protected function _getUnitAmount($product, $includingTax = false)
    {
        $useParent = false;

        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_USE_ROOT_PRICES)
            && $this->_parentProduct != null
            && $this->_parentProduct->isConfigurable()
        ) {
            $useParent = true;
        }

        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_USE_PRICE_INDEX_ON_EXPORT)) {
            $index = Mage::getModel('catalog/product_indexer_price');
            $price = $useParent
                ? $index->load($this->_parentProduct->getId())->getFinalPrice()
                : $index->load($product->getId())->getFinalPrice();
        } else {
            $price = $useParent
                ? $this->_parentProduct->getFinalPrice()
                : $product->getFinalPrice();
        }

        if ($price <= 0) {
            $rulePrice = $useParent
                ? Mage::helper('shopgate/export')->calcProductPriceRule($this->_parentProduct)
                : Mage::helper('shopgate/export')->calcProductPriceRule($product);
            $price     = $useParent
                ? $this->_parentProduct->getFinalPrice()
                : $product->getFinalPrice();

            if ($rulePrice && $rulePrice < $price) {
                $price = $rulePrice;
            }
        }

        if (null != $this->_parentProduct && $this->_parentProduct->isConfigurable() && $useParent) {
            $totalOffset     = 0;
            $totalPercentage = 0;
            $superAttributes = $this->_parentProduct
                ->getTypeInstance(true)
                ->getConfigurableAttributes($this->_parentProduct);

            foreach ($superAttributes as $superAttribute) {
                $code  = $superAttribute->getProductAttribute()->getAttributeCode();
                $index = $product->getData($code);

                if ($superAttribute->hasData('prices')) {
                    foreach ($superAttribute->getPrices() as $saPrice) {
                        if ($index != $saPrice['value_index']) {
                            continue;
                        }
                        if ($saPrice['is_percent']) {
                            $totalPercentage += $saPrice['pricing_value'];
                        } else {
                            $totalOffset += $saPrice['pricing_value'];
                        }
                        break;
                    }
                }
            }

            if ($price == $this->_parentProduct->getPrice()) {
                $price += $price * $totalPercentage / 100;
                $price += $totalOffset;
            }
        }

        return Mage::helper('tax')->getPrice($product, $price, $includingTax);
    }

    /**
     * Set options for products
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function _setOptions(Mage_Catalog_Model_Product $product)
    {
        if (!empty($this->_parentProduct) && $this->_parentProduct->isConfigurable()) {
            $product = $this->_parentProduct;
        }

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            || !empty($this->_parentProduct) && $this->_parentProduct->isConfigurable()
            || $product->isConfigurable()
        ) {

            $options         = $product->getOptions();
            $num_inputs      = 1;
            $num_options     = 1;
            $pricesInclTaxes = Mage::helper('tax')->priceIncludesTax($this->_getConfig()->getStoreViewId());

            foreach ($options as $option) {
                /* @var $option Mage_Catalog_Model_Product_Option */
                if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
                    || $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
                    || $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
                    || $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
                ) {

                    $_option       = $option->getId() . '=' . $option->getDefaultTitle();
                    $_option_value = array();

                    if (!$option->getIsRequire()) {
                        $_option_value[] = '0=' . $this->_getHelper()->__('None');
                    }

                    $priceMultiplier = $this->_getStackPriceMultiplicator($product->getStockItem());
                    foreach ($option->getValues() as $value) {
                        $valuePrice      = $this->_getExportHelper()->getOptionPrice(
                            $product,
                            $value->getPrice($pricesInclTaxes)
                        );
                        $_option_value[] = $value->getId() . '=' . trim(
                                $value->getTitle()
                            ) . '=>' . ($valuePrice * $priceMultiplier * 100);
                    }

                    $info                           =
                        $this->_getConfig()->jsonDecode($this->_defaultRow['internal_order_info'], true);
                    $info['has_individual_options'] = true;

                    $this->_defaultRow['has_options']                  = '1';
                    $this->_defaultRow['internal_order_info']          = $this->_getConfig()->jsonEncode($info);
                    $this->_defaultRow["option_{$num_options}"]        = $_option;
                    $this->_defaultRow["option_{$num_options}_values"] = implode('||', $_option_value);

                    $num_options++;
                } else {
                    if ($option->getType() === Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD
                        || $option->getType() === Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA
                    ) {
                        $valuePrice = $this->_getExportHelper()->getOptionPrice(
                            $product,
                            $option->getPrice($pricesInclTaxes)
                        );

                        $priceMultiplier = $this->_getStackPriceMultiplicator($product->getStockItem());

                        $this->_defaultRow['has_input_fields']                     = '1';
                        $this->_defaultRow["input_field_{$num_inputs}_number"]     = $option->getId();
                        $this->_defaultRow["input_field_{$num_inputs}_type"]       = 'text';
                        $this->_defaultRow["input_field_{$num_inputs}_label"]      = $option->getDefaultTitle();
                        $this->_defaultRow["input_field_{$num_inputs}_infotext"]   = '';
                        $this->_defaultRow["input_field_{$num_inputs}_required"]   = $option->getIsRequire();
                        $this->_defaultRow["input_field_{$num_inputs}_add_amount"] = $this->_formatPriceNumber(
                            $valuePrice * $priceMultiplier,
                            2
                        );

                        $num_inputs++;
                    }
                }
            }
        }
    }

    /**
     * Set attributes for the product
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function _setAttributes(Mage_Catalog_Model_Product $product)
    {
        $tmpProduct = null;
        if ($this->_parentProduct && $this->_parentProduct->isConfigurable()) {
            $tmpProduct = $this->_parentProduct;
        } else {
            if ($product->isConfigurable()) {
                $tmpProduct = $product;
            } else {
                return false;
            }
        }

        // Create a stack with all attribute-codes
        // fetching it on every item it get very high memory usage on some systems
        if (!isset($this->_stack[$tmpProduct->getId()])) {
            $productTypeInstance = $tmpProduct->getTypeInstance(true);
            if ($productTypeInstance == null || !method_exists($productTypeInstance, 'getUsedProducts')) {
                return false;
            }
            $allowAttributes                    = $productTypeInstance->getConfigurableAttributes($tmpProduct);
            $this->_stack[$tmpProduct->getId()] = $allowAttributes;
        }

        $i = 0;
        if ($this->_parentProduct) {
            foreach ($this->_stack[$this->_parentProduct->getId()] as $attribute) {
                $i++;
                $attribute = $attribute->getProductAttribute();
                if ($attribute == null) {
                    continue;
                }
                $attribute                         = $attribute->getAttributeCode();
                $attrValue                         = $product->getResource()->getAttribute($attribute)->getFrontend();
                $this->_defaultRow["attribute_$i"] = $attrValue->getValue($product);
            }
        } else {
            if ($product->isConfigurable()) {
                foreach ($this->_stack[$product->getId()] as $attribute) {
                    $i++;
                    $this->_defaultRow["attribute_$i"] = $attribute->getLabel();
                }
            }
        }
    }

    /**
     * Set bundle options
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setBundleOptions(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return;
        }

        /**
         * This function does not exists in Magento <= 1.5!!  Mage::helper('catalog/product')->getSkipSaleableCheck()
         */
        $typeInstance = $product->getTypeInstance(true);
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection    = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        $bundleOptions       = $optionCollection->appendSelections($selectionCollection, false, true);
        $bundlePriceModel    = Mage::getModel('bundle/product_price');
        $selectionQuantities = array();
        $options             = array();

        foreach ($bundleOptions as $bundleOption) {
            /* @var $bundleOption Mage_Bundle_Model_Option */
            $option = array(
                'key'    => $bundleOption->getId() . '=' . $bundleOption->getTitle(),
                'values' => array()
            );

            if (!$bundleOption->getRequired()) {
                $option["values"][] = $this->_getHelper()->__('-- Not Selected --');
            } else {
                if (!is_array($bundleOption->getSelections())) {
                    // deactivate stock if a required option is not available
                    $this->_defaultRow['is_available']  = false;
                    $this->_defaultRow['active_status'] = ShopgatePlugin::PRODUCT_STATUS_INACTIVE;
                }
            }

            if (!is_array($bundleOption->getSelections())) {
                // if option don't have any active selections, skip the option
                $bundleOption->setSelections(array());
            }

            foreach ($bundleOption->getSelections() as $selection) {
                /** @var Mage_Bundle_Model_Selection $selection */
                $selectionQty = max(1, (int)$selection->getSelectionQty());
                /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                $stockItem = $selection->getStockItem();
                /** @var $selection Mage_Catalog_Model_Product */
                $dif = $bundlePriceModel->getSelectionFinalPrice(
                    $product,
                    $selection,
                    $product->getQty(),
                    null,
                    true
                );

                /* @var $taxHelper Mage_Tax_Helper_Data */
                $dif = Mage::helper('tax')->getPrice($selection, $dif, true);

                $dif = $dif * 100;
                $dif = intval(round($dif, 0));

                $selectionName = $selectionQty > 1 ? $selectionQty . " x " : '';
                $selectionName .= $selection->getName();
                $value         = $selection->getSelectionId() . '=' . $selectionName . '=>' . $dif;

                $selectionQty = $stockItem->getQty();
                $selectionId  = $selection->getData('option')->getData('option_id');

                // by grouping option depending quantities
                if (!array_key_exists($selectionId, $selectionQuantities)) {
                    $selectionQuantities[$selectionId] = 0;
                }

                if ($stockItem->getManageStock() && $selection->isSaleable() && $selectionQty > 0) {
                    if ($selectionQuantities[$selectionId] !== null) {
                        $selectionQuantities[$selectionId] += $selectionQty;
                    }
                    $option['values'][] = $value;
                } else {
                    if (!$stockItem->getManageStock()) {
                        // reset quantities of options which have at least one item without stock management
                        $selectionQuantities[$selectionId] = null;
                        $option['values'][]                = $value;
                    } else {
                        if (!$selection->isSaleable()
                            && $stockItem->getBackorders() != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO
                        ) {
                            // reset quantities of options which have at least one item which is backorderable
                            $selectionQuantities[$selectionId] = null;
                            $option['values'][]                = $value;
                        } else {
                            // export not available options with 0 quantity
                            $selectionQuantities[$selectionId] = 0;
                            $option['values'][]                = $value;
                        }
                    }
                }

                // remove option qty elements with unlimited qty
                if ($selectionQuantities[$selectionId] === null) {
                    unset($selectionQuantities[$selectionId]);
                }
            }
            $options[] = $option;
        }

        // set bundle overall qty through option quantities
        $stock = count($selectionQuantities) ? min($selectionQuantities) : 0;

        // disable stock management if no option has a limited qty
        if (!count($selectionQuantities)) {
            $this->_defaultRow['use_stock'] = 0;
        }

        $this->_defaultRow['stock_quantity'] = $stock;
        $this->_defaultRow['has_options']    = !empty($options);

        $i = 1;
        foreach ($options as $option) {
            $this->_defaultRow["option_{$i}"]        = $option["key"];
            $this->_defaultRow["option_{$i}_values"] = implode("||", $option["values"]);
            $i++;
        }
    }
}
