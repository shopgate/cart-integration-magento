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
class Shopgate_Framework_Model_Modules_Affiliate_Validator extends Shopgate_Framework_Model_Modules_Validator
{
    /**
     * The parameters that are in the 'tracking_get_parameters' array
     *
     * @var array
     */
    private $affiliateParameters;

    /**
     * Holds the default parameters and on init
     * get populated with custom parameters that
     * may be defined in the system > config
     *
     * @var array
     */
    protected $validParams = array();

    /**
     * Checks if the script ran already
     *
     * @var bool
     */
    protected $scriptRan = false;

    /**
     * Affiliate params are passed into constructor
     */
    public function _construct()
    {
        $this->affiliateParameters = current($this->_data);
        $this->assignParamsToKeys();
        $this->removeUnassignedKeys();
    }

    /**
     * Returns all valid parameters
     * of the module
     *
     * @return array
     */
    public function getValidParams()
    {
        return $this->validParams;
    }

    /**
     * Checks if the current route is valid
     * based on the parameter passed
     *
     * @return bool
     */
    public function checkGenericValid()
    {
        $validParams = $this->getValidParams();

        foreach ($this->affiliateParameters as $param) {
            if (isset($param['key']) && isset($validParams[$param['key']])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assign passed params to keys
     */
    private function assignParamsToKeys()
    {
        foreach ($this->affiliateParameters as $param) {
            if ($this->isKeySetButNoValue($param)) {
                $this->validParams[$param['key']] = $param['value'];
            } elseif (isset($this->validParams[$param['key']])) {
                unset ($this->validParams[$param['key']]);
            }
        }
    }

    /**
     * Initializes the valid params and checks if
     * the key exists for the passed parameter
     *
     * @param array $param - e.g. array('key'=> 'key', 'value' => 'value')
     *
     * @return bool
     */
    private function isKeySetButNoValue($param)
    {
        $validParams = $this->getValidParams();

        return isset($param['key']) && isset($validParams[$param['key']]) && $validParams[$param['key']] === false;
    }

    /**
     * Initializes removal of unused parameter keys
     */
    private function removeUnassignedKeys()
    {
        foreach ($this->validParams as $key => $param) {
            if ($this->validParams[$key] === false || $this->validParams[$key] === '') {
                unset($this->validParams[$key]);
            }
        }
    }
}
