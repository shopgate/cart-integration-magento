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
 * Handles all CC defaults
 *
 * Class Shopgate_Framework_Model_Payment_Cc_Abstract
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc_Abstract
    extends Shopgate_Framework_Model_Payment_Abstract
    implements Shopgate_Framework_Model_Payment_Interface
{
    /**
     * @var $_order Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Retrieve credit card type by mapping
     *
     * @param  $ccType string
     * @return string
     */
    protected function _getCcTypeName($ccType)
    {
        switch ($ccType) {
            case 'visa':
                $ccType = 'VI';
                break;
            case 'mastercard':
                $ccType = 'MC';
                break;
            case 'american_express':
                $ccType = 'AE';
                break;
            case 'discover':
                $ccType = 'DI';
                break;
            case 'jcb':
                $ccType = 'JCB';
                break;
            case 'maestro':
                $ccType = 'SM';
                break;
            default:
                $ccType = 'OT';
                break;
        }
        return $ccType;
    }

}