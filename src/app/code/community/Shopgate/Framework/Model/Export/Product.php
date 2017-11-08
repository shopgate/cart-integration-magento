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
class Shopgate_Framework_Model_Export_Product extends Shopgate_Framework_Model_Export_Abstract
{

    /**
     * @var null
     */
    protected $_giftWrappingLabel = null;

    /**
     * get manufacturer suggested retail price
     *
     * @param $product
     *
     * @return float
     */
    public function getMsrp($product)
    {
        $msrp = $product->getMsrp();
        if ($msrp > 0) {
            $msrp = $this->_getExportHelper()->convertPriceCurrency($msrp);
            $msrp = round($msrp, 2);
            $msrp = number_format($msrp, 2, ".", "");
        } else {
            $msrp = null;
        }

        return $msrp;
    }

    /**
     * getting images as array
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $parentItem
     *
     * @return array
     */
    public function getImages($product, $parentItem = null)
    {
        $images = $this->_getProductImages($product);

        if ($parentItem) {
            $parentImages = $this->_getProductImages($parentItem);
            switch (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_VARIATION_IMAGE)) {
                case 1:
                    $images = $parentImages;
                    break;
                case 2:
                    $images = array_merge($parentImages, $images);
                    break;
                case 3:
                    $images = array_merge($images, $parentImages);
                    break;
                default:
                    //intentionally blank
            }
        }

        $images = array_unique($images);
        foreach ($images as &$image) {
            $image = ($image);
        }

        return $images;
    }


    /**
     * get product images
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function _getProductImages($product)
    {
        $images = array();
        if ($product) {
            $mediaGallery = $this->_getExportHelper()->getMediaImages($product);
            if (!empty($mediaGallery)) {
                foreach ($mediaGallery as $image) {
                    if ($image->getFile()) {
                        $images[] = $image->geturl();
                    }
                }
            }
        }

        return array_unique($images);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return mixed
     */
    public function getWeight($product)
    {
        return $product->getWeight() * $this->_getExportHelper()->getWeightFactor();
    }

    /**
     * get product name
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getProductName($product)
    {
        return trim($product->getName());
    }

    /**
     * @param string $priceType - 'percent' vs 'fixed'
     *
     * @return null|string
     */
    protected function _getOptionPriceType($priceType)
    {
        switch ($priceType) {
            case 'percent':
                $type = Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_PERCENT;
                break;
            case 'fixed':
                $type = Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_FIXED;
                break;
            default:
                $type = null;
        }

        return $type;
    }

    /**
     * @param string $type
     *
     * @return null|string
     */
    protected function _getInputType($type)
    {
        switch ($type) {
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_AREA;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_CHECKBOX;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_SELECT;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TEXT;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_MULTIPLE;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_RADIO;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE:
                $input = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_FILE;
                break;
            default:
                $input = null;
        }

        return $input;
    }

    /**
     * Copy of core format price
     *
     * @param        $price
     * @param int    $digits
     * @param string $decimalPoint
     * @param string $thousandPoints
     *
     * @return float|string
     */
    protected function _formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "")
    {
        $price = round($price, $digits);

        return number_format($price, $digits, $decimalPoint, $thousandPoints);
    }

    /**
     * Fetches attributes for properties column and filters by ignored attributes.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param                            $ignoredProductAttributeCodes
     * @param array                      $forcedProductAttributeCodes
     *
     * @return array
     */
    protected function _getProductProperties(
        $product,
        $ignoredProductAttributeCodes,
        $forcedProductAttributeCodes = array()
    ) {
        $properties = array();

        $cacheKey = 'product_type_' . $product->getTypeId() . '_attributes_' . $product->getAttributeSetId();
        $cache    = Mage::app()->getCacheInstance();
        $value    = $cache->load($cacheKey);

        if ($value !== false) {
            $attributes = unserialize($value);
        } else {
            $attributes = $product->getAttributes();
            $attrCache  = array();
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttributeCode();

                $isFilterable = Mage::getStoreConfigFlag('shopgate/export/filterable_attributes')
                    ? $attribute->getIsFilterable()
                    : false;

                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if ($attribute
                    && ($attribute->getIsVisibleOnFront() || $isFilterable
                        || in_array(
                            $code,
                            $forcedProductAttributeCodes
                        ))
                ) {
                    if (in_array($code, $ignoredProductAttributeCodes)
                        && !in_array($code, $forcedProductAttributeCodes)
                    ) {
                        continue;
                    }
                    $attrCache[$code] = array(
                        'id'    => $attribute->getId(),
                        'label' => $attribute->getStoreLabel($this->_getConfig()->getStoreViewId())
                    );
                }
            }
            $attributes = $attrCache;
            $cache->save(
                serialize($attrCache),
                $cacheKey,
                array(
                    'shopgate_export',
                    Mage_Core_Model_Mysql4_Collection_Abstract::CACHE_TAG,
                    Mage_Catalog_Model_Resource_Eav_Attribute::CACHE_TAG
                ),
                3600
            );
        }

        foreach ($attributes as $code => $data) {
            $value = $product->getResource()->getAttribute($code)->getFrontend()->getValue($product);
            if ($value) {
                $properties[$code] = "{$data['label']}=>{$value}";
            }
        }

        return $properties;
    }

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     *
     * @return float|int
     */
    protected function _getStackPriceMultiplicator($stockItem)
    {
        $priceMultiplier = 1;
        if ($stockItem->getEnableQtyIncrements()) {
            $stackQuantity = ceil($stockItem->getQtyIncrements());
            if ($stackQuantity > 1) {
                $priceMultiplier = $stackQuantity;
            }
        }

        return $priceMultiplier;
    }
}
