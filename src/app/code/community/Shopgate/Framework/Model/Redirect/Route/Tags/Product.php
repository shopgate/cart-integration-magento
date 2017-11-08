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
class Shopgate_Framework_Model_Redirect_Route_Tags_Product extends Shopgate_Framework_Model_Redirect_Route_Tags_Generic
{
    /** @var ShopgateLogger */
    protected $logger;

    /**
     * Initializing params
     */
    public function __construct()
    {
        $this->logger = ShopgateLogger::getInstance();
    }

    /**
     * Generates page specific tags + generic tags
     *
     * @param string $pageTitle
     *
     * @return array
     */
    public function generate($pageTitle)
    {
        $tags = parent::generate($pageTitle);

        $product = $this->getCurrentProduct();
        if (!$product) {
            return $tags;
        }

        $categoryName  = $this->getCategoryName();
        $name          = $product->getData('name');
        $availableText = $product->isInStock() ? 'instock' : 'oos';
        $eanAttrCode   = Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EAN_ATTR_CODE);
        $ean           = $product->hasData($eanAttrCode) ? $product->getData($eanAttrCode) : '';
        $image         = $product->getMediaGalleryImages()->getFirstItem();
        $imageUrl      = is_object($image) ? $image->getData('url') : '';

        $description = $product->getData('short_description');
        if (strlen($description) > 140) {
            $description = substr($description, 0, 136) . ' ...';
        }

        $price           = $product->getData('price');
        $defaultCurrency = Mage::getStoreConfig('currency/options/default');
        $baseCurrency    = Mage::getStoreConfig('currency/options/base');
        if ($defaultCurrency != $baseCurrency) {
            $price = Mage::helper('directory')->currencyConvert($price, $baseCurrency, $defaultCurrency);
        }
        $priceIsGross = Mage::getStoreConfig('tax/calculation/price_includes_tax');
        /** @noinspection PhpUndefinedMethodInspection */
        $request = new Varien_Object(
            array(
                'country_id'        => Mage::getStoreConfig('tax/defaults/country'),
                'region_id'         => Mage::getStoreConfig('tax/defaults/region'),
                'postcode'          => Mage::getStoreConfig('tax/defaults/postcode'),
                'customer_class_id' => Mage::getModel('tax/calculation')->getDefaultCustomerTaxClass(),
                'product_class_id'  => $product->getData('tax_class_id'),
                'store'             => Mage::app()->getStore()
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

        $productTags = array(
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
            $productTags[Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_CURRENCY]        =
                $defaultCurrency;
            $productTags[Shopgate_Helper_Redirect_TagsGenerator::SITE_PARAMETER_PRODUCT_PRETAX_CURRENCY] =
                $defaultCurrency;
        }

        return array_merge($tags, $productTags);
    }

    /**
     * Helps pulling category name from
     * current product
     *
     * @return string
     */
    public function getCategoryName()
    {
        $categoryId = Mage::app()->getRequest()->getParam('category');

        return !empty($categoryId) ? Mage::getModel('catalog/category')->load($categoryId)->getName() : '';
    }

    /**
     * @return false | Mage_Catalog_Model_Product
     */
    protected function getCurrentProduct()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Mage::getModel('catalog/product')
                   ->setStoreId(Mage::app()->getStore()->getId())
                   ->load(Mage::app()->getRequest()->getParam('id'));
    }
}
