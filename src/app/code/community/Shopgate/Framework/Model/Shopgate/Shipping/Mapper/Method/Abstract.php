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
abstract class Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract extends Mage_Core_Model_Abstract
    implements Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Interface
{
    /**
     * xml path to store config value for shipping api
     */
    const XML_PATH_USPS_METHODS = 'shopgate/shipping/mapping_api_mage';

    /**
     * const for default shipping method
     */
    const DEFAULT_SHIPPING_METHOD = 'Default';

    /**
     * @var ShopgateCartBase
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_method;

    /**
     * Mapper initialization
     *
     * @param ShopgateCartBase $order
     *
     * @return $this
     */
    public function init(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log('# ' . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);
        $this->_order  = $order;
        $this->_method = $this->_fetchMethod($order);

        return $this;
    }

    /**
     * Predefined Getter for method
     * ready to be rewritten in subclasses
     */
    public function getMethod()
    {
        ShopgateLogger::getInstance()->log('# ' . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);
        if (is_null($this->_method) && is_object($this->_order)) {
            $this->_method = $this->_fetchMethod($this->_order);
        } else {
            if (is_null($this->_method) && is_null($this->_order)) {
                Mage::logException(new Exception('Error: model not initialized properly'));
            }
        }

        ShopgateLogger::getInstance()->log(
            "  Mapped shipping method is: '" . $this->_method . "'",
            ShopgateLogger::LOGTYPE_DEBUG
        );

        return $this->_method;
    }

    abstract protected function _fetchMethod(ShopgateCartBase $order);
}
