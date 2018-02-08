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

class Shopgate_Framework_Model_Payment_Payone extends Shopgate_Framework_Model_Payment_Simple
{
    /**
     * Temp rewrite for edge case where AUTHN_CC needs to be
     * handled by AuthorizeCIM or USAEPAY_CC by ChargeItPro
     *
     * @return false|Shopgate_Framework_Model_Payment_Abstract
     * @throws Exception
     */
    public function getModelByPaymentMethod()
    {
        if ($this->getPaymentMethod() == ShopgateOrder::PAYONE_PP &&
            Mage::getModel('shopgate/payment_payone_pp3', array($this->getShopgateOrder()))->isValid()) {
            $this->setPaymentMethod('PP3'); 
        }

        return parent::getModelByPaymentMethod();
    }
}