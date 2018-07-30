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
include_once(Mage::getBaseDir('lib') . '/Shopgate/cart-integration-sdk/shopgate.php');

class Shopgate_Framework_Model_Observer
{
    /**
     * @var Shopgate_Framework_Model_Config
     */
    protected $_config = null;

    /**
     * @var ShopgateMerchantApi
     */
    protected $_merchantApi = null;

    /**
     * Set the shipping status at shopgate of this order
     * $data["order"] should set with an object of Mage_Sales_Model_Order
     * called on event "sales_order_shipment_save_after"
     * called from Mage_Sales_Model_Order_Shipment::save() [after save]
     * Uses the add_order_delivery_note action in ShopgateMerchantApi to add tracking numbers to the order
     * and set_order_shipping_completed action in ShopgateMerchantApi to complete the order in ShopgateMerchantApi
     *
     * @see http://wiki.shopgate.com/Merchant_API_add_order_delivery_note
     * @see http://wiki.shopgate.com/Merchant_API_set_order_shipping_completed
     *
     * @param Varien_Event_Observer $observer
     */
    public function setShippingStatus(Varien_Event_Observer $observer)
    {
        ShopgateLogger::getInstance()->log(
            'Try to set Shipping state for current Order',
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $order = $observer->getEvent()->getShipment()->getOrder();
        if (!$order) {
            $order = $observer->getEvent()->getOrder();
        }
        /** @var Shopgate_Framework_Model_Shopgate_Order $shopgateOrder */
        $shopgateOrder = Mage::getModel('shopgate/shopgate_order')->load($order->getId(), 'order_id');
        if (!$shopgateOrder->getId()) {
            return;
        }

        $errors = 0;

        if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE, $order->getStore())) {
            ShopgateLogger::getInstance()->log('> Plugin is not active, return!', ShopgateLogger::LOGTYPE_DEBUG);

            return;
        }

        $this->_initMerchantApi($order->getStoreId());
        if (!$this->_config->isValidConfig()) {
            ShopgateLogger::getInstance()->log('> Plugin has no valid config data!', ShopgateLogger::LOGTYPE_DEBUG);

            return;
        }

        $orderNumber = $shopgateOrder->getShopgateOrderNumber();

