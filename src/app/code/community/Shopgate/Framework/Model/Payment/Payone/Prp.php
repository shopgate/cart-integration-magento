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
 * Class Shopgate_Framework_Model_Payment_Payone_Prepay
 *
 * @author awesselburg <wesselburg@me.com>
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Payone_Prp
    extends Shopgate_Framework_Model_Payment_Payone_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const PAYONE_CORE_MODEL_CONFIG_IDENTIFIER = 'payone_advance_payment';
    const PAYMENT_MODEL = 'payone_core/payment_method_advancePayment';
    const PAYMENT_IDENTIFIER = ShopgateOrder::PAYONE_PRP;

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function manipulateOrderWithPaymentData($order)
    {
        $info = $this->getShopgateOrder()->getPaymentInfos();

        if (isset($info['clearing_bankaccountholder'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankAccountholder($info['clearing_bankaccountholder']);
        }
        if (isset($info['clearing_bankcountry'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankCountry($info['clearing_bankcountry']);
        }
        if (isset($info['clearing_bankaccount'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankAccount($info['clearing_bankaccount']);
        }
        if (isset($info['clearing_bankcode'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankCode($info['clearing_bankcode']);
        }
        if (isset($info['clearing_bankcity'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankCity($info['clearing_bankcity']);
        }
        if (isset($info['clearing_bankname'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankName($info['clearing_bankname']);
        }
        if (isset($info['clearing_bankiban'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankIban(strtoupper($info['clearing_bankiban']));
        }
        if (isset($info['clearing_bankbic'])) {
            $this->getOrder()->getPayment()->setPayoneClearingBankBic(strtoupper($info['clearing_bankbic']));
        }

        return parent::manipulateOrderWithPaymentData($order);
    }

    /**
     * Rewritten to add additional clearing parameters to response
     *
     * @param null|Payone_Api_Response_Authorization_Approved|Payone_Api_Response_Preauthorization_Approved $response
     * @return Payone_Api_Response_Authorization_Approved|Payone_Api_Response_Preauthorization_Approved
     */
    protected function _createFakeResponse($response = null)
    {
        $info = $this->getShopgateOrder()->getPaymentInfos();

        $response = $this->_getPayoneResponse();

        /** @var Payone_Api_Response_Authorization_Approved | Payone_Api_Response_Preauthorization_Approved $response */
        foreach ($info as $key => $val) {
            switch ($key) {
                case 'clearing_bankaccount':
                    $response->setClearingBankaccount($info[$key]);
                    break;
                case 'clearing_bankcode':
                    $response->setClearingBankcode($info[$key]);
                    break;
                case 'clearing_bankcountry':
                    $response->setClearingBankcountry($info[$key]);
                    break;
                case 'clearing_bankname':
                    $response->setClearingBankname($info[$key]);
                    break;
                case 'clearing_bankaccountholder':
                    $response->setClearingBankaccountholder($info[$key]);
                    break;
                case 'clearing_bankcity':
                    $response->setClearingBankcity($info[$key]);
                    break;
                case 'clearing_bankiban':
                    $response->setClearingBankiban($info[$key]);
                    break;
                case 'clearing_bankbic':
                    $response->setClearingBankbic($info[$key]);
                    break;
            }
        }
        return parent::_createFakeResponse($response);
    }
}