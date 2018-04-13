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
 * Abstract payment model
 *
 * @package     Shopgate_Framework_Model_Payment_Abstract
 * @author      Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Abstract
    extends Mage_Core_Model_Abstract
    implements Shopgate_Framework_Model_Interfaces_Modules_Validator
{
    /**
     * Has to match the payment_method coming from API call
     */
    const PAYMENT_IDENTIFIER = '';

    /**
     * Model code of the class that inherits mage's Payment_Method_Abstract
     * Defaults to Shopgate's Mobile payment block
     */
    const PAYMENT_MODEL = 'shopgate/payment_mobilePayment';

    /**
     * The config path to module enabled
     */
    const XML_CONFIG_ENABLED = '';

    /**
     * The config path to module's paid status
     */
    const XML_CONFIG_STATUS_PAID = '';

    /**
     * The config path to module's not paid status
     */
    const XML_CONFIG_STATUS_NOT_PAID = '';

    /**
     * The name of the module, as defined in etc/modules/*.xml
     */
    const MODULE_CONFIG = '';

    /**
     * @var null|Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Comes from payment_type of API
     */
    protected $_payment_method;

    /**
     * Shopgate order inserted upon instantiation
     *
     * @var ShopgateOrder
     */
    protected $_shopgate_order;

    /**
     * $this->_data contains constructor param
     * Pass it into the Mage:getModel('',$param)
     *
     * @throws Exception
     */
    public function _construct()
    {
        if ($this->_shopgate_order) {
            return;
        }

        $shopgateOrder = current($this->_data);
        if (!$shopgateOrder instanceof ShopgateCartBase) {
            $given = is_object($shopgateOrder) ? get_class($shopgateOrder) : $shopgateOrder;
            $error = $this->_getHelper()->__(
                'Incorrect type provided to: %s::_construct(). Expected: ShopgateCartBase. Got "%s" instead.',
                get_class($this),
                $given
            );
            ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);
            throw new Exception($error);
        }
        $this->setShopgateOrder($shopgateOrder);
    }

    /**
     * @param ShopgateCartBase $shopgateOrder
     *
     * @return $this
     */
    public function setShopgateOrder(ShopgateCartBase $shopgateOrder)
    {
        $this->_shopgate_order = $shopgateOrder;

        return $this;
    }

    /**
     * @return ShopgateOrder
     */
    public function getShopgateOrder()
    {
        return $this->_shopgate_order;
    }

    /**
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->_payment_method = $paymentMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        if (!$this->_payment_method) {
            $this->_payment_method = $this->getShopgateOrder()->getPaymentMethod();
        }

        return $this->_payment_method;
    }

    /**
     * Magento order getter
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Helps initialize magento order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        return $this->_order = $order;
    }

    /**
     * Get version of plugin
     *
     * @return mixed
     */
    protected function _getVersion()
    {
        $constant = $this->getConstant('MODULE_CONFIG');

        return Mage::getConfig()->getModuleConfig($constant)->version;
    }

    /**
     * ===========================================
     * ============= Active Checkers =============
     * ===========================================
     */

    /**
     * All around check for whether module is the one to use
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isPayment() && $this->isEnabled() && $this->isModuleActive() && $this->checkGenericValid();
    }

    /**
     * Checks that api->payment_method is equals to class constant
     *
     * @return bool
     */
    public function isPayment()
    {
        $payment = $this->getConstant('PAYMENT_IDENTIFIER');
        $flag    = $this->getPaymentMethod() === $payment;

        if (!$flag) {
            $debug = $this->_getHelper()->__(
                'Payment method "%s" does not equal to identifier "%s" in class "%s"',
                $this->getPaymentMethod(),
                $payment,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $flag;
    }

    /**
     * Checks store config to be active
     *
     * @return bool
     */
    public function isEnabled()
    {
        $config  = $this->getConstant('XML_CONFIG_ENABLED');
        $enabled = Mage::getStoreConfigFlag($config);
        if (!$enabled) {
            $debug = $this->_getHelper()->__(
                'Enabled check by path "%s" was evaluated as empty: "%s" in class "%s"',
                $config,
                $enabled,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $enabled;
    }

    /**
     * Checks module node to be active
     *
     * @return mixed
     */
    public function isModuleActive()
    {
        $config = $this->getConstant('MODULE_CONFIG');
        /** @noinspection PhpUndefinedMethodInspection */
        $active = Mage::getConfig()->getModuleConfig($config)->is('active', 'true');

        if (!$active) {
            $debug = $this->_getHelper()->__(
                'Module by config "%s" was not active in class "%s"',
                $config,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $active;
    }

    /**
     * Implement any custom validation
     *
     * @return bool
     */
    public function checkGenericValid()
    {
        return true;
    }

    /**
     * ===========================================
     * ======== Payment necessary methods ========
     * ===========================================
     */

    /**
     * Set up function to be rewritten by payment
     * methods if anything needs to be set up before
     * general logic runs
     */
    public function setUp()
    {
    }

    /**
     * Default order creation if no payment matches
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createNewOrder($quote)
    {
        $service = Mage::getModel('sales/service_quote', $quote);
        if (!Mage::helper('shopgate/config')->getIsMagentoVersionLower15()) {
            $service->submitAll();

            $order = $this->setOrder($service->getOrder());
        } else {
            /** @noinspection PhpDeprecationInspection */
            $order = $this->setOrder($service->submit());
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order' => $order, 'quote' => $quote));
        return $order;
    }

    /**
     * Generic order manipulation, taken originally from Plugin::_setOrderPayment()
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    public function manipulateOrderWithPaymentData($magentoOrder)
    {
        $shopgateOrder = $this->getShopgateOrder();
        if ($shopgateOrder->getIsPaid() && $magentoOrder->getBaseTotalDue()) {
            $magentoOrder->getPayment()->setData('should_close_parent_transaction', true);
            $magentoOrder->getPayment()->registerCaptureNotification($shopgateOrder->getAmountComplete());
            $magentoOrder->addStatusHistoryComment($this->_getHelper()->__("[SHOPGATE] Payment received."), false);
            $magentoOrder->setData('is_customer_notified', false);
        }
        $id = $magentoOrder->getPayment()->getLastTransId();
        if (empty($id)) {
            $magentoOrder->getPayment()->setLastTransId($shopgateOrder->getPaymentTransactionNumber());
        }

        return $magentoOrder;
    }

    /**
     * Default quote prepare handler
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array                  $info
     *
     * @return Mage_Sales_Model_Quote
     */
    public function prepareQuote($quote, $info)
    {
        return $quote;
    }

    /**
     * Setting for default magento status
     *
     * @param $magentoOrder Mage_Sales_Model_Order
     *
     * @return mixed
     */
    public function setOrderStatus($magentoOrder)
    {
        $paid    = $this->getConstant('XML_CONFIG_STATUS_PAID');
        $notPaid = $this->getConstant('XML_CONFIG_STATUS_NOT_PAID');

        if ($this->getShopgateOrder()->getIsPaid()
            || (!$this->getShopgateOrder()->getIsShippingBlocked() &&
                Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_MARK_UNBLOCKED_AS_PAID))
        ) {
            $status = Mage::getStoreConfig($paid, $magentoOrder->getStoreId());
        } else {
            $status = Mage::getStoreConfig($notPaid ? $notPaid : $paid, $magentoOrder->getStoreId());
        }

        if ($status) {
            $state   = $this->_getHelper()->getStateForStatus($status);
            $message = $this->_getHelper()->__('[SHOPGATE] Using native plugin status');
            $magentoOrder->setState($state, $status, $message);
            $magentoOrder->setData('shopgate_status_set', true);
        }

        return $magentoOrder;
    }

    /**
     * Returns the payment model of a class,
     * else falls back to mobilePayment
     *
     * @return mixed
     */
    public function getPaymentModel()
    {
        $payment = $this->getConstant('PAYMENT_MODEL');
        $model   = Mage::getModel($payment);
        if (!$model) {
            $debug = $this->_getHelper()->__(
                'Could not find PAYMENT_MODEL %s in class %s',
                $payment,
                get_class($this)
            );
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
            $model = Mage::getModel(self::PAYMENT_MODEL);
        }

        return $model;
    }

    /**
     * Manipulation of new magento order, BEFORE payment is processed
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _beforeOrderManipulate($magentoOrder)
    {
        $shopgateOrder = $this->getShopgateOrder();

        if ($this->_getHelper()->oneCentBugDetected($shopgateOrder, $magentoOrder)) {
            $fixedOrderAmount = $shopgateOrder->getAmountComplete();
            $magentoOrder->setBaseTotalDue($fixedOrderAmount);
            $magentoOrder->setTotalDue($fixedOrderAmount);
            $magentoOrder->setBaseGrandTotal($fixedOrderAmount);
            $magentoOrder->setGrandTotal($fixedOrderAmount);
        }

        return $magentoOrder;
    }

    /**
     * Manipulation of new magento order, AFTER payment is processed
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _afterOrderManipulate($magentoOrder)
    {
        $shopgateOrder = $this->getShopgateOrder();

        if ($this->_getHelper()->oneCentBugDetected($shopgateOrder, $magentoOrder)) {
            $fixedOrderAmount = $shopgateOrder->getAmountComplete();
            $magentoOrder->getPayment()->setAmountOrdered($fixedOrderAmount);
            $magentoOrder->getPayment()->setBaseAmountOrdered($fixedOrderAmount);

            /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $collection */
            $collection = $magentoOrder->getInvoiceCollection();
            if ($collection->getSize() == 1) {
                foreach ($collection as $invoice) {
                    /** @var Mage_Sales_Model_Order_Invoice $invoice */
                    $invoice->setGrandTotal($magentoOrder->getGrandTotal());
                    $invoice->setBaseGrandTotal($magentoOrder->getBaseGrandTotal());

                    $magentoOrder->setBaseTotalInvoiced($invoice->getBaseGrandTotal());
                    $magentoOrder->setTotalInvoiced($invoice->getGrandTotal());
                    $magentoOrder->setTotalPaid($invoice->getGrandTotal());
                    $magentoOrder->setBaseTotalPaid($magentoOrder->getBaseTotalInvoiced());
                }
            }
        }

        return $magentoOrder;
    }

    /**
     * =======================================
     * ============ Helpers ==================
     * =======================================
     */

    /**
     * @return Shopgate_Framework_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('shopgate');
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Abstract
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_abstract');
    }

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('shopgate/config');
    }

    /**
     * Added support for PHP version 5.2
     * $this::CONSTANT retrieval as static::
     * was supported at 5.3+
     *
     * @param string $input
     *
     * @return mixed
     */
    protected final function getConstant($input)
    {
        $configClass = new ReflectionClass($this);

        return $configClass->getConstant($input);
    }
}
