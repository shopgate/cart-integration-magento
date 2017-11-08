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
class Shopgate_Framework_Block_Totals_AbstractPaymentFee extends Mage_Core_Block_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_code = 'shopgate_payment_fee';

    /**
     * add fee to order detail view
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent       = $this->getParentBlock();
        $this->_order = $parent->getOrder();

        if ($this->_order->getShopgatePaymentFee()) {
            $fee = new Varien_Object();
            $fee->setLabel($this->__('Payment Fee'));
            $fee->setValue($this->_order->getShopgatePaymentFee());
            $fee->setBaseValue($this->_order->getBaseShopgatePaymentFee());
            $fee->setCode($this->getCode());

            $parent->addTotalBefore($fee, 'tax');
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getCode()
    {
        return $this->_code;
    }
}
