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
 * Redirects to the proper bank payment class
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Prepay extends Shopgate_Framework_Model_Payment_Simple
{
    /**
     * If mage 1.7+ use native bank payment, else try Check Money Order.
     * Phoenix plugin takes priority if it's enabled
     * Note! Last one overwrites previous.
     *
     * @return false|Shopgate_Framework_Model_Payment_Abstract
     */
    public function getModelByPaymentMethod()
    {
        if ($this->_getConfigHelper()->getIsMagentoVersionLower1700() === false) {
            $native = Mage::getModel('shopgate/payment_simple_prepay_native', array($this->getShopgateOrder()));
            if ($native instanceof Shopgate_Framework_Model_Payment_Interface && $native->isValid()) {
                $this->setPaymentMethod('Native');
            }
        } else {
            $checkmo = Mage::getModel('shopgate/payment_simple_prepay_checkmo', array($this->getShopgateOrder()));
            if ($checkmo instanceof Shopgate_Framework_Model_Payment_Interface && $checkmo->isValid()) {
                $this->setPaymentMethod('Checkmo');
            }
        }

        $phoenix = Mage::getModel('shopgate/payment_simple_prepay_phoenix', array($this->getShopgateOrder()));
        if ($phoenix instanceof Shopgate_Framework_Model_Payment_Interface && $phoenix->isValid()) {
            $this->setPaymentMethod('Phoenix');
        }

        return parent::getModelByPaymentMethod();
    }

}