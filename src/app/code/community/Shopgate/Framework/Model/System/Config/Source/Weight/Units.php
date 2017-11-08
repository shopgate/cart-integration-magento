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
class Shopgate_Framework_Model_System_Config_Source_Weight_Units
{
    /**
     * const for automatic weight unit
     */
    const WEIGHT_UNIT_AUTO = 'auto';

    /**
     * const for kg weight unit
     */
    const WEIGHT_UNIT_KG = 'kg';

    /**
     * const for g weight unit
     */
    const WEIGHT_UNIT_GRAMM = 'g';

    /**
     * const for pound weight unit
     */
    const WEIGHT_UNIT_POUND = 'lb';

    /**
     * const for ounce weight unit
     */
    const WEIGHT_UNIT_OUNCE = 'oz';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::WEIGHT_UNIT_AUTO,
                'label' => Mage::helper('shopgate')->__(self::WEIGHT_UNIT_AUTO)
            ),
            array(
                'value' => self::WEIGHT_UNIT_KG,
                'label' => Mage::helper('shopgate')->__(self::WEIGHT_UNIT_KG)
            ),
            array(
                'value' => self::WEIGHT_UNIT_GRAMM,
                'label' => Mage::helper('shopgate')->__(self::WEIGHT_UNIT_GRAMM)
            ),
            array(
                'value' => self::WEIGHT_UNIT_POUND,
                'label' => Mage::helper('shopgate')->__(self::WEIGHT_UNIT_POUND)
            ),
            array(
                'value' => self::WEIGHT_UNIT_OUNCE,
                'label' => Mage::helper('shopgate')->__(self::WEIGHT_UNIT_OUNCE)
            )
        );
    }
}
