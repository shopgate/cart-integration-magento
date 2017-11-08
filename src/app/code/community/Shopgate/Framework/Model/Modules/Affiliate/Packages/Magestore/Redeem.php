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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Redeem
{

    const CONFIG_IGNORE_CART_RULES = 'affiliate';

    /**
     * Sets up affiliate data to be pulled in totals collector
     *
     * @param Varien_Object $data   - array(
     *                              'quote' => Mage Quote,
     *                              'parameters' => valid get params,
     *                              'customer_id' => customer id this quote belongs to
     *                              'coupons' => possible coupons in the cart, we possibly wrongly assume only 1
     *                              )
     *
     * @return bool
     */
    public function setAffiliateData(Varien_Object $data)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote      = $data->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::QUOTE);
        $parameters = $data->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::PARAMS);
        $customerId = $data->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::CUSTOMER_ID);
        $coupons    = $data->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::COUPONS);
        $this->executeRuleDisableConfig();

        $couponCode = $this->affiliateCouponRetriever($coupons);
        if ($couponCode) {
            $quote->setCouponCode($couponCode);
        }

        /** @see Magestore_Affiliateplusprogram_Helper_Data::initProgram */
        if ($quote instanceof Mage_Sales_Model_Quote) {
            Mage::getSingleton('checkout/session')->replaceQuote($quote);
            Mage::getSingleton('checkout/cart')->setQuote($quote);
        }

        //Means we run the coupon observer when the coupon is an affiliate coupon, but it will clear param logic
        if ($couponCode) {
            $this->runCheckCartCouponObserver($couponCode);
        }

        Mage::getSingleton('customer/session')->setCustomerId($customerId);
        $accountCode = array_pop($parameters);
        $account     = Mage::getSingleton('affiliateplus/session')->getAccount();

        //Account code exists AND it's not the affiliate checking out
        if ($accountCode && $account->getIdentifyCode() != $accountCode) {
            $affiliateAccount = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
            $cookieName       = 'affiliateplus_account_code_';

            if ($affiliateAccount->getId()) {
                $cookie       = Mage::getSingleton('core/cookie');
                $currentIndex = $cookie->get('affiliateplus_map_index');

                for ($i = intval($currentIndex); $i > 0; $i--) {
                    if ($_COOKIE[$cookieName . $i] == $accountCode) {
                        $curI = intval($currentIndex);

                        for ($j = $i; $j < $curI; $j++) {
                            $cookieValue               = $cookie->get($cookieName . intval($j + 1));
                            $_COOKIE[$cookieName . $j] = $cookieValue;
                        }
                        $_COOKIE[$cookieName . $curI] = $accountCode;

                        return true;
                    }
                }
                $currentIndex                         = $currentIndex ? intval($currentIndex) + 1 : 1;
                $_COOKIE['affiliateplus_map_index']   = $currentIndex;
                $_COOKIE[$cookieName . $currentIndex] = $accountCode;

                return true;
            }
        }

        return false;
    }

    /**
     * Returns an export ready affiliate coupon, in case there is no
     * affiliate discount it returns false
     *
     * @param Varien_Object $params - array(
     *                              'quote' => Mage Quote,
     *                              'parameters' => valid get params,
     *                              'use_tax_classes' => bool of whether to use tax classes or no
     *                              )
     *
     * @return false|ShopgateExternalCoupon
     */
    public function retrieveCoupon(Varien_Object $params)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote  = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::QUOTE);
        $coupon = false;

        if ($quote->getData('affiliateplus_discount')) {
            //affiliate coupon program check
            $code     = Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code');
            $discount = abs($quote->getData('affiliateplus_discount'));
            $coupon   = new ShopgateExternalCoupon();
            $coupon->setIsValid(true);
            $coupon->setCode($code ? $code : Shopgate_Framework_Helper_Coupon::COUPON_TYPE_AFFILIATE);
            $coupon->setName('Affiliate Discount');
            $coupon->setIsFreeShipping(false);
            if (Mage::getStoreConfigFlag('tax/calculation/discount_tax')) {
                $coupon->setAmountGross($discount);
            } else {
                $coupon->setAmountNet($discount);
            }
            //GET param affiliate, not affiliate program coupon related
            if (!$code) {
                $paramKey = Shopgate_Framework_Model_Modules_Affiliate_Factory::PARAMS;
                $coupon->setInternalInfo(Zend_Json::encode(array($paramKey => $params->getData($paramKey))));
            } else {
                $coupon->setInternalInfo(Zend_Json::encode(array('coupon_id' => 0)));
            }

        } else {
            $message = 'Affiliate discount was not found in the quote';
            ShopgateLogger::getInstance()->log($message, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $coupon;
    }

    /**
     * Prompts to create a commission transaction in case
     * checkout_submit_all_after observer is not triggered
     * by our order import (some payment methods).
     * Note! that there are other observers in Magestore
     * Affiliate that could be triggered to create a
     * transaction.
     *
     * @param Varien_Object $params - array(
     *                              'sg_order' => Shopgate Order,
     *                              'mage_order' => Magento Order,
     *                              )
     *
     * @return Mage_Sales_Model_Order
     */
    public function promptCommission(Varien_Object $params)
    {
        $order    = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::MAGE_ORDER);
        $sgOrder  = $params->getData(Shopgate_Framework_Model_Modules_Affiliate_Factory::SG_ORDER);
        $customer = Mage::getModel('customer/customer');
        $customer->setData('website_id', Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($sgOrder->getMail());
        $order->setCustomerId($customer->getId());

        $observer = new Varien_Event_Observer();
        $observer->setData('orders', array($order));
        Mage::getModel('affiliateplus/observer')->checkout_submit_all_after($observer);

        return $order;
    }

    /**
     * Destroy cookies & session data when done
     */
    public function destroyCookies()
    {
        $cookie       = Mage::getSingleton('core/cookie');
        $currentIndex = $cookie->get('affiliateplus_map_index');
        $cookieName   = 'affiliateplus_account_code_';

        for ($i = intval($currentIndex); $i > 0; $i--) {
            $curI = intval($currentIndex);

            for ($j = $i; $j < $curI; $j++) {
                $cookieValue               = $cookie->get(
                    $cookieName . intval($j + 1)
                );
                $_COOKIE[$cookieName . $j] = $cookieValue;
            }
            unset($_COOKIE[$cookieName . $currentIndex]);
        }
        $currentIndex = $currentIndex ? intval($currentIndex) + 1 : 1;
        unset($_COOKIE['affiliateplus_map_index']);
        unset($_COOKIE[$cookieName . $currentIndex]);

        /**
         * Remove sessions created by Affiliate Coupon plugin
         * Also removes Quote from session, good for debugging purposes
         */
        $session = Mage::getSingleton('checkout/session');
        $session->unsetData('affiliate_coupon_code');
        $session->unsetData('affiliate_coupon_data');
        $session->clear();
    }

    /**
     * Checks if sale rules need to be disabled
     */
    private function executeRuleDisableConfig()
    {
        $allowDiscount = Mage::helper('affiliateplus/config')->getDiscountConfig('allow_discount');
        if ($allowDiscount === self::CONFIG_IGNORE_CART_RULES) {
            Mage::register('shopgate_disable_sales_rules', true, true);
        }
    }

    /**
     * Fake running the coupon post controller from cart page
     *
     * @param string $couponCode
     */
    private function runCheckCartCouponObserver($couponCode)
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('coupon_code', $couponCode);
        $response   = new Mage_Core_Controller_Response_Http();
        $controller = Mage::getControllerInstance('Mage_Core_Controller_Front_Action', $request, $response);
        $observer   = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Event(array('controller_action' => $controller)));
        Mage::getModel('affiliatepluscoupon/observer')->couponPostAction($observer);
    }

    /**
     * Checks if one of the coupons inside the cart is
     * an affiliate coupon and returns it
     *
     * @param ShopgateExternalCoupon[] $coupons
     *
     * @return false | string
     */
    private function affiliateCouponRetriever(array $coupons)
    {
        /** @var Magestore_Affiliateplus_Helper_Data $helper */
        $helper = Mage::helper('affiliateplus');
        foreach ($coupons as $coupon) {
            $code = $coupon->getCode();
            /** @var Magestore_Affiliateplus_Model_Account $account */
            $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
            if ($account->getId() && $helper->isAllowUseCoupon($code)) {
                return $coupon->getCode();
            }
        }

        return false;
    }
}
