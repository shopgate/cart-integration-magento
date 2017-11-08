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
 * Handles all payment routing for Multi-Payment implementations like PayOne & PayPal.
 * It helps figure out which class model to use based on payment_method provided.
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Router extends Shopgate_Framework_Model_Payment_Abstract
{
    /**
     * Default: returns 2nd part of payment_method
     * e.g. in AUTHN_CC, return CC. Simple class
     * handles the first part.
     */
    protected $_payment_method_part = 2;

    /**
     * Returns the correct Shopgate payment model
     * based on method identifier. This is recursive (sorta)!
     *
     * @return false | Shopgate_Framework_Model_Payment_Abstract
     */
    public function getModelByPaymentMethod()
    {
        $class = $this->getClassFromMethod();

        return $this->loadClass($class);
    }

    /**
     * @return bool|false|Mage_Core_Model_Abstract
     */
    public function getMethodModel()
    {
        $model = $this->getModelByPaymentMethod();

        if (!$model) {
            $model = $this->_checkAllPossibleModels();
        }

        if (!$model) {
            $debug = $this->_getHelper()->__('No class found for payment method: %s', $this->getPaymentMethod());
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }

        return $model;
    }

    /**
     * Using payment_method as example - AUTHN_CC.
     * Adds _cc to an already resolved shopgate/payment_authn
     *
     * @return string
     */
    protected function getClassFromMethod()
    {
        $endPart = strtolower($this->_getMethodPart());
        $current = $this->getCurrentClassShortName();

        return $current . '_' . $endPart;
    }

    /**
     * Returns the correct part of the payment id.
     * E.g. payment_method_part = 2, will return
     * the second part of AUTHN_CC, so CC.
     *
     * @return string
     */
    protected function _getMethodPart()
    {
        //user friendly, first part (1) == 0 for array
        $index = $this->_payment_method_part;
        $index--;
        $parts = explode('_', $this->getPaymentMethod());

        return isset($parts[$index]) ? $parts[$index] : $parts[0];
    }

    /** ======= Fallback Functionality ======== */

    /**
     * Initializes models until it finds the one implementing
     * interface.
     *
     * @return bool|false|Mage_Core_Model_Abstract
     */
    protected function _checkAllPossibleModels()
    {
        $combinations = $this->_getModelCombinations();
        $class        = $this->getCurrentClassShortName();
        foreach ($combinations as $combination) {
            $className = $class . $combination;
            $model     = $this->loadClass($className);

            if ($model) {
                return $model;
            }
        }

        return false;
    }

    /**
     * Gets all possible model combinations
     * PAONE_PRP -> _Prp, _Payone, _Payone_Prp, _Prp_Payone
     *
     * @return array
     */
    protected function _getModelCombinations()
    {
        $combinations  = array();
        $paymentMethod = explode('_', strtolower($this->getPaymentMethod()));
        $this->depthPicker($paymentMethod, '', $combinations);

        return $combinations;
    }

    /**
     * Recursive array combination logic
     *
     * @param $arr
     * @param $temp_string
     * @param $collect
     */
    private function depthPicker($arr, $temp_string, &$collect)
    {
        if ($temp_string != "") {
            $collect [] = $temp_string;
        }

        for ($i = 0; $i < sizeof($arr); $i++) {
            $arrcopy = $arr;
            $elem    = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
            $temp    = $temp_string . "_" . $elem[0];
            if (sizeof($arrcopy) > 0) {
                $this->depthPicker($arrcopy, $temp, $collect);
            } else {
                $collect[] = $temp;
            }
        }
    }

    /**
     * Resolves current class name to magento's
     * short class. Truncates Router as well.
     *
     * @return string
     */
    private function getCurrentClassShortName()
    {
        $class = str_replace('Shopgate_Framework_Model_', 'shopgate/', get_class($this));
        $class = preg_replace('/_Router$/', '', $class);

        return strtolower($class);
    }

    /**
     * Takes short class name and attempts to load it
     *
     * @param string $class - short class name
     *
     * @return false | Shopgate_Framework_Model_Payment_Abstract
     */
    private function loadClass($class)
    {
        $model = false;

        try {
            $model = Mage::getModel($class, array($this->getShopgateOrder()));
        } catch (Exception $e) {
            ShopgateLogger::getInstance()->log(
                'No payment mapping file exists. ' . $e->getMessage(),
                ShopgateLogger::LOGTYPE_DEBUG
            );
        }

        if ($model) {
            if ($model instanceof Shopgate_Framework_Model_Payment_Interface) {
                return $model;
            } elseif ($model instanceof Shopgate_Framework_Model_Payment_Router) {
                return $model->getModelByPaymentMethod();
            }
        }

        return false;
    }
}
