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

include_once Mage::getBaseDir("lib") . '/Shopgate/shopgate.php';

class Shopgate_Framework_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Shopgate_Framework_Model_Config
     */
    protected $_config;

    protected $_mage14xStates = array(
        Mage_Sales_Model_Order::STATE_NEW             => "pending",
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT => "pending",
        Mage_Sales_Model_Order::STATE_PROCESSING      => "processing",
        Mage_Sales_Model_Order::STATE_COMPLETE        => "complete",
        Mage_Sales_Model_Order::STATE_CLOSED          => "closed",
        Mage_Sales_Model_Order::STATE_CANCELED        => "canceled",
        Mage_Sales_Model_Order::STATE_HOLDED          => "holded",
    );

    /**
     * Helps with status for lower versions
     */
    public function __construct()
    {
        if (Mage::helper("shopgate/config")->getIsMagentoVersionLower1410()) {
            $this->_mage14xStates['payment_review'] = Mage_Sales_Model_Order::STATE_HOLDED;
            $this->_mage14xStates['fraud']          = Mage_Sales_Model_Order::STATE_HOLDED;
        } else {
            $this->_mage14xStates[Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW] = "payment_review";
        }
    }

    /**
     * get QR Code directory
     *
     * @return string
     */
    public function getRelativeQrCodeDir()
    {
        return "/media/shopgate/qrcodes/" . $this->getConfig()->getShopNumber();
    }

    /**
     * Return ISO-Code for Magento address
     *
     * @param Varien_Object $address
     *
     * @return null|string
     */
    public function getIsoStateByMagentoRegion(Varien_Object $address)
    {
        $map      = $this->_getIsoToMagentoMapping();
        $sIsoCode = null;

        if ($address->getCountryId() && $address->getRegionCode()) {
            $sIsoCode = $address->getCountryId() . "-" . $address->getRegionCode();
        }

        if (isset($map[$address->getCountryId()])) {
            foreach ($map[$address->getCountryId()] as $isoCode => $mageCode) {
                if ($mageCode === $address->getRegionCode()) {
                    $sIsoCode = $address->getCountryId() . "-" . $isoCode;
                    break;
                }
            }
        }

        return $sIsoCode;
    }

    /**
     * Magento default supported countries:
     * DE, AT, CH, CA, EE, ES, FI, FR, LT, LV, RO, US
     * Countries with correct iso-codes for region:
     * US, CA, CH, EE, FR, RO
     * Countries with incorrect iso-codes for region:
     * DE, AT, ES, FI, LT, LV
     * http://de.wikipedia.org/wiki/ISO_3166-2:DE
     * http://de.wikipedia.org/wiki/ISO_3166-2:AT
     * http://de.wikipedia.org/wiki/ISO_3166-2:ES
     * http://de.wikipedia.org/wiki/ISO_3166-2:FI
     * http://de.wikipedia.org/wiki/ISO_3166-2:LT
     * http://de.wikipedia.org/wiki/ISO_3166-2:LV
     *
     * @return array
     */
    protected function _getIsoToMagentoMapping()
    {
        $map = array(
            'DE' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:DE */
                'BW' => 'BAW',
                'BY' => 'BAY',
                'BE' => 'BER',
                'BB' => 'BRG',
                'HB' => 'BRE',
                'HH' => 'HAM',
                'HE' => 'HES',
                'MV' => 'MEC',
                'NI' => 'NDS',
                'NW' => 'NRW',
                'RP' => 'RHE',
                'SL' => 'SAR',
                'SN' => 'SAS',
                'ST' => 'SAC',
                'SH' => 'SCN',
                'TH' => 'THE'
            ),
            'AT' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:AT */
                '1' => 'BL',
                '2' => 'KN',
                '3' => 'NO',
                '4' => 'OO',
                '5' => 'SB',
                '6' => 'ST',
                '7' => 'TI',
                '8' => 'VB',
                '9' => 'WI',
            ),
            'ES' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:ES */
                'C'  => 'A Coruсa',
                'VI' => 'Alava',
                'AB' => 'Albacete',
                'A'  => 'Alicante',
                'AL' => 'Almeria',
                'O'  => 'Asturias',
                'AV' => 'Avila',
                'BA' => 'Badajoz',
                'PM' => 'Baleares',
                'B'  => 'Barcelona',
                'BU' => 'Burgos',
                'CC' => 'Caceres',
                'CA' => 'Cadiz',
                'CS' => 'Castellon',
                'GI' => 'Girona',
                'CO' => 'Cordoba',
                'CU' => 'Cuenca',
                'GR' => 'Granada',
                'GU' => 'Guadalajara',
                'SS' => 'Guipuzcoa',
                'H'  => 'Huelva',
                'HU' => 'Huesca',
                'J'  => 'Jaen',
                'CR' => 'Ciudad Real',
                'S'  => 'Cantabria',
                'LO' => 'La Rioja',
                'GC' => 'Las Palmas',
                'LE' => 'Leon',
                'L'  => 'Lleida',
                'LU' => 'Lugo',
                'M'  => 'Madrid',
                'MA' => 'Malaga',
                'MU' => 'Murcia',
                'NA' => 'Navarra',
                'OR' => 'Ourense',
                'P'  => 'Palencia',
                'PO' => 'Pontevedra',
                'SA' => 'Salamanca',
                'TF' => 'Santa Cruz de Tenerife',
                'Z'  => 'Zaragoza',
                'SG' => 'Segovia',
                'SE' => 'Sevilla',
                'SO' => 'Soria',
                'T'  => 'Tarragona',
                'TE' => 'Teruel',
                'TO' => 'Toledo',
                'V'  => 'Valencia',
                'VA' => 'Valladolid',
                'BI' => 'Vizcaya',
                'ZA' => 'Zamora',
                'CE' => 'Ceuta',
                'ML' => 'Melilla',
            ),
            'LT' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:LT */
                'AL' => 'LT-AL',
                'KU' => 'LT-KU',
                'KL' => 'LT-KL',
                'MR' => 'LT-MR',
                'PN' => 'LT-PN',
                'SA' => 'LT-SA',
                'TA' => 'LT-TA',
                'TE' => 'LT-TE',
                'UT' => 'LT-UT',
                'VL' => 'LT-VL',
            ),
            'FI' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:FI */
                "01" => "Ahvenanmaa",
                "02" => "Etelä-Karjala",
                "03" => "Etelä-Pohjanmaa",
                "04" => "Etelä-Savo",
                "05" => "Kainuu",
                "06" => "Kanta-Häme",
                "07" => "Keski-Pohjanmaa",
                "08" => "Keski-Suomi",
                "09" => "Kymenlaakso",
                "10" => "Lappi",
                "11" => "Pirkanmaa",
                "12" => "Pohjanmaa",
                "13" => "Pohjois-Karjala",
                "14" => "Pohjois-Pohjanmaa",
                "15" => "Pohjois-Savo",
                "16" => "Päijät-Häme",
                "17" => "Satakunta",
                "18" => "Uusimaa",
                "19" => "Varsinais-Suomi",
                "00" => "Itä-Uusimaa", // !!not listet in wiki
            ),
            'LV' => array(
                /* @see http://de.wikipedia.org/wiki/ISO_3166-2:LV */
                /* NOTE: 045 and 063 does not exist in magento */
                "001" => "Aglonas novads",
                "002" => "AI",
                "003" => "Aizputes novads",
                "004" => "Aknīstes novads",
                "005" => "Alojas novads",
                "006" => "Alsungas novads",
                "007" => "AL",
                "008" => "Amatas novads",
                "009" => "Apes novads",
                "010" => "Auces novads",
                "011" => "Ādažu novads",
                "012" => "Babītes novads",
                "013" => "Baldones novads",
                "014" => "Baltinavas novads",
                "015" => "BL",
                "016" => "BU",
                "017" => "Beverīnas novads",
                "018" => "Brocēnu novads",
                "019" => "Burtnieku novads",
                "020" => "Carnikavas novads",
                "021" => "Cesvaines novads",
                "022" => "CE",
                "023" => "Ciblas novads",
                "024" => "Dagdas novads",
                "025" => "DA",
                "026" => "DO",
                "027" => "Dundagas novads",
                "028" => "Durbes novads",
                "029" => "Engures novads",
                "030" => "Ērgļu novads",
                "031" => "Garkalnes novads",
                "032" => "Grobiņas novads",
                "033" => "GU",
                "034" => "Iecavas novads",
                "035" => "Ikšķiles novads",
                "036" => "Ilūkstes novads",
                "037" => "Inčukalna novads",
                "038" => "Jaunjelgavas novads",
                "039" => "Jaunpiebalgas novads",
                "040" => "Jaunpils novads",
                "041" => "JL",
                "042" => "JK",
                "043" => "Kandavas novads",
                "044" => "Kārsavas novads",
                /*"045" => "",*/
                "046" => "Kokneses novads",
                "047" => "KR",
                "048" => "Krimuldas novads",
                "049" => "Krustpils novads",
                "050" => "KU",
                "051" => "Ķeguma novads",
                "052" => "Ķekavas novads",
                "053" => "Lielvārdes novads",
                "054" => "LM",
                "055" => "Līgatnes novads",
                "056" => "Līvānu novads",
                "057" => "Lubānas novads",
                "058" => "LU",
                "059" => "MA",
                "060" => "Mazsalacas novads",
                "061" => "Mālpils novads",
                "062" => "Mārupes novads",
                /*"063" => "",*/
                "064" => "Naukšēnu novads",
                "065" => "Neretas novads",
                "066" => "Nīcas novads",
                "067" => "OG",
                "068" => "Olaines novads",
                "069" => "Ozolnieku novads",
                "070" => "Pārgaujas novads",
                "071" => "Pāvilostas novads",
                "072" => "Pļaviņu novads",
                "073" => "PR",
                "074" => "Priekules novads",
                "075" => "Priekuļu novads",
                "076" => "Raunas novads",
                "077" => "RE",
                "078" => "Riebiņu novads",
                "079" => "Rojas novads",
                "080" => "Ropažu novads",
                "081" => "Rucavas novads",
                "082" => "Rugāju novads",
                "083" => "Rundāles novads",
                "084" => "Rūjienas novads",
                "085" => "Salas novads",
                "086" => "Salacgrīvas novads",
                "087" => "Salaspils novads",
                "088" => "SA",
                "089" => "Saulkrastu novads",
                "090" => "Sējas novads",
                "091" => "Siguldas novads",
                "092" => "Skrīveru novads",
                "093" => "Skrundas novads",
                "094" => "Smiltenes novads",
                "095" => "Stopiņu novads",
                "096" => "Strenču novads",
                "097" => "TA",
                "098" => "Tērvetes novads",
                "099" => "TU",
                "100" => "Vaiņodes novads",
                "101" => "VK",
                "102" => "Varakļānu novads",
                "103" => "Vārkavas novads",
                "104" => "Vecpiebalgas novads",
                "105" => "Vecumnieku novads",
                "106" => "VE",
                "107" => "Viesītes novads",
                "108" => "Viļakas novads",
                "109" => "Viļānu novads",
                "110" => "Zilupes novads",
                // cities
                "DGV" => "LV-DGV",
                "JKB" => "Jēkabpils",
                "JEL" => "LV-JEL",
                "JUR" => "LV-JUR",
                "LPX" => "LV-LPX",
                "REZ" => "LV-REZ",
                "RIX" => "LV-RIX",
                "VMR" => "Valmiera",
                "VEN" => "LV-VEN",
                // Unknown
                // "" => "LV-LE", "" => "LV-RI", "" => "LV-VM",
            ),
        );

        return $map;
    }

    /**
     * show module version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return Mage::getConfig()->getModuleConfig("Shopgate_Framework")->version;
    }

    /**
     * Returns the state depends on given status
     *
     * @param string $status
     *
     * @return string
     */
    public function getStateForStatus($status)
    {
        if (Mage::helper("shopgate/config")->getIsMagentoVersionLower15() ||
            (Mage::helper("shopgate/config")->getEdition() == 'Enterprise' &&
             version_compare(Mage::getVersion(), '1.9.1.2', '<'))
        ) {
            return $this->_getStateFromStatusMagento14x($status);
        }

        $resource = Mage::getSingleton('core/resource');
        $db       = $resource->getConnection('core_read');
        $table    = $resource->getTableName('sales/order_status_state');
        $result   = $db->fetchOne("SELECT state FROM {$table} WHERE status = '{$status}' AND is_default = 1");
        if (!$result) {
            $result = $db->fetchOne("SELECT state FROM {$table} WHERE status = '{$status}'");
        }
        return $result;
    }

    /**
     * Returns status from state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStatusFromState($state)
    {
        if ($this->_getConfigHelper()->getIsMagentoVersionLower15() ||
            ($this->_getConfigHelper()->getEdition() == 'Enterprise' &&
             version_compare(Mage::getVersion(), '1.9.1.2', '<'))
        ) {
            return $this->_getStatusFromStateMagento14x($state);
        }

        $resource = Mage::getSingleton('core/resource');
        $db       = $resource->getConnection('core_read');
        $table    = $resource->getTableName('sales/order_status_state');
        $result   = $db->fetchOne("SELECT state FROM {$table} WHERE state = '{$state}' AND is_default = 1");
        if (!$result) {
            $result = $db->fetchOne("SELECT state FROM {$table} WHERE state = '{$state}'");
        }
        if (!$result) {
            $result = $this->_getStatusFromStateMagento14x($state);
        }
        return $result;
    }

    /**
     * return the sate of status for magento 1.4
     * if status not in mapping-array state will set to status!
     *
     * @param $status string
     *
     * @return string
     */
    protected function _getStateFromStatusMagento14x($status)
    {
        $state = array_search($status, $this->_mage14xStates);
        return $state !== false ? $state : Mage_Sales_Model_Order::STATE_PROCESSING;
    }

    /**
     * Mage 1.4 non DB support for all states
     *
     * @param $state - status returned
     *
     * @return string
     */
    protected function _getStatusFromStateMagento14x($state)
    {
        return isset($this->_mage14xStates[$state]) ? $this->_mage14xStates[$state] : Mage_Sales_Model_Order::STATE_PROCESSING;
    }

    /**
     * returns true if the current request is from shopgate
     * if action is set it will also checked
     *
     * @param string|null $action
     *
     * @return boolean
     */
    public function isShopgateApiRequest($action = null)
    {
        $isShopgateRequest = defined("_SHOPGATE_API") && _SHOPGATE_API;
        if ($isShopgateRequest && defined("_SHOPGATE_ACTION") && $action) {
            $isShopgateRequest = $isShopgateRequest && ($action == _SHOPGATE_ACTION);
        }

        return $isShopgateRequest;
    }

    /**
     * check if order total is correct
     *
     * @param ShopgateOrder          $order
     * @param Mage_Sales_Model_Order $oMageOrder
     * @param string                 $message
     *
     * @return bool
     */
    public function isOrderTotalCorrect(ShopgateOrder $order, Mage_Sales_Model_Order $oMageOrder, &$message = "")
    {
        $totalShopgate = $order->getAmountComplete();
        $totalMagento  = $oMageOrder->getTotalDue() + $oMageOrder->getTotalPaid();

        $msg = "\tShopgate:\t{$totalShopgate} {$order->getCurrency()} \n";
        $msg .= "\tMagento:\t{$totalMagento} {$oMageOrder->getOrderCurrencyCode()}\n";

        ShopgateLogger::getInstance()->log($msg, ShopgateLogger::LOGTYPE_DEBUG);

        if (abs($totalShopgate - $totalMagento) > 0.02) {
            $msg = "differing total order amounts:\n" . $msg;
            $msg .= "\tMagento Order #\t{$oMageOrder->getIncrementId()} \n";
            $msg .= "\tShopgate Order #\t{$order->getOrderNumber()} \n";

            $message = $msg;
            return false;
        }

        return true;
    }

    /**
     * Sets the correct Shipping Carrier and Method
     * in relation to
     *        ShopgateOrder->{shipping_group} [carrier]
     *        ShopgateOrder->{shipping_info}    [method]
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param ShopgateCartBase               $order
     */
    public function setShippingMethod(Mage_Sales_Model_Quote_Address $shippingAddress, ShopgateCartBase $order)
    {
        /* dont set shipping method when the order does not contain any shipping information (e.g. checkCart) */
        if (!$order->getShippingGroup()) {
            ShopgateLogger::getInstance()
                          ->log(
                              "# setShippingMethod skipped, no Shipping information in " . get_class(
                                  $order
                              ) . " available",
                              ShopgateLogger::LOGTYPE_DEBUG
                          );
            return;
        }

        ShopgateLogger::getInstance()->log("# Start of setShippingMethod process", ShopgateLogger::LOGTYPE_DEBUG);
        $infos = $order->getShippingInfos();
        if ($order->getShippingType() == Shopgate_Framework_Model_Shopgate_Shipping_Mapper::SHIPPING_TYPE_PLUGINAPI
            && $infos->getName()
        ) {
            $shippingMethod = $infos->getName();
        } else {
            $mapper         = Mage::getModel('shopgate/shopgate_shipping_mapper')->init($shippingAddress, $order);
            $shippingMethod = $mapper->getCarrier() . '_' . $mapper->getMethod();
        }
        $shippingAddress->setShippingMethod($shippingMethod);

        ShopgateLogger::getInstance()->log(
            "  Shipping method set: '" . $shippingAddress->getShippingMethod() . "'",
            ShopgateLogger::LOGTYPE_DEBUG
        );
        ShopgateLogger::getInstance()->log("# End of setShippingMethod process", ShopgateLogger::LOGTYPE_DEBUG);
    }

    /**
     * return the right store id by shop number
     *
     * @param $shopNumber
     *
     * @return array
     */
    public function getStoreIdByShopNumber($shopNumber)
    {
        /** @var Shopgate_Framework_Model_Resource_Core_Config $configModel */
        $configModel = Mage::getResourceSingleton('shopgate/core_config');
        $config      = $configModel->getConfigDataByWebsite('shopgate/option/shop_number', array($shopNumber));
        if (!empty($config)) {
            $configKeys      = array_keys($config);
            $scopeShopNumber = array_shift($configKeys);
            $defaultStore    = $configModel->getConfigDataByWebsite('shopgate/option/default_store');
            if (!empty($defaultStore)) {
                $defaultStoreKeys = array_keys($defaultStore);
                if (array_search($scopeShopNumber, $defaultStoreKeys) !== false
                    && isset($defaultStore[$scopeShopNumber])
                ) {
                    return $defaultStore[$scopeShopNumber];
                }
            }
        }
        return null;
    }

    /**
     * get library version
     *
     * @return string
     */
    public function getLibraryVersion()
    {
        if (!defined(SHOPGATE_LIBRARY_VERSION)) {
            Mage::helper('shopgate/config')->getConfig();
        }
        return SHOPGATE_LIBRARY_VERSION;
    }

    /**
     * @param int $storeViewId
     *
     * @return Shopgate_Framework_Model_Config
     */
    public function getConfig($storeViewId = null)
    {
        if (!$this->_config) {
            $this->_config = Mage::helper('shopgate/config')->getConfig($storeViewId);
        }
        return $this->_config;
    }


    /**
     * retrieves the maximal count of options for all bundles
     *
     * @return integer
     */
    protected function _getMaxBundleOptionCount()
    {
        $mainTable = $resource = Mage::getModel('bundle/option')
                                     ->getResource()
                                     ->getMainTable();

        $connection = Mage::getModel('bundle/option')
                          ->getResource()
                          ->getReadConnection();

        $entries = $connection->select()
                              ->from("$mainTable", array("count" => "count(*)"))
                              ->group(array("parent_id"))
                              ->query()
                              ->fetchAll();

        $entries[] = array('count' => 0);
        $result    = max($entries);
        return count($entries) ? array_shift($result) : 0;
    }

    /**
     * retrieves the maximal count of custom options for all products
     *
     * @return integer
     */
    protected function _getMaxCustomOptionCount()
    {
        $mainTable = $resource = Mage::getModel('catalog/product_option')
                                     ->getResource()
                                     ->getMainTable();

        $connection = Mage::getModel('catalog/product_option')
                          ->getResource()
                          ->getReadConnection();

        $entries = $connection->select()
                              ->from("$mainTable", array("count" => "count(*)"))
                              ->group(array("product_id"))
                              ->query()
                              ->fetchAll();

        $entries[] = array('count' => 0);
        $result    = max($entries);
        return count($entries) ? array_shift($result) : 0;
    }

    /**
     * retrieves the maximal count of options for all products
     *
     * @return integer
     */
    public function getMaxOptionCount()
    {
        return max(array($this->_getMaxBundleOptionCount(), $this->_getMaxCustomOptionCount()));
    }


    /**
     * Generates a ShopgateCartItem for the checkCart Response
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean                    $isBuyable
     * @param int                        $qtyBuyable
     * @param int|float                  $priceExclTax
     * @param int|float                  $priceInclTax
     * @param array                      $errors
     * @param bool                       $stockQuantity
     *
     * @return ShopgateCartItem
     */
    public function generateShopgateCartItem(
        $product,
        $isBuyable = false,
        $qtyBuyable = 0,
        $priceInclTax = 0,
        $priceExclTax = 0,
        $errors = array(),
        $stockQuantity = false
    ) {

        $item = new ShopgateCartItem();
        $item->setItemNumber($product->getShopgateItemNumber());
        $item->setOptions($product->getShopgateOptions());
        $item->setInputs($product->getShopgateInputs());
        $item->setAttributes($product->getShhopgateAttributes());
        $item->setIsBuyable((int)$isBuyable);
        $item->setQtyBuyable($qtyBuyable);
        $item->setStockQuantity($stockQuantity);
        $item->setUnitAmount(round($priceExclTax, 4));
        $item->setUnitAmountWithTax(round($priceInclTax, 4));

        foreach ($errors as $error) {
            $item->setError($error['type']);
            $item->setErrorText($error['message']);
        }

        return $item;
    }

    /**
     * Translates one shopgate item into the other
     *
     * @param ShopgateOrderItem $orderItem
     * @param int               $errorCode - Shopgate library error code
     * @param bool              $isPurchasable
     * @param int               $qtyPurchasable
     * @param bool              $stockQuantity
     *
     * @return ShopgateCartItem
     */
    public function getCartItemFromOrderItem(
        ShopgateOrderItem $orderItem,
        $errorCode = 0,
        $isPurchasable = false,
        $qtyPurchasable = 0,
        $stockQuantity = false
    ) {
        $cartItem = new ShopgateCartItem($orderItem->toArray());
        $cartItem->setIsBuyable((int)$isPurchasable);
        $cartItem->setQtyBuyable($qtyPurchasable);
        $cartItem->setStockQuantity($stockQuantity);
        if (!empty($errorCode)) {
            $cartItem->setError($errorCode);
            $cartItem->setErrorText(ShopgateLibraryException::getMessageFor($errorCode));
        }

        return $cartItem;
    }

    /**
     * Fetches the count of all entities
     *
     * @param int|string $storeViewId
     *
     * @return array
     */
    public function getEntitiesCount($storeViewId)
    {
        $store         = Mage::getModel('core/store')->load($storeViewId);
        $categoryCount = (int)Mage::getResourceModel('catalog/category')
                                  ->getChildrenCount($store->getRootCategoryId());
        $productCount  = (int)$this->getProductCollection($storeViewId)
                                   ->addAttributeToSelect('id')
                                   ->getSize();
        $reviewCount   = 0;
        if (Mage::getConfig()->getModuleConfig('Mage_Review')->is('active', 'true')) {
            $reviewCount = (int)Mage::getModel('review/review')
                                    ->getCollection()
                                    ->addStoreFilter($storeViewId)
                                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                                    ->getSize();
        }

        return array(
            'category_count' => $categoryCount,
            'item_count'     => $productCount,
            'review_count'   => $reviewCount
        );
    }

    /**
     * Returns a product collection of enabled products
     * from the provides stores
     *
     * @param int|string $storeViewId
     * @param bool       $xml - whether this is an XML export or not
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection($storeViewId, $xml = true)
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
                          ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        if ($xml) {
            $collection->addAttributeToFilter('visibility', array('in' => $this->getVisibilityFilter()));
        }
        if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_EXPORT_IS_EXPORT_STORES)) {
            $collection->addStoreFilter($storeViewId);
        }

        return $collection;
    }

    /**
     * Returns the filter for products that are visible
     * in the frontend of the store
     *
     * @return array
     */
    protected function getVisibilityFilter()
    {
        return array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        );
    }

    /**
     * Retrieve a collection of third party modules installed and active
     *
     * @return Varien_Data_Collection
     */
    public function getThirdPartyModules()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();

        $pluginsInstalled = array();
        foreach ($modules as $moduleName => $obj) {
            if (preg_match('/^(Mage_|Shopgate_Framework)/', $moduleName)) {
                continue;
            }

            $pluginsInstalled[] = $this->_createModuleInfo($moduleName);
        }

        return $pluginsInstalled;
    }

    /**
     * creates module info array from module config
     *
     * @param string $moduleName
     *
     * @return array
     */
    protected function _createModuleInfo($moduleName)
    {
        $moduleConfig = Mage::getConfig()->getModuleConfig($moduleName);
        return array(
            'name'      => $moduleName,
            'id'        => $moduleName,
            'version'   => (string)$moduleConfig->{'version'},
            'is_active' => (string)$moduleConfig->{'active'}
        );
    }

    /**
     * Check qty increments
     *
     * @param int|float                              $qty
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     *
     * @return Varien_Object
     */
    public function checkQtyIncrements($stockItem, $qty)
    {
        $result = new Varien_Object();

        if ($stockItem->getSuppressCheckQtyIncrements()) {
            return $result;
        }

        $qtyIncrements = $stockItem->getQtyIncrements();
        if ($qtyIncrements && (Mage::helper('core')->getExactDivision($qty, $qtyIncrements) != 0)) {
            $result->setHasError(true)
                   ->setQuoteMessage(
                       Mage::helper('cataloginventory')->__(
                           'Some of the products cannot be ordered in the requested quantity.'
                       )
                   )
                   ->setErrorCode('qty_increments')
                   ->setQuoteMessageIndex('qty');
            $result->setMessage(
                Mage::helper('cataloginventory')->__(
                    'This product is available for purchase in increments of %s only.',
                    $qtyIncrements * 1
                )
            );
        }

        return $result;
    }

    /**
     * Generates the URI to direct back after oAuth authorization
     * and makes the URI unique via store view id.
     * Due to this being called from frontend & backend we need
     * to exclude SID to make the redirect_uri match in both calls.
     *
     * @param int $storeViewId
     *
     * @return string
     */
    public function getOAuthRedirectUri($storeViewId)
    {
        /* temporary changing the current store to prevent the generation of get_params on getUrl */
        $oldStoreViewId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore($storeViewId);
        $url = Mage::app()->getStore($storeViewId)
                   ->getUrl(
                       'shopgate/framework/receive_authorization/storeviewid/' . $storeViewId,
                       array('_nosid' => true)
                   );
        Mage::app()->setCurrentStore($oldStoreViewId);

        return $this->includeHtpassToUrl($url);
    }

    /**
     * Injects .htpassw user & pass into URL
     * E.g. http://user:pass@store.com/
     *
     * @param $url
     * @return string
     */
    public function includeHtpassToUrl($url)
    {
        $parsedUrl = parse_url($url);
        if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) && !isset($parsedUrl['user']) && !isset($parsedUrl['password'])) {
            $http  = 'http://';
            $https = 'https://';
            $htpsw = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@';
            $url   = str_replace(array($http, $https), array($http . $htpsw, $https . $htpsw), $url);
        }

        return $url;
    }

    /**
     * Check for enterprise edition
     *
     * @return int
     */
    public function isEnterPrise()
    {
        return (int)is_object(Mage::getConfig()->getNode('global/models/enterprise_enterprise'));
    }

    /**
     * Helper method to fetch already for connections used store view id's
     *
     * @return array<int>
     */
    public function getConnectionDefaultStoreViewCollection()
    {
        $connections = Mage::helper('shopgate/config')->getShopgateConnections();

        $result = array();
        foreach ($connections as $connection) {
            if ($connection->getScope() == 'websites') {
                $collection = Mage::getModel('core/config_data')->getCollection()
                                  ->addFieldToFilter(
                                      'path',
                                      Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_SHOP_DEFAULT_STORE
                                  )
                                  ->addFieldToFilter('scope', $connection->getScope())
                                  ->addFieldToFilter('scope_id', $connection->getScopeId());

                if ($collection->getSize()) {
                    $storeViewId = $collection->getFirstItem()->getValue();
                }
            } elseif ($connection->getScope() == 'stores') {
                $storeViewId = $connection->getScopeId();
            }

            if (!isset($storeViewId)) {
                continue;
            }

            $result[] = $storeViewId;
        }

        return $result;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract|Mage_Sales_Model_Abstract $magentoObject
     * @param ShopgateOrder|ShopgateAddress|ShopgateCustomer                 $shopgateObject
     * @return mixed
     */
    public function setCustomFields($magentoObject, $shopgateObject)
    {
        foreach ($shopgateObject->getCustomFields() as $field) {
            $magentoObject->setData($field->getInternalFieldName(), $field->getValue());
        }

        return $magentoObject;
    }

    /**
     * @return Shopgate_Framework_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('shopgate/config');
    }

    /**
     * @param     $amount
     * @param     $taxAmount
     * @param int $precision
     *
     * @return float
     */
    public function calculateTaxRate($amount, $taxAmount, $precision = 2)
    {
        if ($taxAmount > 0) {
            return round((100 * $taxAmount) / ($amount - $taxAmount), $precision);
        }

        return 0.00;
    }

    /**
     * Checks if totals from shopgate and magento differ by 1 cent
     *
     * @param ShopgateOrder          $shopgateOrder
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return bool
     */
    public function oneCentBugDetected($shopgateOrder, $magentoOrder)
    {
        $config       = $this->getConfig();
        $bugDetected  = round(
                            abs($shopgateOrder->getAmountComplete() - $magentoOrder->getQuoteBaseGrandTotal()),
                            2
                        ) == 0.01;
        $shouldFixBug = $config->getFixOneCentBug();
        return $bugDetected && $shouldFixBug;
    }
}
