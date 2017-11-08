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
 * Forwarder for all Credit Card payment methods that contain CC in payment_method
 * Inherits from Simple class to use the first part of payment_method.
 * Meaning use Authn in Authn_CC to make Cc/Auth.php call
 *
 * Class Shopgate_Framework_Model_Payment_Cc
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc extends Shopgate_Framework_Model_Payment_Simple
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
        if (Mage::getModel('shopgate/payment_cc_authncim', array($this->getShopgateOrder()))->isValid()) {
            $this->setPaymentMethod('AUTHNCIM_CC');
        } elseif (Mage::getModel('shopgate/payment_cc_chargeitpro', array($this->getShopgateOrder()))->isValid()) {
            $this->setPaymentMethod('CHARGEITPRO_CC');
        }

        return parent::getModelByPaymentMethod();
    }
}