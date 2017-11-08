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
 * Shopgate Generic Payment for unknown methods
 */
class Shopgate_Framework_Model_Payment_MobilePayment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * const to get value from store config
     */
    const XML_PATH_PAYMENT_MOBILE_PAYMENT_ORDER_STATUS = "payment/mobile_payment/order_status";

    /**
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var string
     */
    protected $_infoBlockType = 'shopgate/payment_mobilePayment';

    /**
     * @var ShopgateOrder
     */
    protected $_shopgateOrder = null;

    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = 'mobile_payment';

    /**
     * Check whether method is available
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return Mage::helper("shopgate")->isShopgateApiRequest();
    }
}
