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
class Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Shopgate
    extends Shopgate_Framework_Model_Shopgate_Shipping_Mapper_Method_Abstract
{
    /**
     * default method const
     */
    const DEFAULT_SHIPPING_METHOD = 'fix';

    /**
     * @param ShopgateCartBase $order
     *
     * @return string
     */
    protected function _fetchMethod(ShopgateCartBase $order)
    {
        ShopgateLogger::getInstance()->log('# ' . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);

        return self::DEFAULT_SHIPPING_METHOD;
    }

    /**
     * @return array $collection
     */
    public function getAvailableMethods()
    {
        ShopgateLogger::getInstance()->log('# ' . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);

        return array(self::DEFAULT_SHIPPING_METHOD,);
    }

    /**
     * @return string
     */
    public function getDefaultMethod()
    {
        ShopgateLogger::getInstance()->log('# ' . __METHOD__, ShopgateLogger::LOGTYPE_DEBUG);

        return self::DEFAULT_SHIPPING_METHOD;
    }
}
