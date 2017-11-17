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

/** @noinspection PhpIncludeInspection */
include_once Mage::getBaseDir('lib') . '/Shopgate/cart-integration-sdk/shopgate.php';

/**
 * Handles mobile payment block printing in Mangeto Order view page
 */
class Shopgate_Framework_Block_Payment_MobilePayment extends Mage_Payment_Block_Info
{
    /**
     * Sets template directly
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('shopgate/payment/mobile_payment.phtml');
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getMethod()->getInfoInstance()->getOrder();
    }

    /**
     * @return Shopgate_Framework_Model_Shopgate_Order
     */
    public function getShopgateOrder()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Mage::getModel('shopgate/shopgate_order')->load($this->getOrder()->getId(), 'order_id');
    }

    /**
     * @return string
     */
    public function getShopgateOrderNumber()
    {
        return $this->getShopgateOrder()->getShopgateOrderNumber();
    }

    /**
     * @return array
     */
    public function getPaymentInfos()
    {
        $data = array();

        if ($this->getShopgateOrder()->getReceivedData()) {
            $data = unserialize($this->getShopgateOrder()->getReceivedData());
            $data = $data->getPaymentInfos();
        }

        return $data;
    }

    /**
     * Error message wrapper
     *
     * @param $errorMessage - wraps the message with error markup
     *
     * @return string
     */
    public function printHtmlError($errorMessage)
    {
        if (!$errorMessage) {
            return '';
        }

        return '<strong style="color: red; font-size: 1.2em;">' . $this->__($errorMessage) . '</strong><br/>';
    }

    /**
     * Helper function to print PaymentInfo
     * recursively
     *
     * @param $list - paymentInfo array
     * @param $html - don't pass anything, recrusive helper
     * @return string
     */
    public function printPaymentInfo($list, $html = '')
    {
        if (is_array($list)) {
            foreach ($list as $_key => $_value) {
                if (is_array($_value)) {
                    return $this->printPaymentInfo($_value, $html);
                } else {
                    $html .= '<span style="font-weight: bold">'
                             . $this->__(
                            uc_words($_key, ' ') . '</span> : '
                            . uc_words($_value, ' ') . '<br />'
                        );
                }
            }
        } else {
            $html .= $this->__($this->escapeHtml($list)) . '<br />';
        }

        return $html;
    }
}
