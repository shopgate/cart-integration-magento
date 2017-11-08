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
 * Class Shopgate_Framework_Model_Payment_Payone_Cc
 *
 * @author awesselburg <wesselburg@me.com>
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Payone_Cc
    extends Shopgate_Framework_Model_Payment_Payone_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYONE_CORE_MODEL_CONFIG_IDENTIFIER = 'payone_creditcard';
    const PAYMENT_MODEL = 'payone_core/payment_method_creditcard';
    const PAYMENT_IDENTIFIER = ShopgateOrder::PAYONE_CC;

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $paymentInfo = $this->getShopgateOrder()->getPaymentInfos();

        $this->getOrder()->getPayment()->setCcType($this->_getConfigCode());
        $this->getOrder()->getPayment()->setCcOwner($paymentInfo['credit_card']['holder']);
        $this->getOrder()->getPayment()->setCcNumberEnc($paymentInfo['credit_card']['masked_number']);

        return parent::manipulateOrderWithPaymentData($order);
    }

    /**
     * @return bool|string
     */
    protected function _getConfigCode()
    {
        /**
         * @var array
         */
        $paymentInfo = $this->getShopgateOrder()->getPaymentInfos();

        /**
         * @var string $key
         * @var string $value
         */
        foreach ($this->getSystemConfig()->toSelectArray() as $key => $value) {
            if (strtolower($value) == $paymentInfo['credit_card']['type']) {
                return $key;
            }
        }

        return false;
    }

}