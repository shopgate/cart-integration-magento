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
class Shopgate_Framework_Model_Modules_Affiliate_Router implements Shopgate_Framework_Model_Interfaces_Modules_Router
{
    const VALIDATOR = 'validator';
    const REDEEM = 'redeem';
    const CLASS_SHORT_PATH = 'shopgate/modules_affiliate_packages';

    /** @var ShopgateOrder | ShopgateCart */
    private $sgOrder;
    /** @var string $directoryName */
    private $directoryName;

    /**
     * @param array $data - should contain an array of affiliate params in first element
     *
     * @throws Exception
     */
    public function __construct(array $data)
    {
        $sgOrder = current($data);
        if (!$sgOrder instanceof ShopgateCartBase) {
            $error = Mage::helper('shopgate')->__('Incorrect class provided to: %s::_constructor()', get_class($this));
            ShopgateLogger::getInstance()->log($error);
            throw new Exception($error);
        }
        $this->sgOrder = $sgOrder;
    }

    /**
     * @return Shopgate_Framework_Model_Modules_Affiliate_Validator | Shopgate_Framework_Model_Modules_Validator
     */
    public function getValidator()
    {
        $trackingParams = $this->sgOrder->getTrackingGetParameters();
        $validator      = $this->getPluginModel(self::VALIDATOR, array($trackingParams));

        return $validator ? $validator : Mage::getModel('shopgate/modules_validator', array($trackingParams));
    }

    /**
     * Retrieves the redeemer class
     *
     * @return false | Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Redeem
     */
    public function getRedeemer()
    {
        return $this->getPluginModel(self::REDEEM);
    }

    /**
     * Name of the package directory
     *
     * @param string $name
     *
     * @return $this
     */
    public function setDirectoryName($name)
    {
        $this->directoryName = $name;

        return $this;
    }

    /**
     * Retrieve the directory name
     *
     * @return string
     */
    public function getDirectoryName()
    {
        return $this->directoryName;
    }

    /**
     * Retrieves a model to access
     *
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    private function getPluginModel($path, $params = array())
    {
        return $this->initAffiliateClass($this->getDirectoryName(), $path, $params);
    }

    /**
     * Small helper that concatenate the first two params given
     * with an underscore & loads the model
     *
     * @param string $partOne - first part of class name
     * @param string $partTwo - second part of class name
     * @param array  $data    - constructor params
     *
     * @return mixed
     */
    private function initAffiliateClass($partOne, $partTwo, $data = array())
    {
        $partOne = self::CLASS_SHORT_PATH . '_' . $this->lowerCaseFirst($partOne);

        return @ Mage::getModel($partOne . '_' . $this->lowerCaseFirst($partTwo), $data);
    }

    /**
     * For compatibility with PHP 5.2
     *
     * @param string $str
     *
     * @return string
     */
    private function lowerCaseFirst($str)
    {
        if (function_exists('lcfirst') !== false) {
            return lcfirst($str);
        }
        $str[0] = strtolower($str[0]);

        return $str;
    }
}
