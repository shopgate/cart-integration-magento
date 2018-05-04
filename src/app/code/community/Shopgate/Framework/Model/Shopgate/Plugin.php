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
class Shopgate_Framework_Model_Shopgate_Plugin extends ShopgatePlugin
{
    /**
     * Const for enterprise gift wrapping
     */
    const GIFT_WRAP_OPTION_ID = 'EE_GiftWrap';
    /**
     * Identifier for the add_order plugin action
     */
    const ACTION_ADD_ORDER = 'add_order';

    /**
     * @var Shopgate_Framework_Model_Config
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_errorOnInvalidCoupon = true;

    /**
     * @var string
     */
    protected $_defaultTax;

    /**
     * A stack to keep all virtual objects in
     * objects are shopgate-coupons and payment fees
     *
     * @var array
     */
    protected $_virtualObjectStack = array();

    /**
     * @var null | Shopgate_Framework_Model_Export_Product
     */
    protected $_exportProductInstance = null;

    /**
     * @var array | null
     */
    protected $_defaultCategoryRow = null;

    /**
     * @var null | Shopgate_Framework_Model_Factory
     */
    protected $_factory = null;

    /** @var bool */
    protected $couponsIncludeTax = false;

    /**
     * Callback function for initialization by plugin implementations.
     * This method gets called on instantiation of a ShopgatePlugin child class and serves as __construct() replacement.
     * Important: Initialize $this->_config here if you have your own config class.
     *
     * @see http://wiki.shopgate.com/Shopgate_Library#startup.28.29
     */
    public function startup()
    {
        /* @var $config Shopgate_Framework_Helper_Config */
        $this->_config         = $this->_getConfig();
        $storeViewId           = $this->_config->getStoreViewId();
        $this->_defaultTax     = Mage::getModel('tax/calculation')->getDefaultCustomerTaxClass($storeViewId);
        $pricesInclTaxes       = Mage::helper('tax')->priceIncludesTax($storeViewId);
        $applyTaxAfterDiscount = Mage::helper('tax')->applyTaxAfterDiscount($storeViewId);
        $this->setCouponsIncludeTax($pricesInclTaxes || !$applyTaxAfterDiscount);

        return true;
    }

    /**
     * Retrieve main factory
     *
     * @return Shopgate_Framework_Model_Factory
     */
    protected function _getFactory()
    {
        if (!$this->_factory) {
            $this->_factory = Mage::getModel('shopgate/factory');
        }

        return $this->_factory;
    }

    /**
     *
     * @param string $action
     * @return string
     */
    public function getActionUrl($action)
    {
        return $this->_getHelper()->getOAuthRedirectUri(Mage::app()->getRequest()->getParam('storeviewid'));
    }

    /**
     * get config from shopgate helper
     *
     * @return Shopgate_Framework_Model_Config
     */
    protected function _getConfig()
    {
        return $this->_getConfigHelper()->getConfig();
    }

    /**
     * Executes a cron job with parameters.
     * $message contains a message of success or failure for the job.
     * $errorcount contains the number of errors that occurred during execution.
     *
     * @param string       $jobname
     * @param mixed|string $params     Associative list of parameter names and values.
     * @param string       $message    A reference to the variable the message is appended to.
     * @param int          $errorcount A reference to the error counter variable.
     *
     * @throws ShopgateLibraryException
     */
    public function cron($jobname, $params, &$message, &$errorcount)
    {
        $this->log("Start Run CRON-Jobs", ShopgateLogger::LOGTYPE_DEBUG);

        switch ($jobname) {
            case "set_shipping_completed":
                $this->log("> Run job {$jobname}", ShopgateLogger::LOGTYPE_DEBUG);
                $this->_cronSetShippingCompleted();
                break;
            case "cancel_orders":
                $this->log("> Run job {$jobname}", ShopgateLogger::LOGTYPE_DEBUG);
                $this->_cronCancelOrder();
                break;
            default:
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB,
                    '"' . $jobname . '"',
                    true
                );
        }

