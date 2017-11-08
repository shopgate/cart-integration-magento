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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_Magestore_Validator
    extends Shopgate_Framework_Model_Modules_Affiliate_Validator
{
    const MODULE_CONFIG = 'Magestore_Affiliateplus';
    const XML_CONFIG_ENABLED = 'affiliateplus/general/enable';
    const MIN_SUPPORTED_VER = '0.4.3';

    protected $validParams = array(
        'acc' => false
    );

    /**
     * Rewrite of original method to accommodate custom keys
     * defined in the modules system > configuration section.
     * Since it only has one parameter, we can remove the old
     * one.
     */
    public function getValidParams()
    {
        if ($this->isModuleActive() && $this->aboveMinVersion() && empty($this->scriptRan)) {
            $param = Mage::helper('affiliateplus/url')->getPersonalUrlParameter();
            if (!empty($param) && !isset($this->validParams[$param])) {
                $this->validParams[$param] = false;
                unset($this->validParams['acc']);
            }
            $this->scriptRan = true;
        }

        return parent::getValidParams();
    }

    /**
     * Checks if installed module is above
     * the minimum supported version
     *
     * @return mixed
     */
    public function aboveMinVersion()
    {
        $config = $this->getConstant('MODULE_CONFIG');
        /** @noinspection PhpUndefinedFieldInspection */
        $version = Mage::getConfig()->getModuleConfig($config)->version;
        $result  = version_compare($version, self::MIN_SUPPORTED_VER, '>=');
        if (!$result) {
            ShopgateLogger::getInstance()->log('magestore version: ' . $version, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $result;
    }

    /**
     * Rewriting parameter check as we need to check
     * coupons as well. We do not have coupons passed
     * via constructor so we check it later.
     *
     * @inheritdoc
     */
    public function checkGenericValid()
    {
        return true;
    }
}
