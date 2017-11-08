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
 * Redirector for COD payment methods
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Cod extends Shopgate_Framework_Model_Payment_Simple
{
    /**
     * Necessary for isModuleActive() call
     */
    const MODULE_CONFIG = 'Phoenix_CashOnDelivery';

    /**
     * Checks Phoenix|MSP|Native COD payment methods
     * Note! Last one overwrites previous.
     *
     * @return false|Shopgate_Framework_Model_Payment_Abstract
     */
    public function getModelByPaymentMethod()
    {
        if ($this->_getConfigHelper()->getIsMagentoVersionLower1700() === false) {
            $class = Mage::getModel('shopgate/payment_simple_cod_native', array($this->getShopgateOrder()));
            if ($class instanceof Shopgate_Framework_Model_Payment_Interface && $class->isValid()) {
                $this->setPaymentMethod('Native');
            }
        }

        if ($this->isModuleActive()) {
            if (version_compare($this->_getVersion(), '1.0.8', '<')) {
                $this->setPaymentMethod('Phoenix107');
            } else {
                $this->setPaymentMethod('Phoenix108');
            }
        }

        $msp = Mage::getModel('shopgate/payment_simple_cod_msp', array($this->getShopgateOrder()));
        if ($msp instanceof Shopgate_Framework_Model_Payment_Interface && $msp->isValid()) {
            $this->setPaymentMethod('Msp');
        }

        return parent::getModelByPaymentMethod();
    }

}