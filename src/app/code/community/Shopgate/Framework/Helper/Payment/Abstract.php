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
class Shopgate_Framework_Helper_Payment_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * string for cc number in ipn data
     */
    const SHOPGATE_CC_STRING = 'masked_number';

    /**
     * string for cc type in ipn data
     */
    const SHOPGATE_CC_TYPE_STRING = 'type';

    /**
     * string for holder in ipn data
     */
    const SHOPGATE_CC_HOLDER_STRING = 'holder';

    /**
     * Raw details key in additional info
     *
     */
    const RAW_DETAILS = 'raw_details_info';

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @param $order Mage_Sales_Model_Order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function createOrderInvoice($order)
    {
        $invoice = $order->prepareInvoice();
        $invoice->register();
        return $invoice;
    }

    /**
     * Gets raw detail key
     *
     * @return string
     */
    public function getTransactionRawDetails()
    {
        return defined(
            'Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS'
        ) ? Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS : self::RAW_DETAILS;
    }
}
