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
class Mage_Sales_Model_Order_Payment
{
    public $isTransactionPending = false;
    public $isFraudDetected = false;

    public function setIsTransactionPending($isTransactionPending)
    {
        $this->isTransactionPending = $isTransactionPending;
    }

    public function getIsTransactionPending()
    {
        return $this->isTransactionPending;
    }

    public function setIsFraudDetected($isFraudDetected)
    {
        $this->isFraudDetected = $isFraudDetected;
    }

    public function getIsFraudDetected()
    {
        return $this->isFraudDetected;
    }

    public function save()
    {
    }
}