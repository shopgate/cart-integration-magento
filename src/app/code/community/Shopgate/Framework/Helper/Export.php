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
class Shopgate_Framework_Helper_Export extends Mage_Core_Helper_Abstract
{
    /**
     * if images have a height and/or width lower than this, the image will not be exported
     * as long as shopgate/export/export_lowres_image is 0
     */
    const IMAGE_EXPORT_MINRESOLUTION = 150;
    /**
     * @var null
     */
    protected $_weightFactor = null;

    /**
     * @param float $price
     *
     * @return float
     */
    public function convertPriceCurrency($price)
    {
        $baseCurrencyCode    = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::helper('shopgate')->getConfig()->getCurrency();
        $convertedPrice      = Mage::helper('directory')->currencyConvert(
            $price,
            $baseCurrencyCode,
            $currentCurrencyCode
        );

        return $convertedPrice;
    }

    /**
     * Get the price for catalog rules
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return float|null
     */
    public function calcProductPriceRule(Mage_Catalog_Model_Product $product)
    {
        $rulePrice = null;
        if (Mage::helper("shopgate/config")->getIsMagentoVersionLower15()) {
            $pId       = $product->getId();
            $storeId   = $product->getStoreId();
            $date      = Mage::app()->getLocale()->storeTimeStamp($storeId);
            $wId       = Mage::app()->getStore($storeId)->getWebsiteId();
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                             ->getRulePrice($date, $wId, 0, $pId);

        } else {
            $rulePrice = Mage::getModel("catalogrule/rule")->calcProductPriceRule($product, $product->getPrice());
        }

        return $rulePrice;
    }

    /**
     * Helper Function to get ConvertFactor for Weight conversion to gram
     *
     * @return float|int
     */
    public function getWeightFactor()
    {
        if (!$this->_weightFactor) {
            $this->_weightFactor = 1000;
            $lbCountry           = array("US", "GB");
            $country             = Mage::getStoreConfig(
                "general/country/default",
                Mage::helper('shopgate')->getConfig()->getStoreViewId()
            );
            $weightUnit          = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_WEIGHT_UNIT);

            if ($weightUnit == 0) {
                if (in_array($country, $lbCountry)) {
                    $this->_weightFactor = Shopgate_Framework_Model_Shopgate_Plugin::CONVERT_POUNDS_TO_GRAM_FACTOR;
                }
            } else {
                switch ($weightUnit) {
                    case Shopgate_Framework_Model_System_Config_Source_Weight_Units::WEIGHT_UNIT_KG:
                        $this->_weightFactor = 1000;
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Weight_Units::WEIGHT_UNIT_GRAMM:
                        $this->_weightFactor = 1;
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Weight_Units::WEIGHT_UNIT_POUND:
                        $this->_weightFactor = Shopgate_Framework_Model_Shopgate_Plugin::CONVERT_POUNDS_TO_GRAM_FACTOR;
                        break;
                    case Shopgate_Framework_Model_System_Config_Source_Weight_Units::WEIGHT_UNIT_OUNCE:
                        $this->_weightFactor = Shopgate_Framework_Model_Shopgate_Plugin::CONVERT_OUNCES_TO_GRAM_FACTOR;
                        break;
                }
            }
        }

