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
 * General fallback for all COD methods
 */
class Shopgate_Framework_Model_Payment_Simple_Cod_Abstract
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYMENT_IDENTIFIER = ShopgateOrder::COD;
    const XML_CONFIG_FEE_LOCAL = '';
    const XML_CONFIG_FEE_FOREIGN = '';

    /**
     * Run fee processing before everything
     */
    public function setUp()
    {
        $this->processPaymentFee();
    }

    /**
     * No need to pull status, it is assigned automatically,
     * defaults to 'Pending' when not set in config.
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return mixed
     */
    public function setOrderStatus($magentoOrder)
    {
        return $magentoOrder->setData('shopgate_status_set', true);
    }

    /**
     * If the COD config has a payment fee set, overwrite the fee
     * that is coming form Merchant API server
     */
    protected function processPaymentFee()
    {
        $local   = $this->getConstant('XML_CONFIG_FEE_LOCAL');
        $foreign = $this->getConstant('XML_CONFIG_FEE_FOREIGN');
        $sgOrder = $this->getShopgateOrder();

        if (Mage::getStoreConfig($local) || Mage::getStoreConfig($foreign)) {
            $sgOrder->setAmountShopPayment(0);
        }
    }
}
