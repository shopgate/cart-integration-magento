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
class Shopgate_Framework_Model_Modules_Affiliate_Packages_KProject_Validator
    extends Shopgate_Framework_Model_Modules_Affiliate_Validator
{
    const MODULE_CONFIG = 'KProject_ShareASale';
    const XML_CONFIG_ENABLED = 'kproject_sas/general/enabled';

    protected $validParams = array(
        'userID' => false,
        'sscid'  => false,
    );

    /**
     * Rewrite of original method to accommodate custom keys
     * defined in the modules system > configuration section.
     * Since it only has one parameter, we can remove the old
     * one.
     */
    public function getValidParams()
    {
        if ($this->isModuleActive() && empty($this->scriptRan)) {
            $userIdKey = Mage::helper('kproject_sas')->getAffiliateIdentifierKey();
            if (!empty($userIdKey) && !isset($this->validParams[$userIdKey])) {
                $this->validParams[$userIdKey] = false;
                unset($this->validParams['userID']);
            }

            $clickIdKey = Mage::helper('kproject_sas')->getClickIdentifierKey();
            if (!empty($clickIdKey) && !isset($this->validParams[$clickIdKey])) {
                $this->validParams[$clickIdKey] = false;
                unset($this->validParams['sscid']);
            }
            $this->scriptRan = true;
        }

        return parent::getValidParams();
    }
}
