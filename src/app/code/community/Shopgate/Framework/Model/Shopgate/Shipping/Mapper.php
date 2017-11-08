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
class Shopgate_Framework_Model_Shopgate_Shipping_Mapper extends Mage_Core_Model_Abstract
{
    /**
     * Default carrier const
     */
    const DEFAULT_CARRIER = 'shopgate';

    /**
     * Plugin type returned in case of shipping methods
     * provided
     */
    const SHIPPING_TYPE_PLUGINAPI = 'PLUGINAPI';

    /**
     * @var
     */
    protected $_carrier;
    /**
     * @var string
     */
    protected $_method;
    /**
     * @var ShopgateCartBase
     */
    protected $_order;
    /**
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;

    /**
     * Initialize the model
     *
     * @param ShopgateCartBase               $order
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return Shopgate_Framework_Model_Shopgate_Shipping_Mapper
     */
    public function init(Mage_Sales_Model_Quote_Address $address, ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);

        $this->_order   = $order;
        $this->_address = $address;
        $this->_carrier = $this->_fetchCarrier($order);
        $this->_method  = $this->_fetchMethod($order);

        return $this;
    }

    /**
     * Internal helper to extract carrier
     *
     * @param ShopgateCartBase $order
     *
     * @return mixed
     */
    protected function _fetchCarrier(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        $mapper = Mage::getModel('shopgate/shopgate_shipping_mapper_carrier');

        return $mapper->init($order)->getCarrier();
    }

    /**
     * Internal helper to extract method
     *
     * @param ShopgateCartBase $order
     *
     * @return string
     */
    protected function _fetchMethod(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        /** @var Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract $mapper */
        $mapper = Mage::getModel('shopgate/shopgate_shipping_mapper_method_' . $this->getCarrier());
        if (!is_object($mapper)) {
            Mage::logException(
                new Exception('Error: no suitable Mapper Model found for carrier \'' . $this->getCarrier() . '\'')
            );
        }

        $method = $mapper->init($order)->getMethod();

        return $this->_validateMethod($method, $mapper);
    }

    /**
     * Getter for shipping carrier
     *
     * @return String
     */
    public function getCarrier()
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        if (is_null($this->_carrier) && is_object($this->_order)) {
            $this->_carrier = $this->_fetchCarrier($this->_order);
        } else {
            if (is_null($this->_carrier)) {
                Mage::throwException('Error: no carrier set');
            }
        }

        return $this->_carrier;
    }

    /**
     * Checks if the method is available
     * and returns a default method if not
     *
     * @param Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Interface $mapper
     * @param string                                                             $method
     *
     * @return string
     */
    protected function _validateMethod($method, $mapper)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        if (in_array($method, $mapper->getAvailableMethods())) {
            return $method;
        }
        Mage::logException(new Exception('could not match the given method \'' . $method . '\' to a available method'));
        $this->_carrier = self::DEFAULT_CARRIER;

        return Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Shopgate::DEFAULT_SHIPPING_METHOD;
    }

    /**
     * Getter for shipping method
     *
     * @return String
     */
    public function getMethod()
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        if (is_null($this->_method) && is_object($this->_order) && !is_null($this->_carrier)) {
            $this->_method = $this->_fetchMethod($this->_order);
        } else {
            if (is_null($this->_carrier)) {
                Mage::throwException('Error: no carrier set');
            }
        }

        return $this->_method;
    }
}
