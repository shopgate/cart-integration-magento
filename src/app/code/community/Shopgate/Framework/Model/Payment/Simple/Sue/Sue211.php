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
class Shopgate_Framework_Model_Payment_Simple_Sue_Sue211
    extends Shopgate_Framework_Model_Payment_Simple_Sue_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    const XML_CONFIG_ENABLED = 'payment/pnsofortueberweisung/active';
    const PAYMENT_MODEL = 'pnsofortueberweisung/pnsofortueberweisung';
    const XML_CONFIG_STATUS_PAID = 'payment/pnsofortueberweisung/order_status';

    /**
     * Possible configs to check against
     *
     * @var array
     */
    private $allKnownConfigs = array(
        'Sofortueberweisung'    => 'payment/sofort/pnsofort_active',
        'Rechnung by SOFORT'    => 'payment/sofort/sofortrechnung_active',
        'Lastschrift by SOFORT' => 'payment/sofort/lastschriftsofort_active',
        'SOFORT IDEAL'          => 'payment/sofort_ideal/active'
    );

    /**
     * Rewrite to tailor for lower versions
     * of magento's PP implementation
     *
     * @return bool
     */
    public function isEnabled()
    {
        return parent::isEnabled() || $this->_checkOtherEnableConfigs();
    }

    /**
     * @inheritdoc
     */
    protected function setTransactionId(Mage_Sales_Model_Order $magentoOrder)
    {
        $payment = $this->getShopgateOrder()->getPaymentInfos();
        if (isset($payment['transaction_id'])) {
            $magentoOrder->getPayment()->setAdditionalInformation('sofort_transaction', $payment['transaction_id']);
            $magentoOrder->getPayment()->setLastTransId($payment['transaction_id']);
        }
    }

    /**
     * @return bool
     */
    private function _checkOtherEnableConfigs()
    {
        $result = false;
        foreach ($this->allKnownConfigs as $config) {
            $result = Mage::getStoreConfigFlag($config);
            if ($result) {
                return $result;
            }
        }

        $debug = $this->_getHelper()->__('Neither %s are enabled', implode(', ', array_keys($this->allKnownConfigs)));
        ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);

        return $result;
    }
}
