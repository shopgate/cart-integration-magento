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
class Shopgate_Framework_Model_Observer_CmsMap
{
    /**
     * Check if Magento version is below 1.5
     */
    public function execute()
    {
        if (Mage::helper('shopgate/config')->getIsMagentoVersionLower15()) {
            Mage::getConfig()->setNode(
                'global/blocks/shopgate/rewrite/cmsMap',
                'Shopgate_Framework_Block_CmsMap1400'
            );
        }
    }
}