        /** @var $shipments Mage_Sales_Model_Resource_Order_Shipment_Collection */
        $shipments = $order->getShipmentsCollection();
        ShopgateLogger::getInstance()->log(
            "> getTrackCollections from MagentoOrder (count: '" . $shipments->count() . "')",
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $reportedShipments = $shopgateOrder->getReportedShippingCollections();
        foreach ($shipments as $shipment) {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            if (in_array($shipment->getId(), $reportedShipments)) {
                continue;
            }

            /** @var Mage_Sales_Model_Resource_Order_Shipment_Track_Collection $tracks */
            $tracks = $shipment->getTracksCollection();
            ShopgateLogger::getInstance()->log(
                "> getTrackCollections from MagentoOrderShippment (count: '" . $tracks->count() . "')",
                ShopgateLogger::LOGTYPE_DEBUG
            );

            $notes = array();
            if ($tracks->count() == 0) {
                $notes[] = array('service' => ShopgateDeliveryNote::OTHER, 'tracking_number' => '');
            }

            foreach ($tracks as $track) {
                /* @var $track Mage_Sales_Model_Order_Shipment_Track */
                switch ($track->getCarrierCode()) {
                    case 'fedex':
                        $carrier = ShopgateDeliveryNote::FEDEX;
                        break;
                    case 'usps':
                        $carrier = ShopgateDeliveryNote::USPS;
                        break;
                    case 'ups':
                        $carrier = ShopgateDeliveryNote::UPS;
                        break;
                    case 'dhlint':
                    case 'dhl':
                        $carrier = ShopgateDeliveryNote::DHL;
                        break;
                    default:
                        $carrier = ShopgateDeliveryNote::OTHER;
                        break;
                }

                $notes[] = array('service' => $carrier, 'tracking_number' => $track->getNumber());
            }

            foreach ($notes as $note) {
                try {
                    ShopgateLogger::getInstance()->log(
                        "> Try to call SMA::addOrderDeliveryNote (Ordernumber: {$shopgateOrder->getShopgateOrderNumber()} )",
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                    $this->_merchantApi->addOrderDeliveryNote(
                        $shopgateOrder->getShopgateOrderNumber(),
                        $note['service'],
                        $note['tracking_number']
                    );
                    ShopgateLogger::getInstance()->log(
                        '> Call to SMA::addOrderDeliveryNote was successfull!',
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                    $reportedShipments[] = $shipment->getId();
                } catch (ShopgateMerchantApiException $e) {

                    if ($e->getCode() == ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED
                        || $e->getCode() == ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED
                    ) {
                        $reportedShippments[] = $shipment->getId();
                    } else {

                        $errors++;
                        ShopgateLogger::getInstance()->log(
                            "! (#{$orderNumber})  SMA-Error on add delivery note! Message: {$e->getCode()} - {$e->getMessage()}",
                            ShopgateLogger::LOGTYPE_DEBUG
                        );
                        ShopgateLogger::getInstance()->log(
                            "(#{$orderNumber}) SMA-Error on add delivery note! Message: {$e->getCode()} - {$e->getMessage()}",
                            ShopgateLogger::LOGTYPE_ERROR
                        );
                    }
                } catch (Exception $e) {

                    ShopgateLogger::getInstance()->log(
                        "! (#{$orderNumber})  SMA-Error on add delivery note! Message: {$e->getCode()} - {$e->getMessage()}",
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                    ShopgateLogger::getInstance()->log(
                        "(#{$orderNumber}) SMA-Error on add delivery note! Message: {$e->getCode()} - {$e->getMessage()}",
                        ShopgateLogger::LOGTYPE_ERROR
                    );
                    $errors++;
                }
            }
        }

        if (!$this->_completeShipping($shopgateOrder, $order)) {
            $errors++;
        }

        $shopgateOrder->setReportedShippingCollections($reportedShipments);
        $shopgateOrder->save();

        ShopgateLogger::getInstance()->log('> Save data and return!', ShopgateLogger::LOGTYPE_DEBUG);

        if ($errors > 0) {
            Mage::getSingleton('core/session')->addError(
                Mage::helper('shopgate')->__(
                    '[SHOPGATE] Order status was updated but %s errors occurred',
                    $errors['errorcount']
                )
            );
        } else {
            Mage::getSingleton('core/session')->addSuccess(
                Mage::helper('shopgate')->__('[SHOPGATE] Order status was updated successfully at Shopgate')
            );
        }
    }

    /**
     * get config
     *
     * @param $storeId
     */
    protected function _initConfig($storeId = null)
    {
        if ($this->_config == null || ($storeId !== null && $storeId !== $this->_config->getStoreViewId())) {
            $this->_config = Mage::helper('shopgate/config')->getConfig($storeId);
        }
    }

    /**
     * get merchant api
     *
     * @param $storeId
     */
    protected function _initMerchantApi($storeId)
    {
        $this->_initConfig($storeId);
        $builder            = new ShopgateBuilder($this->_config);
        $this->_merchantApi = $builder->buildMerchantApi();
    }

    /**
     * set shipping to complete for the shopgate order model
     *
     * @param $shopgateOrder Shopgate_Framework_Model_Shopgate_Order
     * @param $order         Mage_Sales_Model_Order
     *
     * @return bool
     */
    protected function _completeShipping($shopgateOrder, $order)
    {
        $orderNumber        = $shopgateOrder->getShopgateOrderNumber();
        $isShipmentComplete = $shopgateOrder->hasShippedItems($order);

        if ($shopgateOrder->hasItemsToShip($order)) {
            $isShipmentComplete = false;
        }

        if (Mage::getStoreConfig(
                Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ORDER_CONFIRM_SHIPPING_ON_COMPLETE,
                $order->getStore()
            )
            && $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE
        ) {
            ShopgateLogger::getInstance()->log(
                "> (#{$orderNumber}) Order state is complete and should send to Shopgate",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            $isShipmentComplete = true;
        }

        if (!$isShipmentComplete) {
            ShopgateLogger::getInstance()->log(
                "> (#{$orderNumber}) This order is not shipped completly",
                ShopgateLogger::LOGTYPE_DEBUG
            );

            return true;
        }

        try {
            ShopgateLogger::getInstance()->log(
                "> (#{$orderNumber}) Try to call SMA::setOrderShippingCompleted (Ordernumber: {$shopgateOrder->getShopgateOrderNumber()} )",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            $this->_merchantApi->setOrderShippingCompleted($shopgateOrder->getShopgateOrderNumber());
            ShopgateLogger::getInstance()->log(
                "> (#{$orderNumber}) Call to SMA::setOrderShippingCompleted was successfull!",
                ShopgateLogger::LOGTYPE_DEBUG
            );
        } catch (ShopgateMerchantApiException $e) {
            if ($e->getCode() == ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED
                || $e->getCode() == ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED
            ) {
                Mage::getSingleton('core/session')->addNotice(
                    Mage::helper('shopgate')->__(
                        '[SHOPGATE] The order status is already set to "shipped" at Shopgate!'
                    )
                );
            } else {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('shopgate')->__(
                        '[SHOPGATE] An error occured while updating the shipping status.<br />Please contact Shopgate support.'
                    )
                );
                Mage::getSingleton('core/session')->addError("{$e->getCode()} - {$e->getMessage()}");
                ShopgateLogger::getInstance()->log(
                    "! (#{$orderNumber})  SMA-Error on set shipping complete! Message: {$e->getCode()} - {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_DEBUG
                );
                ShopgateLogger::getInstance()->log(
                    "(#{$orderNumber}) SMA-Error on set shipping complete! Message: {$e->getCode()} - {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_ERROR
                );

                return false;
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError(
                Mage::helper('shopgate')->__(
                    '[SHOPGATE] An unknown error occured!<br />Please contact Shopgate support.'
                )
            );
            ShopgateLogger::getInstance()->log(
                "! (#{$orderNumber}) unknown error on set shipping complete! Message: {$e->getCode()} - {$e->getMessage()}",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            ShopgateLogger::getInstance()->log(
                "(#{$orderNumber}) Unkwon error on set shipping complete! Message: {$e->getCode()} - {$e->getMessage()}",
                ShopgateLogger::LOGTYPE_ERROR
            );

            return false;
        }

        $shopgateOrder->setIsSentToShopgate(true);
        $shopgateOrder->save();

        return true;
    }

    /**
     * full cancel of the order at shopgate
     * $data['order'] should set with an object of Mage_Sales_Model_Order
     * called on event 'order_cancel_after'
     * called from Mage_Sales_Model_Order::cancel()
     * Uses the cancle_order action in ShopgateMerchantApi
     *
     * @see http://wiki.shopgate.com/Merchant_API_cancel_order
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function cancelOrder(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        /* @var $shopgateOrder Shopgate_Framework_Model_Shopgate_Order */
        $shopgateOrder = Mage::getModel('shopgate/shopgate_order')->load($order->getId(), 'order_id');

        if (!$shopgateOrder->getId()) {
            return true;
        }

        if ($order instanceof Mage_Sales_Model_Order) {
            $orderNumber = $shopgateOrder->getShopgateOrderNumber();
            try {

                $this->_initMerchantApi($order->getStoreId());

                // Do nothing if plugin is not active for this store
                if (!Mage::getStoreConfig(
                    Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE,
                    $this->_config->getStoreViewId()
                )
                ) {
                    return $this;
                }

                if (!$this->_config->isValidConfig()) {
                    return $this;
                }

                $cancellationItems = array();
                $qtyCancelled      = 0;

                $rd = $shopgateOrder->getShopgateOrderObject();

                $orderItems = $order->getItemsCollection();

                foreach ($orderItems as $orderItem) {
                    /**  @var $orderItem Mage_Sales_Model_Order_Item */
                    if ($rd instanceof ShopgateOrder) {
                        $rdItem = $this->_findItemByProductId($rd->getItems(), $orderItem->getData('product_id'));
                    } else {
                        throw new ShopgateLibraryException(
                            ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                            "! (#{$orderNumber})  unable to unserialize shopgate order object",
                            true
                        );
                    }

                    if ($orderItem->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                        && $orderItem->getQtyCanceled() + $orderItem->getQtyRefunded() > 0
                        && !$orderItem->getIsVirtual()
                        && $rdItem
                    ) {
                        $mainItem = $orderItem->getParentItem();
                        if (empty($mainItem)
                            || $mainItem->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                        ) {
                            $mainItem = $orderItem;
                        }

                        $cancellationItems[] = array(
                            'item_number' => $rdItem->getItemNumber(),
                            'quantity'    => intval($mainItem->getQtyCanceled()) + intval($mainItem->getQtyRefunded()),
                        );
                        $qtyCancelled        += intval($mainItem->getQtyCanceled()) + intval(
                                $mainItem->getQtyRefunded()
                            );
                    }
                }

                if (count($orderItems) > 0
                    && empty($cancellationItems)
                ) {
                    $order->addStatusHistoryComment(
                        '[SHOPGATE] Notice: Credit memo was not sent to Shopgate because no product quantity was affected.'
                    );
                    $order->save();

                    return true;
                }

                $cancelShippingCosts = !$shopgateOrder->hasShippedItems($order);

                /** @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
                $creditMemo = $observer->getEvent()->getCreditMemo();
                if ($creditMemo) {
                    if ($creditMemo->getShippingAmount() == $order->getShippingAmount()) {
                        $cancelShippingCosts = true;
                    } else {
                        $cancelShippingCosts = false;
                    }
                }

                $fullCancellation = empty($cancellationItems) || $qtyCancelled == $order->getTotalQtyOrdered();
                $fullCancellation = $fullCancellation && $cancelShippingCosts;

                $this->_merchantApi->cancelOrder(
                    $shopgateOrder->getShopgateOrderNumber(),
                    $fullCancellation,
                    $cancellationItems,
                    $cancelShippingCosts,
                    'Order was cancelled in Shopsystem!'
                );

                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('shopgate')->__('[SHOPGATE] Order successfully cancelled at Shopgate.')
                );

                $shopgateOrder->setIsCancellationSentToShopgate(true);
                $shopgateOrder->save();

                if (!$shopgateOrder->getIsSentToShopgate() && !$this->_completeShipping($shopgateOrder, $order)) {
                    $this->_logShopgateError(
                        "! (#{$orderNumber})  not sent to shopgate and shipping not complete",
                        ShopgateLogger::LOGTYPE_ERROR
                    );
                }
            } catch (ShopgateMerchantApiException $e) {

                if ($e->getCode() == '222') {
                    // order already canceled in shopgate
                    $shopgateOrder->setIsCancellationSentToShopgate(true);
                    $shopgateOrder->save();
                } else {
                    // Received error from shopgate server
                    Mage::getSingleton('core/session')->addError(
                        Mage::helper('shopgate')->__(
                            '[SHOPGATE] An error occured while trying to cancel the order at Shopgate.<br />Please contact Shopgate support.'
                        )
                    );

                    Mage::getSingleton('core/session')->addError("Error: {$e->getCode()} - {$e->getMessage()}");

                    $this->_logShopgateError(
                        "! (#{$orderNumber})  SMA-Error on cancel order! Message: {$e->getCode()} -
                     {$e->getMessage()}",
                        ShopgateLogger::LOGTYPE_ERROR
                    );
                    $this->_logShopgateError(
                        "! (#{$orderNumber})  SMA-Error on cancel order! Message: {$e->getCode()} -
                     {$e->getMessage()}",
                        ShopgateLogger::LOGTYPE_DEBUG
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('shopgate')->__(
                        '[SHOPGATE] An unknown error occured!<br />Please contact Shopgate support.'
                    )
                );

                $this->_logShopgateError(
                    "! (#{$orderNumber})  SMA-Error on cancel order! Message: {$e->getCode()} -
                     {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_ERROR
                );
                $this->_logShopgateError(
                    "! (#{$orderNumber})  SMA-Error on cancel order! Message: {$e->getCode()} -
                     {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_DEBUG
                );
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function submitCancellations($observer)
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
        $creditMemo          = $observer->getCreditmemo();
        $data['order']       = $creditMemo->getOrder();
        $data['credit_memo'] = $creditMemo;
        $event               = new Varien_Event($data);
        $observer            = new Varien_Event_Observer();

        $observer->setEvent($event);
        $this->cancelOrder($observer);
    }


    /**
     * @param $items
     * @param $productID
     *
     * @return bool
     */
    protected function _findItemByProductId($items, $productID)
    {

        if (empty($productID) || empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            /** @var $item ShopgateOrderItem */
            $internalOrderInfo = json_decode($item->getInternalOrderInfo(), true);
            if ($internalOrderInfo['product_id'] == $productID) {
                return $item;
            }
        }

        return false;
    }

    /**
     * @param $message
     * @param $type
     */
    protected function _logShopgateError($message, $type)
    {
        ShopgateLogger::getInstance()->log($message, $type);
    }

    /**
     * @param $items
     * @param $id
     *
     * @return bool
     */
    protected function findItemByOriginal($items, $id)
    {
        if (empty($id) || empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            $json_info = $item->getInternalOrderInfo();

            try {
                $object = Mage::helper('shopgate')->getConfig()->jsonDecode($json_info);
            } catch (Exception $e) {
                ShopgateLogger::getInstance()->log(
                    "Product ID (#{$id}) Json parse error! Message: {$e->getCode()} - {$e->getMessage()}",
                    ShopgateLogger::LOGTYPE_ERROR
                );

                return false;
            }

            if ($object->product_id == $id) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Filters coupon with a specific code or free_shipping set.
     * Free shipping is allowed because we cannot export it as a coupon code
     * in check_cart as it will discount UPS/USPS which are not discounted by
     * Magento and therefore will be full price on add_order import.
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeSalesrulesLoaded($observer)
    {
        if (Mage::helper('shopgate')->isShopgateApiRequest()) {
            $this->_initConfig();
            if (!$this->_config->applyCartRulesToCart() || Mage::registry('shopgate_disable_sales_rules')) {
                $collection = $observer->getEvent()->getCollection();
                if ($collection instanceof Mage_SalesRule_Model_Resource_Rule_Collection
                    || $collection instanceof Mage_SalesRule_Model_Mysql4_Rule_Collection
                ) {
                    if (Mage::helper('shopgate/config')->getIsMagentoVersionLower1410()) {
                        $couponType = "coupon_code <> ''";
                    } else {
                        $couponType = 'coupon_type = ' . Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC;
                    }
                    $collection->getSelect()->where($couponType . ' OR simple_free_shipping IN (1,2)');
                }
            }
        }
    }

    /**
     * @param $observer Varien_Event_Observer
     */
    public function deleteShopgateCouponProducts($observer)
    {
        if (Mage::helper('shopgate')->isShopgateApiRequest()) {
            $eventResourceModule = explode('/', $observer->getResourceName());
            $eventResourceModule = count($eventResourceModule) ? $eventResourceModule[0] : 'default';
            /* Prevent collection loading on admin to avoid an error while using flat tables */
            if ((Mage::app()->getStore()->isAdmin() && !$eventResourceModule == 'cron')
                || (!Mage::helper('shopgate')->isShopgateApiRequest() && !$eventResourceModule == 'cron')
            ) {
                return;
            }

            $oldStoreViewId = Mage::app()->getStore()->getId();

            if ($eventResourceModule == 'cron') {
                $storeViewIds = Mage::getModel('core/store')->getCollection()->toOptionArray();
            } else {
                $storeViewIds = array(array('value' => $oldStoreViewId, 'label' => 'current'));
            }

            foreach ($storeViewIds as $storeView) {
                $storeViewId = $storeView['value'];
                Mage::app()->setCurrentStore($storeViewId);

                $collection = Mage::getModel('catalog/product')
                                  ->getResourceCollection()
                                  ->addFieldToFilter('type_id', 'virtual');

                $helper = Mage::helper('shopgate/coupon');

                foreach ($collection->getItems() as $product) {
                    if ($helper->isShopgateCoupon($product)) {
                        Mage::app()->setCurrentStore(0);
                        $product->delete();
                    }
                }
            }
            Mage::app()->setCurrentStore($oldStoreViewId);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catchOAuthRegistration(Varien_Event_Observer $observer)
    {
        $config   = $observer->getConfig();
        $settings = $observer->getSettings();

        if (Mage::app()->getRequest()->getParam('action')
            && Mage::app()->getRequest()->getParam('action') == 'receive_authorization'
            && isset($settings['shop_number'])
        ) {
            $storeViewId = Mage::app()->getRequest()->getParam('storeviewid');

            if (Mage::helper('shopgate/config')->isOAuthShopAlreadyRegistered($settings['shop_number'], $storeViewId)) {
                Mage::throwException(
                    'For the current storeView with id #' . $storeViewId
                    . ' is already a shopnumber set. OAuth registration canceled.'
                );
            }

            $config->setStoreViewId($storeViewId);

            /* pre save shop_number in proper scope to trigger the save mechanisms scope definition algorithm */
            if (!$config->oauthSaveNewShopNumber($settings['shop_number'], $storeViewId)) {
                Mage::throwExecption(
                    'Could not determine proper scope for new shop with number: #' . $settings['shop_number']
                );
            }

            unset($settings['shop_number']);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function setDefaultStoreOnOAuthRegistration(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getParam('action')
            && Mage::app()->getRequest()->getParam('action') == 'receive_authorization'
        ) {
            $storeViewId = Mage::app()->getRequest()->getParam('storeviewid');
            $shopnumber  = Mage::getStoreConfig(
                Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER,
                $storeViewId
            );

            $collection = Mage::getModel('core/config_data')
                              ->getCollection()
                              ->addFieldToFilter('path', Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_NUMBER)
                              ->addFieldToFilter('value', $shopnumber);

            if ($collection->getSize() && $collection->getFirstItem()->getScope() == 'websites') {
                Mage::getConfig()->saveConfig(
                    Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE,
                    $storeViewId,
                    $collection->getFirstItem()->getScope(),
                    $collection->getFirstItem()->getScopeId()
                );
            }
        }
    }

    /**
     * check for updates on rss feed
     */
    public function checkForUpdates()
    {
        $model = Mage::getModel('shopgate/feed');
        $model->checkUpdate();
    }

    /**
     * Copy client type from the persistent quote to the quote address before sales rules are validated
     *
     * @param Varien_Event_Observer $observer
     */
    public function setClientOnAddress(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address       = $observer->getData('quote_address');
        $quoteCartType = $address->getQuote()->getData(Shopgate_Framework_Model_SalesRule_Condition::CART_TYPE);
        $address->setData(Shopgate_Framework_Model_SalesRule_Condition::CART_TYPE, $quoteCartType);
    }

    /**
     * Adds extra parameters for developers
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addDevSystemConfig(Varien_Event_Observer $observer)
    {
        $hasDevParam = Mage::app()->getRequest()->getParam('dev');
        if (!(Mage::getIsDeveloperMode() || $hasDevParam)) {
            return $this;
        }

        /**
         * Hidden API field declaration in Website/Store view
         *
         * @var Mage_Core_Model_Config_Element $optionTab
         */
        $config    = $observer->getConfig();
        $optionTab = $config->getNode('sections/shopgate/groups/option/fields');
        $devConfig = new Mage_Core_Model_Config_Element(
            '
            <fields>
                <customer_number translate="label comment tooltip">
                    <label>Customer number</label>
                    <frontend_type>text</frontend_type>
                    <sort_order>11</sort_order>
                    <show_in_default>0</show_in_default>
                    <show_in_website>1</show_in_website>
                    <show_in_store>1</show_in_store>
                    <validate>validate-number validate-length
                        minimum-length-5
                    </validate>
                </customer_number>
                <shop_number translate="label comment tooltip">
                    <label>Shop number</label>
                    <frontend_type>text</frontend_type>
                    <sort_order>13</sort_order>
                    <show_in_default>0</show_in_default>
                    <show_in_website>1</show_in_website>
                    <show_in_store>1</show_in_store>
                    <validate>validate-number validate-length
                        minimum-length-5
                    </validate>
                </shop_number>
                <api_key translate="label comment tooltip">
                    <label>API key</label>
                    <frontend_type>text</frontend_type>
                    <sort_order>15</sort_order>
                    <show_in_default>0</show_in_default>
                    <show_in_website>1</show_in_website>
                    <show_in_store>1</show_in_store>
                    <validate>validate-alphanum validate-length
                        minimum-length-20 maximum-length-20
                    </validate>
                </api_key>
            </fields>
            '
        );

        $optionTab->extend($devConfig);

        /**
         * Hidden oAuth token declaration in Store View
         *
         * @var Mage_Core_Model_Config_Element $shopgateSection
         */
        $shopgateSection = $config->getNode('sections/shopgate/groups');
        $hiddenGroup     = new Mage_Core_Model_Config_Element(
            '
            <hidden translate="label">
                <label>Developer Only Section</label>
                <sort_order>15</sort_order>
                <show_in_default>0</show_in_default>
                <show_in_website>0</show_in_website>
                <show_in_store>1</show_in_store>
                <fields>
                    <oauth_access_token translate="label tooltip">
                        <label>Oauth Access Token</label>
                        <frontend_type>text</frontend_type>
                        <sort_order>10</sort_order>
                        <show_in_default>0</show_in_default>
                        <show_in_website>0</show_in_website>
                        <show_in_store>1</show_in_store>
                        <tooltip>Use Shopgate connect button unless you know what you are doing</tooltip>
                    </oauth_access_token>
                </fields>
            </hidden>
            '
        );
        $shopgateSection->appendChild($hiddenGroup);

        return $this;
    }

    /**
     * Triggers when order becomes paid.
     * Main purpose is to set a different sequence number
     * after we capture an invoice. Else we cannot refund
     * because PayOne plugin is not setting it correctly.
     *
     * @param Varien_Event_Observer $event
     *
     * @return $this
     * @throws Exception
     */
    public function preparePayoneOrderForRefund(Varien_Event_Observer $event)
    {
        $active = Mage::getConfig()->getModuleConfig('Payone_Core')->is('active', 'true');
        if (!$active) {
            return $this;
        }

        $factory = Mage::getModel('payone_core/factory');
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice     = $event['invoice'];
        $transaction = $factory->getModelTransaction();
        $transStatus = $factory->getModelApi();
        if ($transaction->load($invoice->getTransactionId(), 'txid')->hasData()) {
            $collection = $transStatus
                ->getCollection()
                ->addFieldToFilter('order_id', $transaction->getOrderId())
                ->addFieldToFilter('response', 'APPROVED')
                ->addFieldToFilter('request', 'capture');
            /**
             * If we have an approval on capture in API
             * call database, set next sequence as refund
             */
            if ($collection->getSize() > 0) {
                $transaction->setLastSequencenumber(1);
                $transaction->save();
            }
        }

        return $this;
    }

    /**
     * Deletes this website's default store set
     *
     * @param Varien_Event_Observer $event
     *
     * @return $this
     */
    public function removeDefaultStore(Varien_Event_Observer $event)
    {
        $websiteId = $event->getData('store')->getWebsiteId();
        if ($websiteId) {
            Mage::getModel('core/config')->deleteConfig(
                Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE,
                'websites',
                $websiteId
            );
        }

        return $this;
    }

    /**
     * Produces an info block in the Order View panel
     *
     * @param Varien_Event_Observer $observer
     */
    public function getSalesOrderViewShopgateNotice(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Sales_Order_View_Info $block */
        $block = $observer->getData('block');
        if ($block->getNameInLayout() === 'order_info') {
            $child     = $block->getChild('shopgate_payment_notice');
            $transport = $observer->getData('transport');
            if ($child && $transport) {
                $html = $transport->getData('html');
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }

    /**
     * Adds a shopgate cart type to the shopping
     * cart price rule condition list
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function addConditionToSalesRule(Varien_Event_Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = (array)$additional->getConditions();

        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'label' => Mage::helper('shopgate')->__('Shopgate Rules'),
                    'value' => array(
                        array(
                            'label' => Mage::helper('shopgate')->__('Cart Type'),
                            'value' => 'shopgate/salesRule_condition'
                        )
                    )
                ),
            )
        );

        $additional->setConditions($conditions);
        $observer->setAdditional($additional);

        return $observer;
    }
}
