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
class Shopgate_Framework_Model_Sales_Quote_Address_Total_ShopgatePaymentFee
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /** @inheritdoc */
    protected $_code = 'shopgate_payment_fee';

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return Mage::helper('shopgate')->__('Payment Fee');
    }

    /**
     * @inheritdoc
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $shopgateOrder = Mage::registry('shopgate_order');
        $quote         = $address->getQuote();
        $hasFee        = $shopgateOrder && $shopgateOrder->getAmountShopPayment() != 0;

        if ($address->getAddressType() === Mage_Sales_Model_Quote_Address::TYPE_BILLING || !$hasFee
        ) {
            return $this;
        }
        $paymentFee = $shopgateOrder->getAmountShopPayment();
        $address->setData('shopgate_payment_fee', $paymentFee);
        $address->setData('base_shopgate_payment_fee', $paymentFee);

        $quote->setData('shopgate_payment_fee', $paymentFee);
        $quote->setData('base_shopgate_payment_fee', $paymentFee);

        $address->setGrandTotal($address->getGrandTotal() + $address->getData('shopgate_payment_fee'));
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getData('base_shopgate_payment_fee'));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $fee = $address->getData('shopgate_payment_fee');
        if ($fee != 0) {
            $address->addTotal(
                array('code' => $this->getCode(), 'title' => $this->getLabel(), 'value' => $fee)
            );
        }

        return $this;
    }
}
