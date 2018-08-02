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
class Shopgate_Framework_Helper_Client extends Mage_Core_Helper_Abstract
{
    const VALUE_MOBILESITE = 'mobilesite';
    const VALUE_APP        = 'app';

    private $typesApp = array(
        ShopgateClient::TYPE_ANDROIDPHONEAPP,
        ShopgateClient::TYPE_ANDROIDTABLETAPP,
        ShopgateClient::TYPE_IPADAPP,
        ShopgateClient::TYPE_IPHONEAPP
    );

    /**
     * Identify app or mobile site
     *
     * @param ShopgateClient $shopgateClient
     *
     * @return string
     */
    public function getMagentoCartTypeFromClient(ShopgateClient $shopgateClient)
    {
        $shopgateType = $shopgateClient->getType();
        if ($shopgateType === ShopgateClient::TYPE_MOBILESITE) {
            return self::VALUE_MOBILESITE;
        }
        if (in_array($shopgateType, $this->typesApp)) {
            return self::VALUE_APP;
        }

        return '';
    }
}
