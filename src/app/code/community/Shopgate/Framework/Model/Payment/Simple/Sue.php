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
 * Sofortuebreweisung version router
 *
 * Class Shopgate_Framework_Model_Payment_Simple_Sue
 *
 * @author  Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Simple_Sue extends Shopgate_Framework_Model_Payment_Simple
{
    const MODULE_CONFIG = 'Paymentnetwork_Pnsofortueberweisung';

    /**
     * Route to appropriate file based on version
     *
     * @return false|Shopgate_Framework_Model_Payment_Abstract
     */
    public function getModelByPaymentMethod()
    {
        if (version_compare($this->_getVersion(), '3.0.0', '>=')) {
            $this->setPaymentMethod('SUE300');
        } elseif (version_compare($this->_getVersion(), '2.1.1', '>=')) {
            $this->setPaymentMethod('SUE211');
        } else {
            $this->setPaymentMethod('SUE118');
        }

        return parent::getModelByPaymentMethod();
    }
}
