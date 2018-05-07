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
class Shopgate_Framework_Helper_Coupon extends Mage_Core_Helper_Abstract
{
    const COUPON_ATTRIUBTE_SET_NAME = 'Shopgate Coupon';
    const COUPON_PRODUCT_SKU = 'shopgate-coupon';
    const COUPON_TYPE_AFFILIATE = 'affiliate';

    /**
     * Const to detect coupons, which just represent cart rules
     */
    const CART_RULE_COUPON_CODE = '1';

    protected $_attributeSet = null;
    /** @var Shopgate_Framework_Helper_Coupon_Attribute */
    protected $attributeHelper;

    /**
     * Initialize like a DI
     */
    public function __construct()
    {
        $this->attributeHelper = Mage::helper('shopgate/coupon_attribute');
    }

    /**
     * Determines if a product is a Shopgate Coupon
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return boolean
     */
    public function isShopgateCoupon(Mage_Catalog_Model_Product $product)
    {
        $attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL
            && $attributeSetModel->getAttributeSetName() == self::COUPON_ATTRIUBTE_SET_NAME
        ) {
            return true;
        }

        return false;
    }

    /**
     * Sets missing product Attributes for virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function prepareShopgateCouponProduct(Mage_Catalog_Model_Product $product)
    {
        $product->setData('weight', 0);
        $product->setData('tax_class_id', $this->_getTaxClassId());
        $product->setData('attribute_set_id', $this->_getAttributeSetId());
        $product->setData('stock_data', $this->_getStockData());
        $product->setData('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $product->setData('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $product->setData('type_id', Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL);

        return $product;
    }

    /**
     * Offers a suitable tax_class_id for Shopgate-Coupons
     *
     * @return int
     */
    protected function _getTaxClassId()
    {
        return 0;
    }

    /**
     * Offers an attribute set for Shopgate-Coupons
     *
     * @return int
     */
    protected function _getAttributeSetId()
    {
        return $this->_getShopgateCouponAttributeSet()->getId();
    }

    /**
     * @return null|Mage_Eav_Model_Entity_Attribute_Set
     */
    protected function _getShopgateCouponAttributeSet()
    {
        if ($this->_attributeSet) {
            return $this->_attributeSet;
        }

        $set = $this->attributeHelper->getShopgateAttributeSet();
        if (!$set) {
            $set          = $this->attributeHelper->createShopgateCouponAttributeSet();
            $generalGroup = $this->attributeHelper->getGeneralGroup();
            $this->attributeHelper->createGroup($set->getId(), $generalGroup);
        }

        return $this->_attributeSet = $set;
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Set|null
     * @deprecated v2.9.60
     */
    protected function _createShopgateCouponAttributeSet()
    {
        return $this->attributeHelper->createShopgateCouponAttributeSet();
    }

    /**
     * Delivers an stock_item Dummy Object
     *
     * @return array
     */
    protected function _getStockData()
    {
        return array(
            "qty"                         => 1,
            "use_config_manage_stock"     => 0,
            "is_in_stock"                 => 1,
            "use_config_min_sale_qty"     => 1,
            "use_config_max_sale_qty"     => 1,
            "use_config_notify_stock_qty" => 1,
            "use_config_backorders"       => 1,
        );
    }

    /**
     * Create magento coupon product from object
     *
     * @param Varien_Object $coupon
     *
     * @return Mage_Catalog_Model_Product
     */
    public function createProductFromShopgateCoupon(Varien_Object $coupon)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $id      = $product->getIdBySku($coupon->getItemNumber());
        $product->load($id);

        $product = $this->prepareShopgateCouponProduct($product);
        $product->setPriceCalculation(false);
        $product->setData('name', $coupon->getName());
        $product->setData('sku', $coupon->getItemNumber());
        $product->setData('price', $coupon->getUnitAmountWithTax());
        $product->setData('store_id', Mage::app()->getStore()->getStoreId());

        if (!$product->getId()) {
            $oldStoreId = Mage::app()->getStore()->getId();
            Mage::app()->setCurrentStore(0);
            $product->save();
            Mage::app()->setCurrentStore($oldStoreId);
        }

        return $product;
    }

    /**
     * Check coupons for validation and apply shopping cart price rules to the cart
     *
     * @param Mage_Checkout_Model_Cart $mageCart
     * @param ShopgateCart             $cart
     * @param bool                     $useTaxClasses
     *
     * @return ShopgateExternalCoupon[]
     */
    public function checkCouponsAndCartRules(
        $mageCart,
        ShopgateCart $cart,
        $useTaxClasses
    ) {
        /* @var $mageQuote Mage_Sales_Model_Quote */
        /* @var $mageCart Mage_Checkout_Model_Cart */
        /* @var $mageCoupon Mage_SalesRule_Model_Coupon */
        /* @var $mageRule Mage_SalesRule_Model_Rule */
        $mageQuote = $mageCart->getQuote();
        $mageQuote->setTotalsCollectedFlag(false);

        $externalCoupons    = array();
        $validCouponsInCart = 0;
        $returnEmptyCoupon  = false;
        $showLabel          =
            Mage::getStoreConfigFlag(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOW_CARTRULE_LABEL);

        foreach ($cart->getExternalCoupons() as $coupon) {
            if ($coupon->getCode() === self::CART_RULE_COUPON_CODE) {
                $returnEmptyCoupon = true;
                continue;
            }

            $externalCoupon = $this->validateExternalCoupon($coupon, $mageQuote, $useTaxClasses);

            if ($externalCoupon->getIsValid()) {
                $validCouponsInCart++;
            }
            if ($validCouponsInCart > 1) {
                $errorCode = ShopgateLibraryException::COUPON_TOO_MANY_COUPONS;
                $externalCoupon->setIsValid(false);
                $externalCoupon->setNotValidMessage(ShopgateLibraryException::getMessageFor($errorCode));
            }
            $externalCoupons[] = $externalCoupon;
        }

        $mageQuote->collectTotals();
        $appliedRules = $mageQuote->getAppliedRuleIds();

        if ($validCouponsInCart == 0 && !empty($appliedRules)) {
            try {
                $label  = $mageQuote->getShippingAddress()->getDiscountDescription();
                $coupon = new ShopgateExternalCoupon();
                $coupon->setIsValid(true);
                $coupon->setCode(self::CART_RULE_COUPON_CODE);
                $coupon->setName($showLabel ? $this->getFormattedRuleLabel($label) : $this->getFormattedRuleLabel(''));
                if ($useTaxClasses) {
                    $amountCoupon = $mageQuote->getSubtotal() - $mageQuote->getSubtotalWithDiscount();
                    $coupon->setAmountGross($amountCoupon);
                } else {
                    $amountCoupon = $mageQuote->getBaseSubtotal() - $mageQuote->getBaseSubtotalWithDiscount();
                    $coupon->setAmountNet($amountCoupon);
                }
                $coupon->setIsFreeShipping($this->shippingShouldBeFree($mageQuote));
                $coupon->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());
                if ($amountCoupon || $coupon->getIsFreeShipping()) {
                    $externalCoupons[] = $coupon;
                    $returnEmptyCoupon = false;
                }
            } catch (Exception $e) {
                ShopgateLogger::getInstance()->log(
                    "Could not add rule with id " . $appliedRules . " to quote",
                    ShopgateLogger::LOGTYPE_DEBUG
                );
            }
        }

        if ($returnEmptyCoupon) {
            $coupon = new ShopgateExternalCoupon();
            $coupon->setCode(self::CART_RULE_COUPON_CODE);
            $coupon->setName($this->getFormattedRuleLabel(null));
            $coupon->setIsValid(false);
            $externalCoupons[] = $coupon;
        }

        foreach ($externalCoupons as $externalCoupon) {
            $couponInfo             = $externalCoupon->getInternalInfo();
            $couponInfo             = (array)Mage::helper('shopgate')->getConfig()->jsonDecode($couponInfo);
            $couponInfo['rule_ids'] = $appliedRules;
            $externalCoupon->setInternalInfo(Mage::helper('shopgate')->getConfig()->jsonEncode($couponInfo));
        }

        return $externalCoupons;
    }

    /**
     * If there is still at least one non free shipping method, we shouldn't return a freeshipping coupon
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    protected function shippingShouldBeFree(Mage_Sales_Model_Quote $quote)
    {
        foreach ($quote->getShippingAddress()->getAllShippingRates() as $rate) {
            if ($rate->getPrice()) {
                return false;
            }
        }

        return (bool)$quote->getShippingAddress()->getFreeShipping();
    }

    /**
     * Checks a coupon for validation
     *
     * @param ShopgateExternalCoupon $coupon
     * @param Mage_Sales_Model_Quote $mageQuote
     * @param bool                   $useTaxClasses
     *
     * @return ShopgateExternalCoupon
     * @throws ShopgateLibraryException
     */
    public function validateExternalCoupon($coupon, $mageQuote, $useTaxClasses)
    {
        /** @var ShopgateExternalCoupon $coupon */
        $externalCoupon = new ShopgateExternalCoupon();
        $externalCoupon->setIsValid(true);
        $externalCoupon->setCode($coupon->getCode());

        try {
            $mageQuote->setCouponCode($coupon->getCode());
            $mageQuote->setTotalsCollectedFlag(false)->collectTotals();
        } catch (Exception $e) {
            $externalCoupon->setIsValid(false);
            $externalCoupon->setNotValidMessage($e->getMessage());
        }

        if (Mage::helper('shopgate/config')->getIsMagentoVersionLower1410()) {
            $mageRule   = Mage::getModel('salesrule/rule')->load($coupon->getCode(), 'coupon_code');
            $mageCoupon = $mageRule;
        } else {
            $mageCoupon = Mage::getModel('salesrule/coupon')->load($coupon->getCode(), 'code');
            $mageRule   = Mage::getModel('salesrule/rule')->load($mageCoupon->getRuleId());
        }

        if ($mageRule->getId() && $mageQuote->getCouponCode()) {
            $couponInfo['coupon_id'] = $mageCoupon->getId();
            $couponInfo['rule_id']   = $mageRule->getId();

            $externalCoupon->setIsFreeShipping($this->shippingShouldBeFree($mageQuote));
            $externalCoupon->setInternalInfo(Mage::helper('shopgate')->getConfig()->jsonEncode($couponInfo));

            if ($useTaxClasses) {
                $amountCoupon = $mageQuote->getSubtotal() - $mageQuote->getSubtotalWithDiscount();
                $externalCoupon->setAmountGross($amountCoupon);
            } else {
                $amountCoupon = $mageQuote->getBaseSubtotal() - $mageQuote->getBaseSubtotalWithDiscount();
                $externalCoupon->setAmountNet($amountCoupon);
            }

            if (!$amountCoupon && !$externalCoupon->getIsFreeShipping()) {
                $externalCoupon->setIsValid(0);
                $externalCoupon->setAmount(0);
                $externalCoupon->setNotValidMessage(
                    Mage::helper('shopgate')->__('Coupon code "%s" is not valid.', $coupon->getCode())
                );
            }
        } else {
            $externalCoupon->setIsValid(0);
            $externalCoupon->setAmount(0);
            $externalCoupon->setNotValidMessage(
                Mage::helper('shopgate')->__('Coupon code "%s" is not valid.', $coupon->getCode())
            );
        }
        $externalCoupon->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());

        return $externalCoupon;
    }

    /**
     * Remove CartRule coupons from an array
     *
     * @param ShopgateExternalCoupon[] $coupons
     *
     * @return ShopgateExternalCoupon[] $coupons
     */
    public function removeCartRuleCoupons($coupons)
    {
        return $this->removeCouponsByCode($coupons, self::CART_RULE_COUPON_CODE);
    }

    /**
     * Remove Affiliate coupons from an array
     *
     * @param ShopgateExternalCoupon[] $coupons
     *
     * @return ShopgateExternalCoupon[] $coupons
     */
    public function removeAffiliateCoupons($coupons)
    {
        return $this->removeCouponsByCode($coupons, self::COUPON_TYPE_AFFILIATE);
    }

    /**
     * Safely merge coupons and remove duplicate CODE coupons
     *
     * @param ShopgateExternalCoupon[] $coupons
     * @param ShopgateExternalCoupon[] $affiliateCoupons
     *
     * @return ShopgateExternalCoupon[]
     */
    public function mergeAffiliateCoupon($coupons, $affiliateCoupons)
    {
        foreach ($coupons as $key => $coupon) {
            foreach ($affiliateCoupons as $affiliateCoupon) {
                if ($coupon->getCode() === $affiliateCoupon->getCode()) {
                    unset($coupons[$key]);
                }
            }
        }

        return array_merge($coupons, $affiliateCoupons);
    }

    /**
     * Remove coupons with a specific code from an array
     *
     * @param ShopgateExternalCoupon[] $coupons
     * @param string                   $code
     *
     * @return ShopgateExternalCoupon[] $filteredCoupons
     */
    public function removeCouponsByCode($coupons, $code)
    {
        $filteredCoupons = array();
        foreach ($coupons as $coupon) {
            /* @var $coupon ShopgateExternalCoupon */
            if ($coupon->getCode() !== $code) {
                $filteredCoupons[] = $coupon;
            }
        }

        return $filteredCoupons;
    }

    /**
     * Formats the label of the cart rule if it is provided
     *
     * @param null | string $label - label to format, default to "Discount" if empty
     *
     * @return string
     */
    public function getFormattedRuleLabel($label)
    {
        return empty($label)
            ? Mage::helper('sales')->__('Discount')
            : Mage::helper('sales')->__('Discount (%s)', $label);
    }
}
