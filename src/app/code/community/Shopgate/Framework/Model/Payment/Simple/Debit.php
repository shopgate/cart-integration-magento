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
class Shopgate_Framework_Model_Payment_Simple_Debit extends Shopgate_Framework_Model_Payment_Simple
{
    /**
     * Directs DEBIT payments to Itabs module if it is valid
     *
     * @return false|Shopgate_Framework_Model_Payment_Abstract
     */
    public function getModelByPaymentMethod()
    {
        $itabs = Mage::getModel('shopgate/payment_simple_debit_itabs', array($this->getShopgateOrder()));

        if ($itabs instanceof Shopgate_Framework_Model_Payment_Abstract && $itabs->isValid()) {
            $this->setPaymentMethod('ITABS');
        }

        return parent::getModelByPaymentMethod();
    }
}
