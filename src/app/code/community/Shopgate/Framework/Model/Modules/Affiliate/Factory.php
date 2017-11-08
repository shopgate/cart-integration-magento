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
class Shopgate_Framework_Model_Modules_Affiliate_Factory extends Shopgate_Framework_Model_Modules_Factory
{
    const PARAMS = 'parameters';
    const QUOTE = 'quote';
    const CUSTOMER_ID = 'customer_id';
    const SG_ORDER = 'sg_order';
    const MAGE_ORDER = 'mage_order';
    const COUPONS = 'coupons';

    /** @var Shopgate_Framework_Model_Modules_Affiliate_Router */
    private $router;
    /** @var array */
    private $validPackages = array();

    /**
     * @throws Exception
     */
    public function _construct()
    {
        parent::_construct();

        $route = next($this->_data);
        if (!$route instanceof Shopgate_Framework_Model_Interfaces_Modules_Router) {
            $error = Mage::helper('shopgate')->__('Incorrect class provided to: %s::_constructor()', get_class($this));
            ShopgateLogger::getInstance()->log($error);
            throw new Exception($error);
        }

        $this->router = $route;
    }

    /**
     * Runs the initial setup functionality. Usually setting
     * up parameters before the totals collector runs.
     *
     * @param Mage_Sales_Model_Quote | null $quote
     *
     * @return bool
     */
    public function setUp($quote = null)
    {
        $result = false;
        foreach ($this->getAllValidPackages() as $package) {
            $redeemer = $this->getRouter()->setDirectoryName($package)->getRedeemer();
            if ($redeemer && method_exists($redeemer, 'setAffiliateData')) {
                $data   = array(
                    self::PARAMS      => $this->getRouter()->setDirectoryName($package)->getValidator()->getValidParams(
                    ),
                    self::CUSTOMER_ID => $this->getSgOrder()->getExternalCustomerId(),
                    self::QUOTE       => $quote,
                    self::COUPONS     => $this->getSgOrder()->getExternalCoupons()
                );
                $result = $redeemer->setAffiliateData(new Varien_Object($data));
                ShopgateLogger::getInstance()->log("Affiliate setUp with {$package}", ShopgateLogger::LOGTYPE_DEBUG);
                ShopgateLogger::getInstance()->log(print_r($data[self::PARAMS], true), ShopgateLogger::LOGTYPE_DEBUG);
            }
        }

        return $result;
    }

    /**
     * Retrieves a Shopgate coupons to export in check_cart call
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return ShopgateExternalCoupon[]
     */
    public function redeemCoupon(Mage_Sales_Model_Quote $quote)
    {
        $coupons = array();
        foreach ($this->getAllValidPackages() as $package) {
            $redeemer = $this->getRouter()->setDirectoryName($package)->getRedeemer();
            if ($redeemer && method_exists($redeemer, 'retrieveCoupon')) {
                $data   = array(
                    self::PARAMS => $this->getRouter()->setDirectoryName($package)->getValidator()->getValidParams(),
                    self::QUOTE  => $quote
                );
                $coupon = $redeemer->retrieveCoupon(new Varien_Object($data));
                if ($coupon) {
                    $coupons[] = $coupon;
                }
                ShopgateLogger::getInstance()->log("Affiliate Coupon /w {$package}", ShopgateLogger::LOGTYPE_DEBUG);
            }
        }

        return $coupons;
    }

    /**
     * Trigger affiliate commission retrieval
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function promptCommission(Mage_Sales_Model_Order $order)
    {
        foreach ($this->getAllValidPackages() as $package) {
            $redeemer = $this->getRouter()->setDirectoryName($package)->getRedeemer();
            if ($redeemer && method_exists($redeemer, 'promptCommission')) {
                $data = array(
                    self::MAGE_ORDER => $order,
                    self::SG_ORDER   => $this->getSgOrder()
                );
                $redeemer->promptCommission(new Varien_Object($data));
                ShopgateLogger::getInstance()->log("Affiliate commission /w {$package}", ShopgateLogger::LOGTYPE_DEBUG);
            }
        }
    }

    /**
     * Retrieves allowed params that can be passed to our Merchant API
     * as GET params in the redirect
     *
     * @see Shopgate_Framework_Model_Modules_Affiliate_Router::getValidAffiliateParameterKeys
     *
     * @return array
     */
    public function getModuleTrackingParameters()
    {
        $params = array();
        foreach ($this->getAllValidPackages() as $package) {
            $rawParams = $this->getRouter()->setDirectoryName($package)->getValidator()->getValidParams();
            $keys      = array_keys($rawParams);
            $params    = array_merge($params, $keys);
        }

        return $params;
    }

    /**
     * Destroys cookies
     */
    public function destroyCookies()
    {
        foreach ($this->getAllValidPackages() as $package) {
            $redeemer = $this->getRouter()->setDirectoryName($package)->getRedeemer();
            if ($redeemer && method_exists($redeemer, 'destroyCookies')) {
                ShopgateLogger::getInstance()->log("Affiliate cookies /w {$package}", ShopgateLogger::LOGTYPE_DEBUG);
                $redeemer->destroyCookies();
            }
        }
    }

    /**
     * Returns all the valid package folder names.
     * Disabled affiliate routing when compiler is enabled as it
     * would be inefficient to derive routing
     *
     * @return array - e.g. array('Magestore')
     */
    public function getAllValidPackages()
    {
        $file = dirname(__FILE__);
        if (strpos($file, 'includes/src') !== false || !empty($this->validPackages)) {
            return $this->validPackages;
        }

        $iterator = new DirectoryIterator($file . DIRECTORY_SEPARATOR . 'Packages' . DIRECTORY_SEPARATOR);
        foreach ($iterator as $location) {
            if (!$location->isDot() && $location->isDir()) {
                if ($this->getRouter()->setDirectoryName($location->getBasename())->getValidator()->isValid()) {
                    $this->validPackages[] = $location->getBasename();
                }
            }
        }

        if (empty($this->validPackages)) {
            ShopgateLogger::getInstance()->log(
                'None of the affiliate packages were valid',
                ShopgateLogger::LOGTYPE_DEBUG
            );
        }

        return $this->validPackages;
    }

    /**
     * @return Shopgate_Framework_Model_Modules_Affiliate_Router
     */
    private function getRouter()
    {
        return $this->router;
    }
}