        $this->log("END Run CRON-Jobs", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * cron to set the shipping to complete
     */
    protected function _cronSetShippingCompleted()
    {
        /** @var Shopgate_Framework_Model_Resource_Shopgate_Order_Collection $collection */
        $collection = Mage::getResourceModel('shopgate/shopgate_order_collection')->getUnsyncedOrders();
        $this->log(">> Found {$collection->getSize()} potential orders to send", ShopgateLogger::LOGTYPE_DEBUG);

        foreach ($collection as $order) {
            /* @var $order Shopgate_Framework_Model_Shopgate_Order */
            $this->log(">> Order with ID {$order->getId()} loaded", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log(">> Call observer->setShippingStatus", ShopgateLogger::LOGTYPE_DEBUG);
            $shipment = Mage::getModel('sales/order_shipment')->setOrderId($order->getOrderId());

            $event         = new Varien_Event(
                array(
                    'data_object' => $shipment,
                    'shipment'    => $shipment
                )
            );
            $eventObserver = new Varien_Event_Observer();
            $eventObserver->setEvent($event);
            Mage::getModel('shopgate/observer')->setShippingStatus($eventObserver);
        }
    }

    /**
     * cron to cancel already cancelled orders
     */
    protected function _cronCancelOrder()
    {
        /** @var Shopgate_Framework_Model_Resource_Shopgate_Order_Collection $collection */
        $collection = Mage::getResourceModel('shopgate/shopgate_order_collection')->getAlreadyCancelledOrders();
        $this->log(">> Found {$collection->getSize()} potential orders to send", ShopgateLogger::LOGTYPE_DEBUG);

        foreach ($collection as $shopgateOrder) {
            /* @var $shopgateOrder Shopgate_Framework_Model_Shopgate_Order */
            /* @var $order Mage_Sales_Model_Order */
            $order = $shopgateOrder->getOrder();

            if (!$order->isCanceled()) {
                continue;
            }
            $this->log(
                ">> Order with ID {$order->getId()} loaded and ready for cancellation",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            $this->log(">> Dispatching event order_cancel_after", ShopgateLogger::LOGTYPE_DEBUG);
            Mage::dispatchEvent('order_cancel_after', array('order' => $order, 'shopgate_order' => $shopgateOrder));
        }
    }

    /**
     * @param string $user
     * @param string $pass
     *
     * @return ShopgateCustomer
     * @throws ShopgateLibraryException
     * @see ShopgatePluginCore::getUserData()
     */
    public function getCustomer($user, $pass)
    {
        /** @var Mage_Customer_Model_Customer $magentoCustomer */
        $magentoCustomer = Mage::getModel('customer/customer');
        $magentoCustomer->setStore(Mage::app()->getStore());
        try {
            $magentoCustomer->authenticate($user, $pass);
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_CUSTOMER_ACCOUNT_NOT_CONFIRMED,
                        null,
                        false,
                        false
                    );
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD,
                        null,
                        false,
                        false
                    );
                default:
                    throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_CUSTOMER_UNKNOWN_ERROR);
            }
        }
        $shopgateCustomer = Mage::getModel('shopgate/export_customer')->loadGetCustomerData($magentoCustomer);
        return $shopgateCustomer;
    }

    /**
     * This method creates a new user account / user addresses for a customer in the shop system's database
     * The method should not abort on soft errors like when the street or phone number of a customer is not set.
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_register_customer#API_Response
     *
     * @param string           $user     The user name the customer entered at Shopgate.
     * @param string           $pass     The password the customer entered at Shopgate.
     * @param ShopgateCustomer $customer A ShopgateCustomer object to be added to the shop system's database.
     *
     * @throws ShopgateLibraryException if an error occures
     */
    public function registerCustomer($user, $pass, ShopgateCustomer $customer)
    {
        try {
            /** @var Mage_Customer_Model_Customer $magentoCustomer */
            $magentoCustomer = Mage::getModel("customer/customer");
            $magentoCustomer->setEmail($user);
            $magentoCustomer->setPassword($pass);
            $magentoCustomer->setStore(Mage::app()->getStore());
            $this->_getCustomerHelper()->registerCustomer($magentoCustomer, $customer);
        } catch (Mage_Customer_Exception $e) {
            if ($e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS);
            } else {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::REGISTER_FAILED_TO_ADD_USER,
                    $e->getMessage(),
                    true
                );
            }
        } catch (Exception $e) {
            throw new ShopgateLibraryException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, $e->getMessage(), true);
        }
    }

    /**
     * Performs the necessary queries to add an order to the shop system's database.
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_orders#API_Response
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order#API_Response
     *
     * @param ShopgateOrder $order The ShopgateOrder object to be added to the shop system's database.
     *
     * @return array(
     *          <ul>
     *          <li>'external_order_id' => <i>string</i>, # the ID of the order in your shop system's database</li>
     *          <li>'external_order_number' => <i>string</i> # the number of the order in your shop system</li>
     *          </ul>
     *         )
     * @throws ShopgateLibraryException if an error occurs.
     */
    public function addOrder(ShopgateOrder $order)
    {
        /* @var Mage_Sales_Model_Order $magentoOrder */
        /* @var Mage_Sales_Model_Quote $quote */
        /* @var Mage_Sales_Model_Service_Quote $service */
        try {
            $this->log('## Start to add new Order', ShopgateLogger::LOGTYPE_DEBUG);
            $this->log("## Order-Number: {$order->getOrderNumber()}", ShopgateLogger::LOGTYPE_DEBUG);
            Mage::getModel('sales/order')->getResource()->beginTransaction();

            $this->_errorOnInvalidCoupon = true;

            $this->log('# Checking Shopgate order for duplicate', ShopgateLogger::LOGTYPE_DEBUG);
            /** @var Shopgate_Framework_Model_Shopgate_Order $magentoShopgateOrder */
            $magentoShopgateOrder = Mage::getModel('shopgate/shopgate_order')->load(
                $order->getOrderNumber(),
                'shopgate_order_number'
            );

            if ($magentoShopgateOrder->getId() !== null) {
                $this->log('# Duplicate order found', ShopgateLogger::LOGTYPE_DEBUG);

                $orderId = 'unset';
                if ($magentoShopgateOrder->getOrderId()) {
                    $orderId = $magentoShopgateOrder->getOrderId();
                }

                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER,
                    'orderId: ' . $orderId,
                    true
                );
            }
            Mage::dispatchEvent('shopgate_add_order_before', array('shopgate_order' => $order));
            $this->log('# Add shopgate order to Session', ShopgateLogger::LOGTYPE_DEBUG);
            Mage::register('shopgate_order', $order, true);
            $this->log('# Create quote for order', ShopgateLogger::LOGTYPE_DEBUG);

            $this->_getFactory()->getPayment()->setUp();
            $quote = Mage::getModel('sales/quote')->setStoreId($this->_getConfig()->getStoreViewId());

            $quote->getBillingAddress()->setCartFixedRules(array());
            $quote->getShippingAddress()->setCartFixedRules(array());
            $quote = $this->executeLoaders($this->_getCreateOrderQuoteLoaders(), $quote, $order);
            $this->log('# Set up affiliate', ShopgateLogger::LOGTYPE_DEBUG);
            $this->_getFactory()->getAffiliate($order)->setUp($quote);

            if (Mage::getConfig()->getModuleConfig('FireGento_MageSetup')->is('active', 'true')
                || Mage::getConfig()->getModuleConfig('FireGento_GermanSetup')->is('active', 'true')
            ) {
                $session = Mage::getSingleton('checkout/session');
                $session->replaceQuote($quote);
            }
            $this->log('# Setting up shipping', ShopgateLogger::LOGTYPE_DEBUG);
            Mage::helper('shopgate/shipping')->applyShipping($quote, $order);

            // due to compatibility with 3rd party modules which fetches the quote from the session (like phoenix_cod, SUE)
            // needed before $service->submitAll() is called
            Mage::getSingleton('checkout/session')->replaceQuote($quote);

            $this->log('# Create order from quote', ShopgateLogger::LOGTYPE_DEBUG);
            $magentoOrder = $this->_getFactory()->getPayment()->createNewOrder($quote);

            $this->log('# Modifying order', ShopgateLogger::LOGTYPE_DEBUG);
            Mage::dispatchEvent('shopgate_modify_order_before', array('order' => $magentoOrder));
            $magentoOrder->setCanEdit(false);
            $magentoOrder->setCanShipPartially(true);
            $magentoOrder->setCanShipPartiallyItem(true);
            $magentoOrder = $this->executeLoaders($this->_getCreateOrderLoaders(), $magentoOrder, $order);

            //todo: move this out, intentionally here after executeLoaders?
            if ($magentoOrder->getTotalDue() > 0
                && $order->getPaymentMethod() == ShopgateOrder::PP_WSPP_CC
            ) {
                if (!$magentoOrder->getPayment()->getIsTransactionPending()) {
                    $magentoOrder->setTotalPaid($magentoOrder->getGrandTotal());
                    $magentoOrder->setBaseTotalPaid($magentoOrder->getBaseGrandTotal());
                    $magentoOrder->setTotalDue(0);
                    $magentoOrder->setBaseTotalDue(0);
                }
            }
            $this->_getFactory()->getAffiliate($order)->promptCommission($magentoOrder);
            $magentoOrder->save();

            $this->log('# Commit Transaction', ShopgateLogger::LOGTYPE_DEBUG);
            Mage::getModel('sales/order')->getResource()->commit();
            $this->log('## Order saved successful', ShopgateLogger::LOGTYPE_DEBUG);
            Mage::dispatchEvent(
                'shopgate_add_order_after',
                array(
                    'shopgate_order' => $order,
                    'order'          => $magentoOrder
                )
            );

            $warnings      = array();
            $totalShopgate = $order->getAmountComplete();
            $totalMagento  = $magentoOrder->getTotalDue();
            $this->log(
                "
                    Shopgate order ID: {$order->getOrderNumber()}
                    Magento order ID: {$magentoOrder->getId()}
                    Total Shopgate: {$totalShopgate} {$order->getCurrency()}
                    Total Magento: {$totalMagento} {$order->getCurrency()}
                    ",
                ShopgateLogger::LOGTYPE_DEBUG
            );

            $result = array(
                'external_order_id'     => $magentoOrder->getId(),
                'external_order_number' => $magentoOrder->getIncrementId()
            );

            $msg = '';
            if (!$this->_getHelper()->isOrderTotalCorrect($order, $magentoOrder, $msg)) {
                if ($this->_getConfigHelper()->getIsMagentoVersionLower16()) {
                    $magentoOrder->addStatusHistoryComment(nl2br($msg), false);
                    $magentoOrder->save();
                }
                $this->log($msg);
                $warnings[] = array(
                    'message' => $msg
                );

                $result['warnings'] = $warnings;
            }
            $this->_setShopgateOrder($magentoOrder, $order);
            $this->_getFactory()->getAffiliate($order)->destroyCookies();
        } catch (ShopgateLibraryException $e) {
            Mage::getModel('sales/order')->getResource()->rollback();
            throw $e;
        } catch (Exception $e) {
            Mage::getModel('sales/order')->getResource()->rollback();
            throw new ShopgateLibraryException(
                ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                "{$e->getMessage()}\n{$e->getTraceAsString()}",
                true
            );
        }

        return $result;
    }

    /**
     * array of functions called to create the quote
     *
     * @return array
     */
    protected function _getCreateOrderQuoteLoaders()
    {
        return array(
            "_setQuoteClientType",
            "_setQuoteItems",
            "_setQuoteShopgateCoupons",
            "_setQuoteVirtualItem",
            "_setQuoteCustomer",
            "_setQuotePayment",
            "_setQuoteShopCoupons",
        );
    }

    /**
     * Set the client cart type for coupon validation
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     */
    public function _setQuoteClientType(Mage_Sales_Model_Quote $quote, ShopgateCartBase $order)
    {
        $type        = ($order->getClient() instanceof ShopgateClient) ? $order->getClient()->getType() : '';
        $defaultType = Shopgate_Framework_Model_SalesRule_Condition::CART_TYPE;
        $quote->getShippingAddress()->setData($defaultType, $type);
    }

    /**
     * Insert the ordered items to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     *
     * @return Mage_Sales_Model_Quote
     * @throws ShopgateLibraryException
     * @throws Exception
     */
    protected function _setQuoteItems($quote, $order)
    {
        $this->log('_setQuoteItems', ShopgateLogger::LOGTYPE_DEBUG);
        $storeId = $this->_getConfig()->getStoreViewId();

        foreach ($order->getItems() as $item) {
            /* @var $item ShopgateOrderItem */
            if ($item->isSgCoupon()) {
                /** is a shopgate coupon */
                continue;
            }

            $orderInfo     = $item->getInternalOrderInfo();
            $orderInfo     = $this->jsonDecode($orderInfo, true);
            $amountWithTax = $item->getUnitAmountWithTax();
            $amountNet     = $item->getUnitAmount();

            $stackQuantity = 1;
            if (!empty($orderInfo['stack_quantity']) && $orderInfo['stack_quantity'] > 1) {
                $stackQuantity = $orderInfo['stack_quantity'];
            }

            if ($stackQuantity > 1) {
                $amountWithTax = $amountWithTax / $stackQuantity;
                $amountNet     = $amountNet / $stackQuantity;
            }

            $pId = $orderInfo["product_id"];
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($pId);

            if (!$product->getId()) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND,
                    'product ID: ' . $pId,
                    true
                );
            }

            $itemNumber    = $item->getItemNumber();
            $productWeight = null;

            if (strpos($itemNumber, '-') !== false) {
                $productWeight = $product->getTypeInstance()->getWeight();
                $productIds    = explode('-', $itemNumber);
                $parentId      = $productIds[0];
                $childId       = $productIds[1];
                /** @var Mage_Catalog_Model_Product $parent */
                $parent = Mage::getModel('catalog/product')->setStoreId($storeId)->load($parentId);
                if ($parent->isConfigurable()
                    && !$this->_getConfig()->addOnlySimplesToCart()
                ) {
                    $buyObject       = $this->_createQuoteItemBuyInfo($item, $parent, $stackQuantity);
                    $superAttributes = $parent->getTypeInstance(true)->getConfigurableAttributesAsArray($parent);
                    $superAttConfig  = array();

                    foreach ($superAttributes as $productAttribute) {
                        $superAttConfig[$productAttribute['attribute_id']] = $product->getData(
                            $productAttribute['attribute_code']
                        );
                    }
                    $buyObject->setSuperAttribute($superAttConfig);
                    $product = $parent;
                } elseif ($parent->isGrouped()) {
                    /** @var Mage_Catalog_Model_Product_Type_Grouped $product */
                    $product            = Mage::getModel('catalog/product')->setStoreId($storeId)->load($childId);
                    $buyObject          = $this->_createQuoteItemBuyInfo($item, $product, $stackQuantity);
                    $associatedProducts = $parent->getTypeInstance(true)->getAssociatedProducts($parent);
                    $superGroup         = array();
                    foreach ($associatedProducts as $associatedProduct) {
                        /** @var Mage_Catalog_Model_Product $associatedProduct */
                        $superGroup[$associatedProduct->getId()] = 1;
                    }
                    $buyObject->setSuperGroup($superGroup);
                    $buyObject->setSuperProductConfig(
                        array(
                            'product_type' => Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE,
                            'product_id'   => $parent->getId()
                        )
                    );
                } else {
                    $buyObject = $this->_createQuoteItemBuyInfo($item, $product, $stackQuantity);
                }
            } else {
                $buyObject = $this->_createQuoteItemBuyInfo($item, $product, $stackQuantity);
            }

            $product->setData('shopgate_item_number', $itemNumber);
            $product->setData('shopgate_options', $item->getOptions());
            $product->setData('shopgate_inputs', $item->getInputs());
            $product->setData('shopgate_attributes', $item->getAttributes());

            if (Mage::app()->getRequest()->getParam('action') == 'check_stock') {
                $product->setSkipCheckRequiredOption(true);
                $product->getStockItem()->setSuppressCheckQtyIncrements(true);
            }

            $quoteItemCount = 0;
            try {
                $quoteItemCount = count($quote->getAllItems());
                /** @var $quotItem Mage_Sales_Model_Quote_Item */
                $quoteItem = $quote->addProduct($product, $buyObject);
                if (!($quoteItem instanceof Varien_Object)) {
                    switch ($quoteItem) {
                        case Mage::helper('catalog')->__('Please specify the product required option(s).'):
                        case Mage::helper('catalog')->__('Please specify the product\'s option(s).'):
                        case Mage::helper('catalog')->__('The text is too long'):
                            Mage::throwException(Mage::helper('catalog')->__($quoteItem));
                        default:
                            throw new ShopgateLibraryException(
                                ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                                "Error on adding product to quote! Details: " . var_export($quoteItem, true),
                                true
                            );
                    }
                }
                $quoteItem = $quote->getItemByProduct($product);

                if ($this->_getConfig()->useShopgatePrices()) {
                    if (Mage::helper('tax')->priceIncludesTax($storeId)) {
                        $taxClassId    = $product->getTaxClassId();
                        $amountWithTax = Mage::helper('shopgate/sales')
                                             ->getOriginalGrossAmount(
                                                 $storeId,
                                                 $taxClassId,
                                                 $amountNet,
                                                 $amountWithTax
                                             );
                        $quoteItem->setCustomPrice($amountWithTax);
                        $quoteItem->setOriginalCustomPrice($amountWithTax);
                    } else {
                        $quoteItem->setCustomPrice($amountNet);
                        $quoteItem->setOriginalCustomPrice($amountNet);
                    }
                }
                $quoteItem->setTaxPercent($item->getTaxPercent());

                if (!is_null($productWeight)) {
                    $quoteItem->setWeight($productWeight);

                    foreach ($quoteItem->getChildren() as $child) {
                        $child->setWeight($productWeight);
                    }
                }
                $quoteItem->setRowWeight($quoteItem->getWeight() * $quoteItem->getQty());
                $quoteItem->setWeeeTaxApplied(serialize(array()));
            } catch (Exception $e) {
                $this->log(
                    "Error importing product to quote by id: {$product->getId()}, error: {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_ERROR
                );
                $this->log($item->toArray(), ShopgateLogger::LOGTYPE_ERROR);
                if (count($quote->getAllItems()) === $quoteItemCount) {
                    $orderInfo['error_message'] = $e->getMessage();
                }
                $item->setInternalOrderInfo($this->jsonEncode($orderInfo));

                // show detailed information about errors for failed add orders
                if (Mage::app()->getRequest()->getParam('action') == self::ACTION_ADD_ORDER) {
                    throw $e;
                }
            }
        }

        return $quote;
    }

    /**
     * @see http://inchoo.net/ecommerce/magento/programatically-add-bundle-product-to-cart-n-magento/
     *
     * @param ShopgateOrderItem          $item
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $stackQuantity
     *
     * @return Varien_Object
     */
    protected function _createQuoteItemBuyInfo($item, $product, $stackQuantity)
    {
        $orderInfo = $item->getInternalOrderInfo();
        $orderInfo = $this->jsonDecode($orderInfo, true);

        $buyInfo = array(
            'qty'     => $item->getQuantity() * $stackQuantity,
            'product' => $product->getId(),
        );

        if (isset($orderInfo["options"])) {
            $buyInfo['super_attribute'] = $orderInfo["options"];
        }

        if ($item->getOptions()) {

            if ($orderInfo["item_type"] == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                foreach ($item->getOptions() as $orderOption) {
                    /* @var $orderOption ShopgateOrderItemOption */
                    $optionId = $orderOption->getOptionNumber();
                    $value    = $orderOption->getValueNumber();

                    if (self::GIFT_WRAP_OPTION_ID === $optionId && 0 < $value) {
                        $buyInfo[self::GIFT_WRAP_OPTION_ID] = array(
                            'id'    => $value,
                            'price' => $orderOption->getAdditionalAmountWithTax()
                        );
                        continue;
                    }
                    /** @var Mage_Catalog_Model_Product_Option $productOption */
                    $productOption = Mage::getModel("bundle/option")->load($optionId);

                    if (!$productOption->getId()
                        || ($productOption->getId() && $productOption->getParentId() != $product->getId())
                    ) {
                        /** @var Mage_Catalog_Model_Product_Option $productOption */
                        $productOption = Mage::getModel("catalog/product_option")->load($optionId);

                        if ($productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
                            || $productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
                        ) {

                            if ($value == 0) {
                                continue;
                            }

                            $value = array($value);
                        }
                        $buyInfo["options"][$optionId] = $value;
                    } else {
                        if ($productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
                            || $productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
                        ) {

                            if (!$value) {
                                continue;
                            }

                            $value = array($value);
                        }
                        /** @var Mage_Bundle_Model_Selection $bundleSelection */
                        $bundleSelection                         = Mage::getModel("bundle/selection")->load($value);
                        $buyInfo["bundle_option_qty"][$optionId] = max(1, (int)$bundleSelection->getSelectionQty());
                        $buyInfo["bundle_option"][$optionId]     = $value;
                    }
                }
            } else {
                foreach ($item->getOptions() as $orderOption) {
                    /* @var $orderOption ShopgateOrderItemOption */
                    $optionId = $orderOption->getOptionNumber();
                    $value    = $orderOption->getValueNumber();

                    if (self::GIFT_WRAP_OPTION_ID === $optionId && 0 < $value) {
                        $buyInfo[self::GIFT_WRAP_OPTION_ID] = array(
                            'id'    => $value,
                            'price' => $orderOption->getAdditionalAmountWithTax()
                        );
                        continue;
                    }
                    /** @var Mage_Catalog_Model_Product_Option $productOption */
                    $productOption = Mage::getModel("catalog/product_option")->load($optionId);

                    if ($productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
                        || $productOption->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
                    ) {

                        if ($value == "0") {
                            continue;
                        }

                        $value = array($value);
                    }
                    $buyInfo["options"][$optionId] = $value;
                }
            }
        }

        if ($item->getInputs()) {
            foreach ($item->getInputs() as $orderInput) {
                /* @var $orderInput ShopgateOrderItemInput */
                $optionId                      = $orderInput->getInputNumber();
                $value                         = $orderInput->getUserInput();
                $buyInfo["options"][$optionId] = $value;
            }
        }

        $obj = new Varien_Object($buyInfo);

        return $obj;
    }

    /**
     * Add coupons managed by shopgate to the quote
     * These coupons will added as dummy article with negative amount
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _setQuoteShopgateCoupons(Mage_Sales_Model_Quote $quote, ShopgateCartBase $order)
    {
        $this->_getSimpleShopgateCoupons($order);
        return $quote;
    }

    /**
     * @param ShopgateCartBase $order
     * @throws ShopgateLibraryException
     */
    protected function _getSimpleShopgateCoupons(ShopgateCartBase $order)
    {
        if ($order instanceof ShopgateOrder) {
            foreach ($order->getItems() as $item) {
                /** @var ShopgateOrderItem $item */
                if (!$item->isSgCoupon()) {
                    continue;
                }
                $itemAmount = $item->getUnitAmountWithTax();

                $obj = new Varien_Object();
                $obj->setName($item->getName());
                $obj->setItemNumber($item->getItemNumber());
                $obj->setUnitAmountWithTax($itemAmount);
                $obj->setQty($item->getQuantity());
                $this->_virtualObjectStack[] = $obj;
            }
        }
    }

    /**
     * Set the payment for the given quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _setQuotePayment($quote, $order)
    {
        $payment = $this->_getFactory()->getPayment()->getPaymentModel();

        if (!($order instanceof ShopgateOrder)) {
            $quote->getPayment()->setMethod($payment->getCode());

            return $quote;
        }

        $paymentInfo = array();
        $info        = $order->getPaymentInfos();

        if ($order->getPaymentMethod() == ShopgateOrder::COD
            && Mage::getConfig()->getModuleConfig('MSP_CashOnDelivery')->is('active', 'true')
        ) {
            $checkoutSession = Mage::getSingleton('checkout/session');
            $checkoutSession->replaceQuote($quote);
        }

        if ($payment instanceof Shopgate_Framework_Model_Payment_MobilePayment) {
            $this->log("payment is shopgate", ShopgateLogger::LOGTYPE_DEBUG);
            $payment->setShopgateOrder($order);
        }

        if ($payment->getCode() == Mage::getModel("shopgate/payment_mobilePayment")->getCode()) {
            $paymentInfo = $order->getPaymentInfos();
        }

        if ($order->getPaymentMethod() == ShopgateOrder::PREPAY) {
            $paymentInfo["mailing_address"] = $info["purpose"];
        }

        if ($order->getAmountShopPayment() != 0) {
            $paymentInfo["amount_payment"] = $order->getAmountShopPayment();
        }
        $paymentInfo['is_customer_invoice_blocked'] = $order->getIsCustomerInvoiceBlocked();
        $paymentInfo['is_test']                     = $order->getIsTest();
        $paymentInfo['is_paid']                     = $order->getIsPaid();

        $quote->getPayment()->setMethod($payment->getCode());
        $quote->getPayment()->setAdditionalData(serialize($paymentInfo));
        $quote->getPayment()->setAdditionalInformation($paymentInfo);
        $quote->getPayment()->setLastTransId($order->getPaymentTransactionNumber());

        return $this->_getFactory()->getPayment()->prepareQuote($quote, $info);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _setQuoteVirtualItem(Mage_Sales_Model_Quote $quote)
    {
        if (!count($this->_virtualObjectStack)) {
            return $quote;
        }

        $quote->setIsSuperMode(true);

        foreach ($this->_virtualObjectStack as $obj) {
            /* @var $obj Varien_Object */
            $amountWithTax = $obj->getUnitAmountWithTax();

            /* @var $merged Mage_Catalog_Model_Product */
            $merged = $this->_getCouponHelper()->createProductFromShopgateCoupon($obj);

            /* @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = $quote->addProduct($merged, $obj->getQty());
            $quoteItem->setCustomPrice($amountWithTax);
            $quoteItem->setOriginalPrice($amountWithTax);
            $quoteItem->setOriginalCustomPrice($amountWithTax);
            $quoteItem->setNoDiscount(true);
            $quoteItem->setRowWeight(0);
            $quoteItem->setWeeeTaxApplied(serialize(array()));
        }

        return $quote;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     *
     * @return Mage_Sales_Model_Quote
     * @throws ShopgateLibraryException
     */
    protected function _setQuoteCustomer($quote, $order)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer           = Mage::getModel('customer/customer');
        $externalCustomerId = $order->getExternalCustomerId();
        if ($externalCustomerId) {
            $this->log('external customer id: ' . $externalCustomerId, ShopgateLogger::LOGTYPE_DEBUG);
            $customer->load($externalCustomerId);
            if (!$customer->getId()) {
                $this->log(
                    sprintf('customer with external id \'%s\' does not exist', $externalCustomerId)
                );
            } else {
                $quote->setCustomer($customer);

                // also set customer in session some 3rd party plugins rely on it
                Mage::getSingleton('customer/session')
                    ->setCustomer($customer)
                    ->setCustomerId($customer->getId())
                    ->setCustomerGroupId($customer->getGroupId());

                $this->log('external customer loaded', ShopgateLogger::LOGTYPE_DEBUG);
            }
        }
        $invoiceAddress = $order->getInvoiceAddress();
        if ($invoiceAddress) {
            $this->log('invoice address start', ShopgateLogger::LOGTYPE_DEBUG);
            $quote->getBillingAddress()->setShouldIgnoreValidation(true);
            $billingAddressData = $this->_getSalesHelper()->createAddressData(
                $order,
                $order->getInvoiceAddress(),
                true
            );
            $billingAddress     = $quote->getBillingAddress()->addData($billingAddressData);
            $this->log('invoice address end', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $deliveryAddress = $order->getDeliveryAddress();
        if ($deliveryAddress) {
            $this->log('delivery address start', ShopgateLogger::LOGTYPE_DEBUG);

            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            $shippingAddressData = $this->_getSalesHelper()->createAddressData(
                $order,
                $order->getDeliveryAddress(),
                false
            );
            $shippingAddress     = $quote->getShippingAddress()->addData($shippingAddressData);
            $shippingAddress->setSameAsBilling($invoiceAddress->equals($deliveryAddress));

            $this->_getHelper()->setShippingMethod($shippingAddress, $order);
            $this->log('delivery address end', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $quote->setCustomerEmail($order->getMail());
        $this->log('customer email: ' . $order->getMail(), ShopgateLogger::LOGTYPE_DEBUG);

        if ($invoiceAddress) {
            $this->log('invoice address start (names)', ShopgateLogger::LOGTYPE_DEBUG);

            $quote->setCustomerPrefix($quote->getShippingAddress()->getPrefix());
            $quote->setCustomerFirstname($invoiceAddress->getFirstName());
            $quote->setCustomerLastname($invoiceAddress->getLastName());

            $this->log('invoice address end (names)', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $externalCustomerId = $order->getExternalCustomerId();
        if (empty($externalCustomerId)) {
            $this->log('external customer number unavailable', ShopgateLogger::LOGTYPE_DEBUG);
            $quote->setCustomerIsGuest(1);
            $quote->getShippingAddress();
            $quote->getBillingAddress();
        } else {
            $this->log('external customer number available', ShopgateLogger::LOGTYPE_DEBUG);

            $quote->setCustomerIsGuest(0);
            if ($invoiceAddress) {
                $billingAddress->setCustomerAddressId($invoiceAddress->getId());
            }

            if ($deliveryAddress) {
                $shippingAddress->setCustomerAddressId($deliveryAddress->getId());
            }
        }

        Mage::register(
            'rule_data',
            new Varien_Object(
                array(
                    'store_id'          => Mage::app()->getStore()->getId(),
                    'website_id'        => Mage::app()->getStore()->getWebsiteId(),
                    'customer_group_id' => $quote->getCustomerGroupId()
                )
            )
        );

        $quote->setIsActive('0');
        $ip = $order->getCustomerIp() ? $order->getCustomerIp() : 'shopgate.com';
        $quote->setRemoteIp($ip);
        $quote->save();
        $quote->getBillingAddress()->isObjectNew(false);
        $quote->getShippingAddress()->isObjectNew(false);

        return $quote;
    }


    /**
     * Add coupon from this system to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param ShopgateCartBase       $order
     *
     * @return Mage_Sales_Model_Quote
     * @throws ShopgateLibraryException
     */
    protected function _setQuoteShopCoupons($quote, $order)
    {
        $coupons = $order->getExternalCoupons();
        $coupons = $this->_getCouponHelper()->removeCartRuleCoupons($coupons);
        $coupons = $this->_getCouponHelper()->removeAffiliateCoupons($coupons);
        $order->setExternalCoupons($coupons);

        if (count($order->getExternalCoupons()) > 1) {
            throw new ShopgateLibraryException(ShopgateLibraryException::COUPON_TOO_MANY_COUPONS);
        }

        foreach ($order->getExternalCoupons() as $coupon) {
            /* @var $coupon ShopgateShopgateCoupon */
            $couponInfos = $this->jsonDecode($coupon->getInternalInfo(), true);

            if ($order instanceof ShopgateOrder) {

                if (!$coupon->getInternalInfo()) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::COUPON_NOT_VALID,
                        'Field "internal_info" is empty.'
                    );
                }

                if (!isset($couponInfos["coupon_id"])) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::COUPON_NOT_VALID,
                        'Field "coupon_id" in "internal_info" is empty.'
                    );
                }
            }

            $quote->setCouponCode($coupon->getCode());
            foreach ($quote->getAllAddresses() as $address) {
                $address->setCouponCode($coupon->getCode());
            }
            $quote->save();
        }

        if ($this->_getConfig()->applyCartRulesToCart()) {
            $session = Mage::getSingleton('checkout/session');
            $session->replaceQuote($quote);
        }

        return $quote;
    }


    /**
     * array of functions called to create the order
     *
     * @return array
     */
    protected function _getCreateOrderLoaders()
    {
        return array(
            "_setOrderStatusHistory",
            "_setOrderPayment",
            "_setAdditionalOrderInfo",
            "_addCustomFields",
            "_setOrderState",
            "_sendNewOrderMail",
        );
    }

    /**
     * Adds custom fields to order, billing and shipping address
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _addCustomFields(Mage_Sales_Model_Order $magentoOrder, ShopgateOrder $shopgateOrder)
    {
        $magentoOrder = $this->_getHelper()->setCustomFields($magentoOrder, $shopgateOrder);
        $billing      = $magentoOrder->getBillingAddress();
        if ($billing) {
            $this->_getHelper()->setCustomFields($billing, $shopgateOrder->getInvoiceAddress());
            $magentoOrder->setBillingAddress($billing);
        }

        $shipping = $magentoOrder->getShippingAddress();
        if ($shipping) {
            $this->_getHelper()->setCustomFields($shipping, $shopgateOrder->getDeliveryAddress());
            $magentoOrder->setShippingAddress($shipping);
        }

        return $magentoOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _sendNewOrderMail($order)
    {
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_SEND_NEW_ORDER_MAIL)) {
            $order->sendNewOrderEmail();
        }

        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _setAdditionalOrderInfo($order)
    {
        $order->setEmailSent("0");
        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _setOrderStatusHistory($order, $shopgateOrder)
    {
        $order->addStatusHistoryComment($this->_getHelper()->__("[SHOPGATE] Order added by Shopgate."), false);
        $order->addStatusHistoryComment(
            $this->_getHelper()->__(
                "[SHOPGATE] Shopgate order number: %s",
                $shopgateOrder->getOrderNumber()
            ),
            false
        );

        Mage::helper('shopgate/import_order')->printCustomFieldComments($order, $shopgateOrder);

        return $order;
    }

    /**
     * Performs the necessary queries to update an order in the shop system's database.
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_orders#API_Response
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order#API_Response
     *
     * @param ShopgateOrder $order The ShopgateOrder object to be updated in the shop system's database.
     *
     * @return array(
     *           <ul>
     *           <li>'external_order_id' => <i>string</i>, # the ID of the order in your shop system's database</li>
     *           <li>'external_order_number' => <i>string</i> # the number of the order in your shop system</li>
     *           </ul>
     *          )
     * @throws ShopgateLibraryException if an error occurs.
     */
    public function updateOrder(ShopgateOrder $order)
    {
        $this->log('## Start to update Order', ShopgateLogger::LOGTYPE_DEBUG);
        $this->log("## Order-Number: {$order->getOrderNumber()}", ShopgateLogger::LOGTYPE_DEBUG);

        Mage::dispatchEvent('shopgate_update_order_before', array('shopgate_order' => $order));
        $this->log('# Begin database transaction', ShopgateLogger::LOGTYPE_DEBUG);
        Mage::getModel("sales/order")->getResource()->beginTransaction();

        /** @var Shopgate_Framework_Model_Shopgate_Order $shopgateOrder */
        $shopgateOrder = Mage::getModel('shopgate/shopgate_order')->load(
            $order->getOrderNumber(),
            'shopgate_order_number'
        );

        if ($shopgateOrder->getId() == null) {
            $this->log('# order not found', ShopgateLogger::LOGTYPE_DEBUG);
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND);
        }
        $this->log('# Add shopgate order to Session', ShopgateLogger::LOGTYPE_DEBUG);
        Mage::register('shopgate_order', $order, true);
        $this->log('# load Magento-order', ShopgateLogger::LOGTYPE_DEBUG);
        $magentoOrder = $shopgateOrder->getOrder();
        $magentoOrder = $this->_getUpdateOrderLoaders($magentoOrder, $order, $shopgateOrder);
        $magentoOrder->addStatusHistoryComment(
            $this->_getHelper()->__('[SHOPGATE] Order updated by Shopgate.'),
            false
        );

        $magentoOrder->save();
        $this->log('# Commit Transaction', ShopgateLogger::LOGTYPE_DEBUG);
        Mage::getModel('sales/order')->getResource()->commit();
        $this->log('## Order saved successful', ShopgateLogger::LOGTYPE_DEBUG);
        Mage::dispatchEvent(
            'shopgate_update_order_after',
            array(
                'shopgate_order' => $order,
                'order'          => $magentoOrder
            )
        );

        if (!$this->_isValidShipping($magentoOrder, $order, $shopgateOrder)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_STATUS_IS_SENT);
        }

        return array(
            'external_order_id'     => $magentoOrder->getId(),
            'external_order_number' => $magentoOrder->getIncrementId()
        );
    }

    /**
     * update order loaders
     *
     * @param Mage_Sales_Model_Order                  $magentoOrder
     * @param ShopgateOrder                           $shopgateOrder
     * @param Shopgate_Framework_Model_Shopgate_Order $magentoShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getUpdateOrderLoaders($magentoOrder, $shopgateOrder, $magentoShopgateOrder)
    {
        $magentoOrder = $this->_updateOrderPayment($magentoOrder, $shopgateOrder);
        $magentoOrder = $this->_updateOrderShipping($magentoOrder, $shopgateOrder, $magentoShopgateOrder);
        $magentoOrder = $this->_setShopgateOrder($magentoOrder, $shopgateOrder, $magentoShopgateOrder);
        return $magentoOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _updateOrderPayment($magentoOrder, $shopgateOrder)
    {
        if ($shopgateOrder->getUpdatePayment() && $magentoOrder->getTotalDue() > 0) {
            $this->log("# Update payment", ShopgateLogger::LOGTYPE_DEBUG);
            $magentoOrder = $this->_setOrderPayment($magentoOrder, $shopgateOrder);
            $this->log("# Update payment successful", ShopgateLogger::LOGTYPE_DEBUG);
        }
        return $magentoOrder;
    }

    /**
     * @param Mage_Sales_Model_Order                  $magentoOrder
     * @param ShopgateOrder                           $shopgateOrder
     * @param Shopgate_Framework_Model_Shopgate_Order $magentoShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _updateOrderShipping($magentoOrder, $shopgateOrder, $magentoShopgateOrder)
    {
        $updateShipment = false;

        foreach ($magentoOrder->getAllItems() as $orderItem) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getQtyShipped() != $orderItem->getQtyOrdered()) {
                $updateShipment = true;
                break;
            }
        }

        if ($shopgateOrder->getUpdateShipping() && $updateShipment) {
            $this->log("# Update shipping", ShopgateLogger::LOGTYPE_DEBUG);
            $magentoOrder = $this->_setOrderState($magentoOrder, $shopgateOrder);
            if ($this->_isValidShipping($magentoOrder, $shopgateOrder, $magentoShopgateOrder)) {
                $message = "[SHOPGATE] Shipping of this order is not blocked by Shopgate.";
                $magentoOrder->addStatusHistoryComment($this->_getHelper()->__($message), false);
            } else {
                $message = "[SHOPGATE] Shipping of this order is not Blocked anymore!";
                $magentoOrder->addStatusHistoryComment($this->_getHelper()->__($message), false);
            }
        }

        return $magentoOrder;
    }

    /**
     * validate shipping
     *
     * @param Mage_Sales_Model_Order                       $magentoOrder
     * @param ShopgateOrder                                $shopgateOrder
     * @param Shopgate_Framework_Model_Shopgate_Order|NULL $magentoShopgateOrder
     *
     * @return bool
     */
    protected function _isValidShipping($magentoOrder, $shopgateOrder, $magentoShopgateOrder = null)
    {
        $isValidShipping = true;
        if (($shopgateOrder->getIsShippingBlocked() || $magentoShopgateOrder->getIsShippingBlocked())
            && $magentoOrder->getShipmentsCollection()->getSize() > 0
        ) {
            $isValidShipping = false;
        }

        return $isValidShipping;
    }

    /**
     * @param Mage_Sales_Model_Order                  $magentoOrder
     * @param ShopgateOrder                           $shopgateOrder
     * @param Shopgate_Framework_Model_Shopgate_Order $magentoShopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _setShopgateOrder($magentoOrder, $shopgateOrder, $magentoShopgateOrder = null)
    {
        if ($magentoShopgateOrder) {
            if ($shopgateOrder->getUpdatePayment()) {
                $magentoShopgateOrder->setIsPaid($shopgateOrder->getIsPaid());
            }

            if ($shopgateOrder->getUpdateShipping()) {
                $magentoShopgateOrder->setIsShippingBlocked($shopgateOrder->getIsShippingBlocked());
            }
        } else {
            $magentoShopgateOrder = Mage::getModel("shopgate/shopgate_order")
                                        ->setOrderId($magentoOrder->getId())
                                        ->setStoreId($this->_getConfig()->getStoreViewId())
                                        ->setShopgateOrderNumber($shopgateOrder->getOrderNumber())
                                        ->setIsShippingBlocked($shopgateOrder->getIsShippingBlocked())
                                        ->setIsPaid($shopgateOrder->getIsPaid())
                                        ->setIsTest($shopgateOrder->getIsTest())
                                        ->setIsCustomerInvoiceBlocked($shopgateOrder->getIsCustomerInvoiceBlocked());
        }

        $magentoShopgateOrder->setReceivedData(serialize($shopgateOrder));
        $magentoShopgateOrder->save();

        return $magentoOrder;
    }

    /**
     * Sets the state & status of Magento order
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _setOrderState($magentoOrder, $shopgateOrder)
    {
        $magentoOrder = $this->_getFactory()->getPayment()->setOrderStatus($magentoOrder);
        if ($magentoOrder->getShopgateStatusSet()) {
            //do nothing, but we will need to pull this whole thing inside factory
        } elseif ($shopgateOrder->getPaymentMethod() == ShopgateOrder::PREPAY && !$shopgateOrder->getIsPaid()) {
            /**
             * Should stop support for this as this happens when Order is imported as Prepay and defaults to Mobile
             */
            if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_MARK_UNBLOCKED_AS_PAID)
                && !$this->_getFactory()->getPayment()->getPaymentClass()
                && $magentoOrder->canHold()
            ) {
                $magentoOrder->hold();
            }
            $this->_forceIsPaidStatus($magentoOrder, $shopgateOrder);
        } else {
            $stateObject    = new Varien_Object();
            $methodInstance = $magentoOrder->getPayment()->getMethodInstance();
            if (strpos($shopgateOrder->getPaymentMethod(), 'PAYONE') === false) {
                // avoid calling Payone again. Initialization will be removed from here in the nearest future
                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
            }

            if (!$stateObject->getState()) {
                $status = $methodInstance->getConfigData("order_status");

                if ($shopgateOrder->getPaymentMethod() == ShopgateOrder::COD
                    && Mage::getConfig()->getModuleConfig('Phoenix_CashOnDelivery')->is('active', 'true')
                ) {
                    $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
                } elseif ($status) {
                    $stateObject->setState($this->_getHelper()->getStateForStatus($status));
                } else {
                    $stateObject->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                }

                $stateObject->setStatus($status);
            }

            $magentoOrder->setState($stateObject->getState(), $stateObject->getStatus());
            $this->_forceIsPaidStatus($magentoOrder, $shopgateOrder);
        }

        $magentoOrder->save();

        return $magentoOrder;
    }

    /**
     * Run order manipulation with isPaid flag true.
     * Set to Private as this will be refactored.
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    private function _forceIsPaidStatus($magentoOrder, $shopgateOrder)
    {
        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_MARK_UNBLOCKED_AS_PAID)
            && !$shopgateOrder->getIsPaid()
            && $shopgateOrder->getPaymentMethod() != ShopgateOrder::BILLSAFE
        ) {
            $oldStatus = $shopgateOrder->getIsPaid();
            $shopgateOrder->setIsPaid(true);

            $magentoOrder->addStatusHistoryComment(
                $this->_getHelper()->__(
                    "[SHOPGATE] Set order as paid because shipping is not blocked and config is set to 'mark unblocked orders as paid'!"
                ),
                false
            )->setIsCustomerNotified(false);

            $magentoOrder = $this->_setOrderPayment($magentoOrder, $shopgateOrder);

            $shopgateOrder->setIsPaid($oldStatus);
        }

        return $magentoOrder;
    }

    /**
     * Set Payment for the order
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param ShopgateOrder          $shopgateOrder
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _setOrderPayment($magentoOrder, $shopgateOrder = null)
    {
        return $this->_getFactory()->getPayment()->manipulateOrderWithPaymentData($magentoOrder);
    }

    /**
     * Create a Magento cart and the quote
     *
     * @param ShopgateCart $cart
     *
     * @throws ShopgateLibraryException
     * @return Mage_Checkout_Model_Cart
     */
    protected function _createMagentoCartFromShopgateCart(ShopgateCart $cart)
    {
        $mageCart = Mage::getSingleton('checkout/cart');
        /** @var Mage_Sales_Model_Quote $mageQuote */
        $mageQuote = $mageCart->getQuote();
        $this->executeLoaders($this->_getCheckCartQuoteLoaders(), $mageQuote, $cart);
        $mageQuote->getShippingAddress()->setCollectShippingRates(true);

        return $mageCart;
    }

    /**
     * @return array
     */
    protected function _getCheckCartQuoteLoaders()
    {
        return array(
            "_setQuoteClientType",
            "_setQuoteItems",
            "_setQuoteShopgateCoupons",
            "_setQuoteCustomer",
            "_setQuotePayment",
        );
    }

    /**
     * Check coupons for validation
     * Function will throw an ShopgateLibraryException if
     * 1 Count of coupons > 1
     * 2 Coupon is not found
     * 3 Magento throws an exception
     *
     * @param Mage_Checkout_Model_Cart $mageCart
     * @param ShopgateCart             $cart
     *
     * @return ShopgateExternalCoupon[]
     * @throws ShopgateLibraryException
     */
    public function checkCoupons($mageCart, ShopgateCart $cart)
    {
        /* @var $mageQuote Mage_Sales_Model_Quote */
        /* @var $mageCoupon Mage_SalesRule_Model_Coupon */
        /* @var $mageRule Mage_SalesRule_Model_Rule */

        if ($this->_getConfig()->applyCartRulesToCart()) {
            return $this->_getCouponHelper()->checkCouponsAndCartRules(
                $mageCart,
                $cart,
                $this->couponsIncludeTax
            );
        }

        if (!$cart->getExternalCoupons()) {
            return array();
        }

        $externalCoupons    = array();
        $mageQuote          = $mageCart->getQuote();
        $validCouponsInCart = 0;

        foreach ($cart->getExternalCoupons() as $coupon) {
            $externalCoupon = $this->_getCouponHelper()->validateExternalCoupon(
                $coupon,
                $mageQuote,
                $this->couponsIncludeTax
            );
            if ($externalCoupon->getIsValid()) {
                $validCouponsInCart++;
            }
            if ($validCouponsInCart > 1) {
                $errorCode = ShopgateLibraryException::COUPON_TOO_MANY_COUPONS;
                $externalCoupon->setIsValid(false);
                $externalCoupon->setNotValidMessage(ShopgateLibraryException::getMessageFor($errorCode));
            }

            $externalCoupons[] = $externalCoupon;
        }

        return $externalCoupons;
    }

    /**
     * Checks the content of a cart to be valid and returns necessary changes if applicable.
     * This currently only supports the validation of coupons.
     * Affiliate logic is ran after the customer info was added to ShopgateCart
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart#API_Response
     *
     * @param ShopgateCart $cart The ShopgateCart object to be checked and validated.
     *
     * @return array(
     *          'external_coupons' => ShopgateExternalCoupon[], # list of all coupons</li>
     *          'items' => array(...), # list of item changes (not supported yet)</li>
     *          'shippings' => array(...), # list of available shipping services for this cart (not supported yet)</li>
     *         )
     * @throws ShopgateLibraryException if an error occurs.
     */
    public function checkCart(ShopgateCart $cart)
    {
        $affiliateFactory = $this->_getFactory()->getAffiliate($cart);
        $db               = Mage::getSingleton('core/resource')->getConnection('core_write');
        $db->beginTransaction();
        $this->_errorOnInvalidCoupon = false;
        $this->_getCustomerHelper()->addCustomerToCart($cart);
        Mage::register('shopgate_order', $cart, true);
        $mageCart = $this->_createMagentoCartFromShopgateCart($cart);
        $affiliateFactory->setUp($mageCart->getQuote());
        $response = array(
            'currency'         => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'external_coupons' => array(),
            'shipping_methods' => array(),
            'payment_methods'  => array(),
            'items'            => array(),
            'customer'         => $this->_getSalesHelper()->getCustomerData(
                $cart,
                $this->_getConfig()->getStoreViewId()
            )
        );

        $coupons = $this->checkCoupons($mageCart, $cart);
        if ($coupons) {
            $response['external_coupons'] = $coupons;
            $this->log('Coupons loaded', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $shippingMethods = $this->_getSalesHelper()->getShippingMethods($mageCart);
        if ($mageCart->getQuote()->hasItems() && $shippingMethods) {
            $response['shipping_methods'] = $shippingMethods;
            $this->log('Shipping methods loaded', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $items = $this->_getSalesHelper()->getItems($mageCart->getQuote(), $cart);
        if ($items) {
            $response['items'] = $items;
            $this->log('Items loaded', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $paymentMethods = $this->_getSalesHelper()->getPaymentMethods($mageCart);
        if ($paymentMethods) {
            $response['payment_methods'] = $paymentMethods;
            $this->log('Payment methods loaded', ShopgateLogger::LOGTYPE_DEBUG);
        }

        $affiliateCoupons = $affiliateFactory->redeemCoupon($mageCart->getQuote());
        if ($affiliateCoupons) {
            $externalCoupons = $response['external_coupons'];
            $externalCoupons = $this->_getCouponHelper()->removeAffiliateCoupons($externalCoupons);
            $externalCoupons = $this->_getCouponHelper()->mergeAffiliateCoupon($externalCoupons, $affiliateCoupons);
            $this->log('Affiliate coupon loaded', ShopgateLogger::LOGTYPE_DEBUG);
            $response['external_coupons'] = $externalCoupons;
        }
        $affiliateFactory->destroyCookies();
        $db->rollback();

        return $response;
    }

    /**
     * Create a quote and collects stock information
     *
     * @param ShopgateCart $sgCart
     *
     * @see ShopgatePlugin::checkStock()
     *
     * @return array()
     */
    public function checkStock(ShopgateCart $sgCart)
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $db->beginTransaction();

        $mageCart  = Mage::getSingleton('checkout/cart');
        $mageQuote = $this->_setQuoteItems($mageCart->getQuote(), $sgCart);
        $items     = $this->_getSalesHelper()->getItems($mageQuote, $sgCart);

        $db->rollback();

        return $items;
    }

    /** =========================================== CATEGORY EXPORT ================================================= */
    /** =========================================== CATEGORY EXPORT ================================================= */
    /** =========================================== CATEGORY EXPORT ================================================= */

    /**
     * Loads the product categories of the shop system's database and passes them to the buffer.
     * Use ShopgatePlugin::buildDefaultCategoryRow() to get the correct indices for the field names in a Shopgate
     * categories csv and use ShopgatePlugin::addCategoryRow() to add it to the output buffer.
     *
     * @see http://wiki.shopgate.com/CSV_File_Categories
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv
     * @throws ShopgateLibraryException
     */
    protected function createCategoriesCsv()
    {
        $this->log("Start Export Categories...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("Start Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);

        $maxCategoryPosition = Mage::getModel("catalog/category")->getCollection()
                                   ->setOrder('position', 'DESC')
                                   ->getFirstItem()
                                   ->getPosition();

        $this->log("Max Category Position: {$maxCategoryPosition}", ShopgateLogger::LOGTYPE_DEBUG);
        $maxCategoryPosition += 100;

        $categoryExportModel = Mage::getModel('shopgate/export_category_csv');
        $categoryExportModel->setDefaultRow($this->buildDefaultCategoryRow());
        $categoryExportModel->setMaximumPosition($maxCategoryPosition);

        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES)) {
            $storesToExport = $this->_getConfig()->getExportStores(true);
            $groups         = Mage::app()->getWebsite()->getGroups();

            foreach ($groups as $group) {
                /* @var $group Mage_Core_Model_Store_Group */
                $store = null;
                foreach ($group->getStores() as $_store) {
                    /* @var $_store Mage_Core_Model_Store */
                    if (in_array($_store->getId(), $storesToExport)) {
                        $store = $_store;
                        break;
                    }
                }
                if (!$store) {
                    continue;
                }

                $rootCatId = $group->getRootCategoryId();
                $this->log("Root-Category-Id: {$rootCatId}", ShopgateLogger::LOGTYPE_DEBUG);

                $category                    = array();
                $category["category_number"] = $rootCatId;
                $category["category_name"]   = $group->getName();
                $category["url_deeplink"]    = $store->getUrl();
                $this->addCategoryRow($category);

                $this->log("Start Build Category-Tree recursively...", ShopgateLogger::LOGTYPE_DEBUG);
                $this->_buildCategoryTree('csv', $rootCatId);
            }
        } else {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
            $this->log("Root-Category-Id: {$rootCatId}", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log("Start Build Category-Tree recursively...", ShopgateLogger::LOGTYPE_DEBUG);
            $this->_buildCategoryTree('csv', $rootCatId);
        }

        $this->log("End Build Category-Tree Recursively...", ShopgateLogger::LOGTYPE_DEBUG);
        $this->log("Finished Export Categories...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("Finished Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * @param string $type     - type of export
     * @param int    $parentId - children of the parentId category will be pulled
     * @param null   $uIds     - UIDs to limit categories pulled
     */
    protected function _buildCategoryTree($type, $parentId, $uIds = null)
    {
        $this->log('Build Tree with Parent-ID: ' . $parentId, ShopgateLogger::LOGTYPE_DEBUG);

        if (!empty($uIds)) {
            $categories = $uIds;
        } else {
            $category = Mage::getModel('catalog/category');
            $tree     = $category->getTreeModel();
            /** @noinspection PhpParamsInspection */
            $root       = $category->getTreeModel()->load()->getNodeById($parentId);
            $categories = $tree->getChildren($root);
        }

        $maxCategoryPosition = Mage::getModel('catalog/category')->getCollection()
                                   ->setOrder('position', 'DESC')
                                   ->getFirstItem()
                                   ->getPosition();

        $this->log("Max Category Position: {$maxCategoryPosition}", ShopgateLogger::LOGTYPE_DEBUG);
        $maxCategoryPosition += 100;

        if ($this->splittedExport) {
            $categories = array_slice($categories, $this->exportOffset, $this->exportLimit);
            $this->log('Limit: ' . $this->exportLimit, ShopgateLogger::LOGTYPE_ACCESS);
            $this->log('[*] Limit: ' . $this->exportLimit, ShopgateLogger::LOGTYPE_DEBUG);
            $this->log('Offset: ' . $this->exportOffset, ShopgateLogger::LOGTYPE_ACCESS);
            $this->log('[*] Offset: ' . $this->exportOffset, ShopgateLogger::LOGTYPE_DEBUG);
        }

        foreach ($categories as $categoryId) {
            $this->log('Load Category with ID: ' . $categoryId, ShopgateLogger::LOGTYPE_DEBUG);
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($type == 'csv') {
                $categoryExportModel = Mage::getModel('shopgate/export_category_csv');
                $categoryExportModel->setDefaultRow($this->buildDefaultCategoryRow());
                $categoryExportModel->setItem($category);
                $categoryExportModel->setParentId($parentId);
                $categoryExportModel->setMaximumPosition($maxCategoryPosition);
                $this->addCategoryRow($categoryExportModel->generateData());
            } else {
                $categoryExportModel = Mage::getModel('shopgate/export_category_xml');
                $categoryExportModel->setItem($category);
                $categoryExportModel->setParentId($parentId);
                $categoryExportModel->setMaximumPosition($maxCategoryPosition);
                $this->addCategoryModel($categoryExportModel->generateData());
            }
            $this->exportLimit--;

            if ($parentId == $category->getId()) {
                continue;
            }
        }
    }

    /**
     * @param int   $limit
     * @param int   $offset
     * @param array $uids
     */
    protected function createCategories($limit = null, $offset = null, array $uids = null)
    {
        $this->log("Start Export Categories...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("Start Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);

        if (!is_null($limit) && !is_null($offset)) {
            $this->setSplittedExport(true);
            $this->setExportLimit($limit);
            $this->setExportOffset($offset);
        }

        if (Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES)) {
            $storesToExport = $this->_getConfig()->getExportStores(true);
            $groups         = Mage::app()->getWebsite()->getGroups();

            foreach ($groups as $group) {
                /* @var $group Mage_Core_Model_Store_Group */
                $store = null;
                foreach ($group->getStores() as $_store) {
                    /* @var $_store Mage_Core_Model_Store */
                    if (in_array($_store->getId(), $storesToExport)) {
                        $store = $_store;
                        break;
                    }
                }
                if (!$store) {
                    continue;
                }

                $rootCatId = $group->getRootCategoryId();

                $this->log("Start Build Category-Tree recursively...", ShopgateLogger::LOGTYPE_DEBUG);
                $this->_buildCategoryTree('xml', $rootCatId, $uids);
            }
        } else {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
            $this->log("Root-Category-Id: {$rootCatId}", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log("Start Build Category-Tree recursively...", ShopgateLogger::LOGTYPE_DEBUG);
            $this->_buildCategoryTree('xml', $rootCatId, $uids);
        }

        $this->log("End Build Category-Tree Recursively...", ShopgateLogger::LOGTYPE_DEBUG);
        $this->log("Finished Export Categories...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("Finished Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /** ========================================== CATEGORY EXPORT END ============================================== */
    /** ========================================== CATEGORY EXPORT END ============================================== */
    /** ========================================== CATEGORY EXPORT END ============================================== */

    /** ============================================== ITEM EXPORT ================================================== */
    /** ============================================== ITEM EXPORT ================================================== */
    /** ============================================== ITEM EXPORT ================================================== */

    /**
     * Loads the products of the shop system's database and passes them to the buffer.
     * If $this->splittedExport is set to "true", you MUST regard $this->offset and $this->limit when fetching items
     * from the database.
     * Use ShopgatePlugin::buildDefaultItemRow() to get the correct indices for the field names in a Shopgate items
     * csv and use ShopgatePlugin::addItemRow() to add it to the output buffer.
     *
     * @see http://wiki.shopgate.com/CSV_File_Items
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv
     * @throws ShopgateLibraryException
     */
    protected function createItemsCsv()
    {
        $this->log("Export start...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("[*] Export Start...", ShopgateLogger::LOGTYPE_DEBUG);

        $this->setDefaultItemRowOptionCount($this->_getHelper()->getMaxOptionCount());

        $this->log(
            'number of options to be exported: ' . $this->getDefaultItemRowOptionCount(),
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $start      = time();
        $productIds = $this->_getExportProduct(false, $this->exportLimit, $this->exportOffset);
        $oldVersion = $this->_getConfigHelper()->getIsMagentoVersionLower15();
        $i          = 1;

        $productExportModel = Mage::getModel('shopgate/export_product_csv');
        $productExportModel->setDefaultRow($this->buildDefaultItemRow());
        $productExportModel->setDefaultTax($this->_defaultTax);
        foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product')
                           ->setStoreId($this->_getConfig()->getStoreViewId())
                           ->load($productId);
            $this->log("#{$i}", ShopgateLogger::LOGTYPE_DEBUG);
            $i++;
            /** @var Mage_Catalog_Model_Product $product */
            if ($this->_getExportHelper()->productHasRequiredUnsupportedOptions($product)) {
                $this->log(
                    "Exclude Product with ID: {$product->getId()} from CSV: not supported custom option",
                    ShopgateLogger::LOGTYPE_DEBUG
                );
                continue;
            }
            $memoryUsage     = memory_get_usage(true);
            $memoryUsage     = round(($memoryUsage / 1024 / 1024), 2);
            $memoryPeekUsage = memory_get_peak_usage(true);
            $memoryPeekUsage = round(($memoryPeekUsage / 1024 / 1024), 2);

            $this->log(
                "[{$product->getId()}] Start Load Product with ID: {$product->getId()}",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            $this->log("Memory usage: {$memoryUsage} MB", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log("Memory peek usage: {$memoryPeekUsage} MB", ShopgateLogger::LOGTYPE_DEBUG);

            $this->log(
                "[{$product->getId()}] Product-Data:\n" . print_r(
                    array(
                        "id"   => $product->getId(),
                        "name" => $product->getName(),
                        "sku"  => $product->getSku(),
                        "type" => $product->getTypeId(),
                    ),
                    true
                ),
                ShopgateLogger::LOGTYPE_DEBUG
            );

            if ($product->isSuper()) {
                if (!$product->isGrouped()) {
                    // add config parent
                    $this->addItem($productExportModel->generateData($product));
                }
            } else {
                $parentIds = Mage::getModel('catalog/product_type_configurable')
                                 ->getParentIdsByChild($product->getId());
                if (!empty($parentIds)) {
                    foreach ($parentIds as $parentId) {
                        /** @var Mage_Catalog_Model_Product $parentProduct */
                        $parentProduct = Mage::getModel("catalog/product")
                                             ->setStoreId($this->_getConfig()->getStoreViewId())
                                             ->load($parentId);
                        // add config child
                        $this->addItem($productExportModel->generateData($product, $parentProduct));
                        if (!$oldVersion) {
                            $parentProduct->clearInstance();
                        }
                    }
                }
                if ($product->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                    // add simple product
                    $this->addItem($productExportModel->generateData($product));
                }
            }
            if (!$oldVersion) {
                $product->clearInstance();
            }
        }

        $end      = time();
        $duration = $end - $start;

        $this->log("[*] Export duration {$duration} seconds", ShopgateLogger::LOGTYPE_DEBUG);
        $this->log("[*] Export End...", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * Returns the products to export
     *
     * @param bool $isXml - is this an xml export or csv
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    protected function _getExportProduct($isXml = false, $limit = null, $offset = null)
    {
        $collection = $this->_getHelper()->getProductCollection($this->_getConfig()->getStoreViewId(), $isXml);
        $collection->addAttributeToSelect('id');

        $skipIds = $this->config->getExcludeItemIds();
        if (!empty($skipIds)) {
            $collection->addAttributeToFilter('entity_id', array('nin' => $skipIds));
        }

        $collection->addAttributeToFilter('type_id', array('in' => $this->_getConfig()->getExportProductTypes()));

        if (!is_null($limit) && !is_null($offset)) {
            $ids = $collection->getAllIds($limit, $offset);
            $this->log('Limit: ' . $this->exportLimit, ShopgateLogger::LOGTYPE_ACCESS);
            $this->log("[*] Limit: {$this->exportLimit}", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log('Offset: ' . $this->exportOffset, ShopgateLogger::LOGTYPE_ACCESS);
            $this->log("[*] Offset: {$this->exportOffset}", ShopgateLogger::LOGTYPE_DEBUG);
        } else {
            $ids = $collection->getAllIds();
        }

        if (empty($ids) || count($ids) == 1) {
            $this->log(
                "Warning! Low amount of items to export, id's: " . print_r($ids, 1),
                ShopgateLogger::LOGTYPE_DEBUG
            );
        }

        return $ids;
    }

    /**
     * build product row
     *
     * @param      $product
     * @param null $parentItem
     *
     * @return Varien_Object
     */
    protected function _buildProductRow($product, $parentItem = null)
    {
        $item = $this->buildDefaultItemRow();
        $item = $this->executeLoaders($this->getCreateItemsCsvLoaders(), $item, $product, $parentItem);

        return $item;
    }

    /**
     * prepared for xml structure
     *
     * @param  $product
     *
     * @return mixed
     */
    protected function _buildProductItem($product)
    {
        $exportModel = Mage::getModel('shopgate/export_product_xml');
        return $exportModel->setItem($product)->generateData();
    }

    /**
     * Returns default row for item export csv.
     *
     * @return array
     */
    protected function buildDefaultItemRow()
    {
        $row                       = parent::buildDefaultItemRow();
        $row['related_shop_items'] = '';
        return $row;
    }

    /**
     * Item XML export function
     *
     * @param int   $limit
     * @param int   $offset
     * @param array $uids
     */
    protected function createItems($limit = null, $offset = null, array $uids = null)
    {
        $this->log("Export start...", ShopgateLogger::LOGTYPE_ACCESS);
        $this->log("[*] Export Start...", ShopgateLogger::LOGTYPE_DEBUG);

        $this->log(
            'number of options to be exported: ' . $this->getDefaultItemRowOptionCount(),
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $start      = time();
        $productIds = $uids ? $uids : $this->_getExportProduct(true, $limit, $offset);
        $oldVersion = $this->_getConfigHelper()->getIsMagentoVersionLower15();

        $i = 1;

        foreach ($productIds as $productId) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->setStoreId($this->_getConfig()->getStoreViewId())
                           ->load($productId);
            $this->log("#{$i}", ShopgateLogger::LOGTYPE_DEBUG);
            $i++;

            /** @var Mage_Catalog_Model_Product $product */
            if ($this->_getExportHelper()->productHasRequiredUnsupportedOptions($product)) {
                $this->log(
                    "Exclude Product with ID: {$product->getId()} from XML: not supported custom option",
                    ShopgateLogger::LOGTYPE_DEBUG
                );
                continue;
            }

            $memoryUsage     = memory_get_usage(true);
            $memoryUsage     = round(($memoryUsage / 1024 / 1024), 2);
            $memoryPeekUsage = memory_get_peak_usage(true);
            $memoryPeekUsage = round(($memoryPeekUsage / 1024 / 1024), 2);

            $this->log(
                "[{$product->getId()}] Start Load Product with ID: {$product->getId()}",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            $this->log("Memory usage: {$memoryUsage} MB", ShopgateLogger::LOGTYPE_DEBUG);
            $this->log("Memory peek usage: {$memoryPeekUsage} MB", ShopgateLogger::LOGTYPE_DEBUG);

            $this->log(
                "[{$product->getId()}] Product-Data:\n" . print_r(
                    array(
                        "id"   => $product->getId(),
                        "name" => $product->getName(),
                        "sku"  => $product->getSku(),
                        "type" => $product->getTypeId(),
                    ),
                    true
                ),
                ShopgateLogger::LOGTYPE_DEBUG
            );

            $this->addItem($this->_buildProductItem($product));
            if (!$oldVersion) {
                $product->clearInstance();
            }
        }

        $end      = time();
        $duration = $end - $start;

        $this->log("[*] Export duration {$duration} seconds", ShopgateLogger::LOGTYPE_DEBUG);
        $this->log("[*] Export End...", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /** ============================================ ITEM EXPORT END ================================================ */
    /** ============================================ ITEM EXPORT END ================================================ */
    /** ============================================ ITEM EXPORT END ================================================ */

    /** ============================================ REVIEW EXPORT ================================================== */
    /** ============================================ REVIEW EXPORT ================================================== */
    /** ============================================ REVIEW EXPORT ================================================== */

    /**
     * Loads the product reviews of the shop system's database and passes them to the buffer.
     * Use ShopgatePlugin::buildDefaultReviewRow() to get the correct indices for the field names in a Shopgate reviews
     * csv and use ShopgatePlugin::addReviewRow() to add it to the output buffer.
     *
     * @see http://wiki.shopgate.com/CSV_File_Reviews
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv
     * @throws ShopgateLibraryException
     */
    protected function createReviewsCsv()
    {
        $reviews           = $this->_getReviewCollection($this->exportLimit, $this->exportOffset);
        $reviewExportModel = Mage::getModel('shopgate/export_review_csv');
        $reviewExportModel->setDefaultRow($this->buildDefaultReviewRow());
        foreach ($reviews as $review) {
            $this->addReviewRow($reviewExportModel->generateData($review));
        }
    }

    /**
     * xml review creation
     */
    protected function createReviews($limit = null, $offset = null, array $uids = null)
    {
        $reviews = $this->_getReviewCollection($limit, $offset, $uids);
        foreach ($reviews as $review) {
            /** @var Shopgate_Framework_Model_Export_Review_Xml $reviewExportModel */
            $reviewExportModel = Mage::getModel('shopgate/export_review_xml');
            $reviewExportModel->setItem($review);
            $this->addReviewModel($reviewExportModel->generateData());
        }
    }

    /**
     * @param null $limit
     * @param null $offset
     * @param null $uids
     * @return mixed
     */
    protected function _getReviewCollection($limit = null, $offset = null, $uids = null)
    {
        /** @var Mage_Review_Model_Resource_Review_Collection $reviewCollection */
        $reviewCollection = Mage::getModel('review/review')
                                ->getResourceCollection()
                                ->addStoreFilter($this->_getConfig()->getStoreViewId())
                                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
        if (!is_null($uids) && !empty($uids)) {
            $reviewCollection->addFieldToFilter('entity_id', array('in' => explode(',', $uids)));
        } elseif (!is_null($limit) && !is_null($offset)) {
            $reviewCollection
                ->getSelect()
                ->limit($limit, $offset);
        }

        return $reviewCollection
            ->addRateVotes()
            ->setDateOrder();
    }

    /** ========================================== REVIEW EXPORT END ================================================ */
    /** ========================================== REVIEW EXPORT END ================================================ */
    /** ========================================== REVIEW EXPORT END ================================================ */

    /** ========================================== SETTING EXPORT =================================================== */
    /** ========================================== SETTING EXPORT =================================================== */
    /** ========================================== SETTING EXPORT =================================================== */

    /**
     * Returns an array of certain settings of the shop. (Currently mainly tax settings.)
     *
     * @see                           http://wiki.shopgate.com/Shopgate_Plugin_API_get_settings#API_Response
     * @return array(
     *             'tax' => Contains the tax settings as follows:
     *             array(
     *                  'tax_classes_products' => A list of product tax class identifiers.</li>
     *                  'tax_classes_customers' => A list of customer tax classes.</li>
     *                  'tax_rates' => A list of tax rates.</li>
     *                  'tax_rules' => A list of tax rule containers.</li>
     *             )
     *         )
     * @throws ShopgateLibraryException on invalid log in data or hard errors like database failure.
     */
    public function getSettings()
    {
        $settings = array(
            "customer_groups"            => array(),
            "allowed_shipping_countries" => array(),
            "allowed_address_countries"  => array(),
            "payment_methods"            => array(),
            "tax"                        => array(
                "product_tax_classes"  => array(),
                "customer_tax_classes" => array(),
                "tax_rates"            => array(),
                "tax_rules"            => array(),
            )
        );

        $settingsExport = Mage::getModel('shopgate/export_settings')->setDefaultRow($settings);
        return $settingsExport->generateData();
    }

    /**
     * @param bool $bool
     */
    public function setCouponsIncludeTax($bool)
    {
        $this->couponsIncludeTax = $bool;
    }

    /** ========================================= SETTING EXPORT END ================================================ */
    /** ========================================= SETTING EXPORT END ================================================ */
    /** ========================================= SETTING EXPORT END ================================================ */

    /** ========================================= HELPER METHODS START ===============================================*/
    /** ========================================= HELPER METHODS START ===============================================*/
    /** ========================================= HELPER METHODS START ===============================================*/
    /**
     * @return Shopgate_Framework_Helper_Export
     */
    protected function _getExportHelper()
    {
        return Mage::helper('shopgate/export');
    }

    /**
     * @return Shopgate_Framework_Helper_Customer
     */
    protected function _getCustomerHelper()
    {
        return Mage::helper('shopgate/customer');
    }

    /**
     * @return Shopgate_Framework_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('shopgate');
    }

    /**
     * @return Shopgate_Framework_Helper_Sales
     */
    protected function _getSalesHelper()
    {
        return Mage::helper('shopgate/sales');
    }

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('shopgate/config');
    }

    /**
     * @return Shopgate_Framework_Helper_Payment_Abstract
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('shopgate/payment_abstract');
    }

    /**
     * @return Shopgate_Framework_Helper_Coupon
     */
    protected function _getCouponHelper()
    {
        return Mage::helper('shopgate/coupon');
    }

    /**
     * @return null|Shopgate_Framework_Model_Export_Product
     */
    protected function _getExportProductInstance()
    {
        if (!$this->_exportProductInstance) {
            $this->_exportProductInstance = Mage::getModel('shopgate/export_product');
        }
        return $this->_exportProductInstance;
    }

    /** ========================================== HELPER METHODS END ================================================*/
    /** ========================================== HELPER METHODS END ================================================*/
    /** ========================================== HELPER METHODS END ================================================*/

    /** ========================================== GENERAL STUFF START ===============================================*/
    /** ========================================== GENERAL STUFF START ===============================================*/
    /** ========================================== GENERAL STUFF START ===============================================*/

    /**
     * return info for API request to get current state of config values
     *
     * @return array|mixed[]
     */
    public function createPluginInfo()
    {
        $moduleInfo = array(
            'Magento-Version' => Mage::getVersion(),
            'Magento-Edition' => $this->_getConfigHelper()->getEdition(),
            'Magento-StoreId' => Mage::app()->getStore()->getId()
        );

        return $moduleInfo;
    }

    /**
     * get additional data from the magento instance
     *
     * @return array|mixed[]
     */
    public function createShopInfo()
    {
        $shopInfo         = parent::createShopInfo();
        $entitiesCount    = $this->_getHelper()->getEntitiesCount($this->config->getStoreViewId());
        $pluginsInstalled = array('plugins_installed' => $this->_getHelper()->getThirdPartyModules());

        return array_merge($shopInfo, $entitiesCount, $pluginsInstalled);
    }

    /**
     * get debug info
     *
     * @return array|mixed[]
     */
    public function getDebugInfo()
    {
        return Mage::helper('shopgate/debug')->getInfo();
    }

    /** ========================================== GENERAL STUFF END =================================================*/
    /** ========================================== GENERAL STUFF END =================================================*/
    /** ========================================== GENERAL STUFF END =================================================*/

    /**
     * create pages csv
     */
    protected function createPagesCsv()
    {
        // TODO: Implement createPagesCsv() method.
    }

    /**
     * Loads the Media file information to the products of the shop system's database and passes them to the buffer.
     *
     * Use ShopgatePlugin::buildDefaultMediaRow() to get the correct indices for the field names in a Shopgate media csv and
     * use ShopgatePlugin::addMediaRow() to add it to the output buffer.
     *
     * @see http://wiki.shopgate.com/CSV_File_Media#Sample_Media_CSV_file
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_media_csv
     *
     * @throws ShopgateLibraryException
     */
    protected function createMediaCsv()
    {
        // TODO: Implement createMediaCsv() method.
    }

    /**
     * Exports orders from the shop system's database to Shopgate.
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_orders
     *
     * @param string $customerToken
     * @param string $customerLanguage
     * @param int    $limit
     * @param int    $offset
     * @param string $orderDateFrom
     * @param string $sortOrder
     *
     * @return ShopgateExternalOrder[] A list of ShopgateExternalOrder objects
     *
     * @throws ShopgateLibraryException
     */
    public function getOrders(
        $customerToken,
        $customerLanguage,
        $limit = 10,
        $offset = 0,
        $orderDateFrom = '',
        $sortOrder = 'created_desc'
    ) {
        return Mage::getModel('shopgate/export_customer_orders')
                   ->getOrders($customerToken, $limit, $offset, $orderDateFrom, $sortOrder);
    }

    /**
     * Updates and returns synchronization information for the favourite list of a customer.
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_sync_favourite_list
     *
     * @param string             $customerToken
     * @param ShopgateSyncItem[] $items A list of ShopgateSyncItem objects that need to be synchronized
     *
     * @return ShopgateSyncItem[] The updated list of ShopgateSyncItem objects
     */
    public function syncFavouriteList($customerToken, $items)
    {
        // TODO: Implement syncFavouriteList() method.
    }
}
