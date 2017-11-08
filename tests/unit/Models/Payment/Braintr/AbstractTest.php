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


namespace unit\Models\Payment\Braintr;

use Mage_Sales_Model_Order_Payment;
use ReflectionClass;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expectedResult
     * @param array  $riskData
     *
     * @covers       Shopgate_Framework_Model_Payment_Braintr_Abstract::_handleFraud()
     * @dataProvider provideDefaultListingSortCases
     */
    public function test_handleFraud($expectedResult, $riskData)
    {
        $shopgateOrder = new \ShopgateOrder;
        $shopgateOrder->setPaymentInfos(array('transaction_id' => 1));
        $payment = new Mage_Sales_Model_Order_Payment();

        $stub = $this->getMockBuilder('Shopgate_Framework_Model_Payment_Braintr_Abstract')
                     ->disableOriginalConstructor()
                     ->setMethods(array('getShopgateOrder', '_getBraintreeTransactionById', '_addBraintreeInfo'))
                     ->getMock();
        $stub->method('getShopgateOrder')->willReturn($shopgateOrder);
        $stub->method('_getBraintreeTransactionById')->willReturn(null);
        $stub->method('_addBraintreeInfo')->willReturn(null);

        $reflection = new ReflectionClass('Shopgate_Framework_Model_Payment_Braintr_Abstract');
        $method     = $reflection->getMethod('_handleFraud');
        $method->setAccessible(true);

        $method->invoke($stub, $payment, $riskData);
        $result = array(
            'isTransactionPending' => $payment->getIsTransactionPending(),
            'isFraudDetected'      => $payment->getIsFraudDetected(),
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function provideDefaultListingSortCases()
    {
        return array(
            'no fraud data'   => array(
                array('isTransactionPending' => false, 'isFraudDetected' => false),
                array(),
            ),
            'approved'        => array(
                array('isTransactionPending' => false, 'isFraudDetected' => false),
                array('id' => 1, 'decision' => 'Approve'),
            ),
            'extended review' => array(
                array('isTransactionPending' => true, 'isFraudDetected' => false),
                array('id' => 1, 'decision' => 'Review'),
            ),
            'declined'        => array(
                array('isTransactionPending' => true, 'isFraudDetected' => true),
                array('id' => 1, 'decision' => 'Decline'),
            ),
        );
    }
}