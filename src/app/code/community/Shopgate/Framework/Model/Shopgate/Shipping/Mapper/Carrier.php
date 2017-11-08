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
class Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Carrier extends Mage_Core_Model_Abstract
{
    /**
     * xml path to store view config value
     */
    const XML_PATH_CARRIER_DATA = 'shopgate/shipping/carriers';

    /**
     * @var ShopgateCartBase
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_carrier;

    /**
     * @param ShopgateCartBase $order
     *
     * @return $this
     */
    public function init(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        $this->_order   = $order;
        $this->_carrier = $this->_fetchCarrier($order->getShippingGroup());

        return $this;
    }

    /**
     * @param $shipping_group
     *
     * @return mixed
     */
    protected function _fetchCarrier($shipping_group)
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        $data = Mage::getStoreConfig(self::XML_PATH_CARRIER_DATA);

        if (!in_array($shipping_group, array_keys($data))) {
            ShopgateLogger::getInstance()->log(
                "  Given shipping_group '$shipping_group' could not be mapped properly: Fallback to '"
                . $data['Default'] . " as carrier'",
                ShopgateLogger::LOGTYPE_DEBUG
            );

            return $data['Default'];
        }

        return $data[$shipping_group];
    }

    /**
     * @return mixed
     */
    public function getCarrier()
    {
        ShopgateLogger::getInstance()->log("# " . __FUNCTION__, ShopgateLogger::LOGTYPE_DEBUG);
        ShopgateLogger::getInstance()->log(
            "  Shipping carrier is mapped to: '" . $this->_carrier . "'",
            ShopgateLogger::LOGTYPE_DEBUG
        );

        return $this->_carrier;
    }
}
