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
class Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Usps
    extends Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract
{
    /**
     * Serve the method name
     *
     * @param ShopgateCartBase $order
     *
     * @return string
     */
    protected function _fetchMethod(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log("# " . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);
        $response = $order->getShippingInfos()->getApiResponse();

        if (!isset($response['MailService'])) {
            try {
                $methodOriginCode = $response['SvcDescription'];
            } catch (Exception $e) {
                Mage::logException(new Exception('No shipping_method in response available'));
                ShopgateLogger::getInstance()->log(
                    "  There is no shipping method available in the response [Not Mailservice nor SvcDescription contains any data]",
                    ShopgateLogger::LOGTYPE_DEBUG
                );

                /* TODO not only the method should get corrected also the carrier */

                return Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract::DEFAULT_SHIPPING_METHOD;
            }
        } else {
            $methodOriginCode = $response['MailService'];
        }

        return $this->_mapShippingMethod($methodOriginCode);
    }

    /**
     * @return array $collection
     */
    public function getAvailableMethods()
    {
        ShopgateLogger::getInstance()->log("# " . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);

        return explode(',', Mage::getStoreConfig('carriers/usps/allowed_methods'));
    }

    /**
     * @return string $method
     */
    public function getDefaultMethod()
    {
        ShopgateLogger::getInstance()->log("# " . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);

        return Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract::DEFAULT_SHIPPING_METHOD;
    }

    /**
     * Clean service name from unsupported strings and characters
     *
     * @param  string $name
     *
     * @return string
     */
    protected function _filterServiceName($name)
    {
        ShopgateLogger::getInstance()->log("# " . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);
        $name = (string)preg_replace(
            array('~<[^/!][^>]+>.*</[^>]+>~sU', '~\<!--.*--\>~isU', '~<[^>]+>~is'),
            '',
            html_entity_decode($name)
        );

        return str_replace('*', '', $name);
    }

    /**
     * Maps incoming USPS method to internal method_id.
     *
     * @param   string $method
     *
     * @return string
     */
    protected function _mapShippingMethod($method)
    {
        $method = $this->_filterServiceName($method);

        /** @var Mage_Usa_Model_Shipping_Carrier_Usps_Source_Method $usps */
        $usps = Mage::getModel('usa/shipping_carrier_usps_source_method');

        if (!$usps) {
            return Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Shopgate::DEFAULT_SHIPPING_METHOD;
        }

        foreach ($usps->toOptionArray() as $methodArr) {

            if ($methodArr['label'] == $method) {
                return $methodArr['value'];
            }
        }

        return $method;
    }
}
