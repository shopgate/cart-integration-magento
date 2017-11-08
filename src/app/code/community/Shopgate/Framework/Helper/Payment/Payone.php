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
class Shopgate_Framework_Helper_Payment_Payone extends Shopgate_Framework_Helper_Payment_Abstract
{
    /**
     * @param Payone_Core_Model_Config $config
     * @param string                   $methodType
     *
     * @return int
     */
    public function getMethodId($config, $methodType)
    {
        if ($methodType) {

            $methods = $config->getPayment()->getMethodsByType($methodType);

            if (!empty($methods)) {
                /** @var Payone_Core_Model_Config_Payment_Method $method */
                foreach ($methods as $method) {
                    $id = $method->getScope() === 'websites'
                        ? Mage::app()->getWebsite()->getId() : Mage::app()->getStore()->getStoreId();

                    if ($method->getScopeId() === $id) {
                        return $method->getId();
                    }
                }
                $error = $this->__('PayOne: could not match config scope with any of the active methods');
            } else {
                $error = $this->__('PayOne: could not find an enabled config for mapping: %s', $methodType);
            }
        } else {
            $error = $this->__('PayOne: method type not set in the called class');
        }

        ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);
        return false;
    }

}
