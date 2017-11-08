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

/**
 * Main factory that gets inherited by other module factories
 */
class Shopgate_Framework_Model_Modules_Factory extends Mage_Core_Model_Abstract
{
    /** @var ShopgateOrder | ShopgateCart */
    private $sgOrder;

    /**
     * @throws Exception
     */
    public function _construct()
    {
        $sgOrder = current($this->_data);

        if (!$sgOrder instanceof ShopgateCartBase
        ) {
            $error = Mage::helper('shopgate')->__('Incorrect class provided to: %s::_constructor()', get_class($this));
            ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);
            throw new Exception($error);
        }
        $this->sgOrder = $sgOrder;
    }

    /** @return ShopgateCart | ShopgateOrder */
    protected function getSgOrder()
    {
        return $this->sgOrder;
    }
}
