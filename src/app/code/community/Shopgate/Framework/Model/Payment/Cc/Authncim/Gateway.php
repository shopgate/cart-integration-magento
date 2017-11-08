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
 * Added an additional call for AuthorizeCim gateway API
 *
 * Class Shopgate_Framework_Model_Payment_Cc_Authncim_Gateway
 *
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Shopgate_Framework_Model_Payment_Cc_Authncim_Gateway extends ParadoxLabs_AuthorizeNetCim_Model_Gateway
{
    protected $recursion_counter = 0;

    /**
     * AuthnCIM extension library did not allow us to make this
     * request at the time. Had to inherit from gateway to use
     * protected _runTransaction() method. We are creating a
     * profileId and profilePaymentId base off of transactionId.
     *
     * @return array
     */
    public function createCustomerProfileFromTransactionRequest()
    {
        $result    = array();
        $params    = array(
            'transId' => $this->getParameter('transId'),
        );
        $response  = $this->_runTransaction('createCustomerProfileFromTransactionRequest', $params);
        $errorCode = $response['messages']['message']['code'];
        $errorText = $response['messages']['message']['text'];

        if (isset($response['customerProfileId'], $response['customerPaymentProfileIdList']['numericString'])) {
            $result['customerProfileId']        = $response['customerProfileId'];
            $result['customerPaymentProfileId'] = $response['customerPaymentProfileIdList']['numericString'];
        } elseif (isset($errorText)
                  && strpos($errorText, 'duplicate') !== false
        ) {
            $profileId = preg_replace('/[^0-9]/', '', $errorText);
            /**
             * If we have profileID from error, try to get paymentID based on card's last 4 digits
             */
            if (!empty($profileId)) {
                $this->setParameter('customerProfileId', $profileId);
                $profile                     = $this->getCustomerProfile();
                $result['customerProfileId'] = $profileId;

                if (isset($profile['profile']['paymentProfiles'])
                    && count($profile['profile']['paymentProfiles']) > 0
                ) {
                    $lastFour = $this->getParameter('cardNumber');
                    //match profile that has the same last 4 card digits
                    foreach ($profile['profile']['paymentProfiles'] as $card) {
                        if (isset($card['payment']['creditCard'])
                            && $lastFour == substr($card['payment']['creditCard']['cardNumber'], -4)
                        ) {
                            $result['customerPaymentProfileId'] = $card['customerPaymentProfileId'];
                            break;
                        }
                    }
                } else {
                    /**
                     * They don't have any cards in profile! Remove CIM profile & recurse.
                     * This can fail on refunding if original payment card bound to transaction
                     * does not match the imported card via Shopgate.
                     */
                    $this->deleteCustomerProfile();
                    if ($this->recursion_counter < 2) {
                        $result = $this->createCustomerProfileFromTransactionRequest();
                        $this->recursion_counter++; //not necessary, but protects from recursion leaks
                    }
                }
            } else {
                /**
                 * weird gateway error that passed _runTransaction() error throw
                 */
                $error = mage::helper('shopgate')->__(
                    'Unknown error passed through _runTransaction validation. Code "%s" Message: "%s"',
                    $errorCode,
                    $errorText
                );
                ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);
            }
        }
        return $result;
    }
}