        return $this->_weightFactor;
    }

    /**
     * calculates price for product options base on product and magento settings
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float                      $price
     *
     * @return float
     */
    public function getOptionPrice($product, $price)
    {
        $priceInclTax = Mage::helper('tax')->getPrice($product, $price, true);
        $priceExclTax = Mage::helper('tax')->getPrice($product, $price);

        $valuePrice = (Mage::helper('tax')->displayPriceIncludingTax())
            ? $priceInclTax
            : $priceExclTax;

        return $valuePrice;
    }

    /**
     * Returns whether product has custom option of unsupported type or not.
     * (only if option is required)
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function productHasRequiredUnsupportedOptions($product)
    {
        $options = $product->getOptions();
        foreach ($options as $option) {
            /** @var $option Mage_Catalog_Model_Product_Option */
            if ($option->getIsRequire() && $this->mapInputType($option->getType()) === false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $mageType
     *
     * @return string|bool
     */
    public function mapInputType($mageType)
    {
        switch ($mageType) {
            case 'select':
            case 'drop_down':
            case 'radio':
            case 'checkbox':
            case 'multiple':
            case 'multi':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_SELECT;
                break;
            case 'field':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TEXT;
                break;
            case 'area':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_AREA;
                break;
            case 'date':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_DATE;
                break;
            case 'date_time':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_DATETIME;
                break;
            case 'time':
                $inputType = Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TIME;
                break;
            default:
                $inputType = false;
        }

        return $inputType;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $parentCategory
     *
     * @return string
     */
    public function getCategoryNumberForGroupProduct(Mage_Catalog_Model_Product $product, $parentCategory)
    {
        return "gp" . $product->getId() . "_" . $parentCategory;
    }


    /**
     * Parse url
     *
     * @param string $url
     *
     * @return string
     */
    public function parseUrl($url)
    {
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DEBUG_HTUSER)
            && Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DEBUG_HTPASS)
        ) {

            $replacement = "http://";
            $replacement .= urlencode(
                Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DEBUG_HTUSER)
            );
            $replacement .= ":";
            $replacement .= urlencode(
                Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DEBUG_HTPASS)
            );
            $replacement .= "@";

            $url = preg_replace("/^http:\/\//i", $replacement, $url, 1);
        }

        return $url;
    }

    /**
     * return images for a product according to settings
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return mixed
     */
    public function getMediaImages($product)
    {
        $mediaGallery  = $this->_getAllImages($product);
        $sortedGallery = array();
        if ($mediaGallery) {
            foreach ($mediaGallery as $image) {
                $imageConfig =
                    Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_FIRST_PRODUCT_IMAGE);
                if (($imageConfig == 'thumbnail' && $image->getFile() == $product->getThumbnail())
                    || ($imageConfig == 'base' && $image->getFile() == $product->getImage())
                    || ($imageConfig == 'small' && $image->getFile() == $product->getSmallImage())
                ) {
                    $sortedGallery[-1] = $image;
                } else {
                    if (!array_key_exists($image->getPosition(), $sortedGallery)) {
                        $sortedGallery[$image->getPosition()] = $image;
                    } else {
                        $sortedGallery[] = $image;
                    }

                }
            }
        }
        ksort($sortedGallery);

        return $sortedGallery;
    }


    /**
     * Retrieve media gallery images, depending on config settings
     * - with the possibility not to ignore the excluded ones
     * - with the possibility to ignore the small ones
     *
     * @see Mage_Catalog_Model_Product::getMediaGalleryImages()
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return mixed
     */
    protected function _getAllImages($product)
    {
        $images = new Varien_Data_Collection();
        if (!$product->hasData('media_gallery_images') && is_array($product->getMediaGallery('images'))) {
            foreach ($product->getMediaGallery('images') as $image) {
                // ignore disabled images
                if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_EXCLUDED_IMAGES)
                    && $image['disabled']
                ) {
                    continue;
                }
                $image['url']  = $product->getMediaConfig()->getMediaUrl($image['file']);
                $image['id']   = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                // ignore small images
                if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_LOWRES_IMAGES)
                    && file_exists($image['path'])
                ) {
                    list($width, $height) = getimagesize($image['path']);
                    if ($width < self::IMAGE_EXPORT_MINRESOLUTION || $height < self::IMAGE_EXPORT_MINRESOLUTION) {
                        continue;
                    }
                }
                $images->addItem(new Varien_Object($image));
            }
        }

        return $images;
    }

    /**
     * Fills the item-array available_text field
     * <strong>Note:</strong> the function isAvailable()  on the Product-Object is only available in Magento-Version >= 1.5
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $storeViewId
     *
     * @return string
     */
    public function getAvailableText($product, $storeViewId)
    {
        $availableText = '';
        $attributeCode = Mage::getStoreConfig(
            Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_AVAILABLE_TEXT_ATTRIBUTE_CODE
        );

        if ($attributeCode) {
            $attributeInputType = $product->getResource()->getAttribute($attributeCode)->getFrontendInput();

            $rawValue = $product
                ->getResource()
                ->getAttributeRawValue($product->getId(), $attributeCode, $storeViewId);

            switch ($attributeInputType) {
                case 'select':
                    /** @var Mage_Eav_Model_Entity_Attribute_Option $attr */
                    $attr = Mage::getModel('eav/entity_attribute_option')
                                ->getCollection()
                                ->setStoreFilter($storeViewId)
                                ->join('attribute', 'attribute.attribute_id=main_table.attribute_id', 'attribute_code')
                                ->addFieldToFilter('main_table.option_id', array('eq' => $rawValue))
                                ->getFirstItem();

                    if ($attr) {
                        $availableText = ($attr->getStoreValue()) ? $attr->getStoreValue() : $attr->getValue();
                    }
                    break;
                case 'date' :
                    $availableText = Mage::helper('core')->formatDate($rawValue, 'medium', false);
                    break;
                case 'text':
                    $availableText = $rawValue;
                    break;
                default:

            }
        }

        if (!$availableText) {
            /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
            $stockItem   = $product->getStockItem();
            $isAvailable = $stockItem->checkQty(1) && $product->isSaleable();
            if ($product->isComposite()) {
                $isAvailable = true;
            }

            if ($isAvailable && $stockItem->getIsInStock()) {
                if ($stockItem->getManageStock()
                    && $stockItem->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY
                    && $stockItem->getQty() <= 0
                ) {
                    $availableText = Mage::helper('shopgate')->__('Item will be backordered');
                } else {
                    $availableText = Mage::helper('shopgate')->__('In stock');
                }
            } else {
                $availableText = Mage::helper('shopgate')->__('Out of stock');
            }

        }

        return $availableText;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $parentItem
     *
     * @return string
     */
    public function createFullDescription($product, $parentItem = null)
    {
        $description = $this->_getProductDescription($product);
        if ($parentItem) {
            $parentDescription = $this->_getProductDescription($parentItem);
            switch (Mage::getStoreConfig(
                Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_VARIATION_DESCRIPTION
            )) {
                case 1:
                    $description = $parentDescription;
                    break;
                case 2:
                    $description = $parentDescription . "<br /><br />" . $description;
                    break;
                case 3:
                    $description = $description . "<br /><br />" . $parentDescription;
                    break;
            }
        }

        $processor = Mage::helper('cms')->getPageTemplateProcessor();

        return $processor->filter($description);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    protected function _getProductDescription($product)
    {
        $description      = "";
        $longDescription  = $product->getDescription();
        $shortDescription = $product->getShortDescription();

        // Convert description and/or short description with nl2br
        $convertDescription = Mage::helper('shopgate')->getConfig()->getConvertDescription(true);
        if (in_array("0", $convertDescription)) {
            $longDescription = nl2br($longDescription);
        } else {
            if (in_array("1", $convertDescription)) {
                $shortDescription = nl2br($shortDescription);
            }
        }

        switch (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_DESCRIPTION_TYPE)) {
            case 0:
                $description = $longDescription;
                break;
            case 1:
                $description = $shortDescription;
                break;
            case 2:
                $description = $longDescription;
                $description .= "<br /><br />";
                $description .= $shortDescription;
                break;
            case 3:
                $description = $shortDescription;
                $description .= "<br /><br />";
                $description .= $longDescription;
                break;
            case 4:
                $descriptionAttribute = Mage::getStoreConfig(
                    Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_DESCRIPTION_ATTR_CODE
                );

                $attributeCodes = explode(",", $descriptionAttribute);

                $counter = 0;
                foreach ($attributeCodes as $attributeCode) {
                    $attributeCode = trim($attributeCode);
                    if (!empty($attributeCode)) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                        $attribute    = $product->getResource()->getAttribute($attributeCode);
                        $description1 = $product->getData($attributeCode);
                        if ($attribute->getFrontend()->getConfigField('input') == 'multiselect') {
                            $description1 = $attribute->getFrontend()->getOption($description1);
                            if (is_array($description1)) {
                                $description1 = implode('<br />', $description1);
                            }
                        }
                        if (in_array("2", $convertDescription)) {
                            $description1 = nl2br($description1);
                        }
                        if (!empty($description1) && $counter > 0) {
                            $description .= "<br /><br />";
                        }
                        $description .= $description1;
                    }

                    $counter++;
                }
        }

        return $description;
    }

    /**
     * Create deep link for the product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $parentItem
     *
     * @return string
     */
    public function getDeepLink($product, $parentItem = null)
    {
        $deepLink = $product->getProductUrl(true);

        if ($parentItem && $product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
            $deepLink = $parentItem->getProductUrl(true);
        }

        return $this->parseUrl($deepLink);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getManufacturer($product)
    {
        $manufacturer = $product->getManufacturer();
        if ($manufacturer) {
            $manufacturer = $product->getResource()
                                    ->getAttribute('manufacturer')
                                    ->getSource()
                                    ->getOptionText($manufacturer);
        }

        return $manufacturer;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     *
     * @return int
     */
    public function getParentStockQuantity($product)
    {
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = $product->getStockItem();
        if (!$stockItem->hasStockQty()) {
            $stockItem->setStockQty(0);  // prevent possible recursive loop
            if (!$product->isComposite()) {
                $stockQty = $stockItem->getQty();
            } else {
                $stockQty         = null;
                $productsByGroups = $this->_getProductsToPurchaseByReqGroups($product);
                foreach ($productsByGroups as $productsInGroup) {
                    $qty = 0;
                    foreach ($productsInGroup as $childProduct) {
                        if ($childProduct->hasStockItem()) {
                            $qty += $childProduct->getStockItem()->getQty();
                        }
                    }
                    if (is_null($stockQty) || $qty < $stockQty) {
                        $stockQty = $qty;
                    }
                }
            }
            $stockQty = (float)$stockQty;
            if ($stockQty < 0 || !$stockItem->getManageStock()
                || !$stockItem->getIsInStock()
                || ($product && !$product->isSaleable())
            ) {
                $stockQty = 0;
            }
            $product->getStockItem()->setQty($stockQty);
        } else {
            $stockQty = ($product->isSalable()) ? $stockItem->getQty() : 0;
        }

        return $stockQty;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     *
     * @return array
     */
    protected function _getProductsToPurchaseByReqGroups($product)
    {
        $type          = $product->getTypeId();
        $productGroups = array();
        switch ($type) {
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                $productGroups = array($product->getTypeInstance(true)->getUsedProducts(null, $product));
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                $productGroups = $this->_getProductGroupsForBundle($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $productGroups = array($product->getTypeInstance(true)->getAssociatedProducts($product));
                break;
        }

        return $productGroups;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param  Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function _getProductGroupsForBundle($product)
    {
        $groups             = array();
        $allProducts        = array();
        $hasRequiredOptions = false;
        foreach ($product->getTypeInstance(true)->getOptions($product) as $option) {
            $groupProducts = array();
            foreach (
                $product->getTypeInstance()->getSelectionsCollection(array($option->getId()), $product) as $childProduct
            ) {
                $groupProducts[] = $childProduct;
                $allProducts[]   = $childProduct;
            }
            if ($option->getRequired()) {
                $groups[]           = $groupProducts;
                $hasRequiredOptions = true;
            }
        }
        if (!$hasRequiredOptions) {
            $groups = array($allProducts);
        }

        return $groups;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductVisibleInCategories($product)
    {
        if ($product->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
            && $product->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH
        ) {
            return true;
        }
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES)) {
            $storesToExport = Mage::helper('shopgate')->getConfig()->getExportStores(true);
            foreach ($storesToExport as $storeId) {
                $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                    $product->getId(),
                    'visibility',
                    $storeId
                );
                if (in_array(
                    $value,
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                    )
                )) {
                    return true;
                }
            }
        }

        return false;
    }
}
