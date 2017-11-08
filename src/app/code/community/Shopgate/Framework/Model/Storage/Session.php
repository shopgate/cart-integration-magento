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
class Shopgate_Framework_Model_Storage_Session extends Mage_Core_Model_Session_Abstract
{

    const SESSION_KEY = 'SHOPGATE_SESSION';

    /**
     * Shopgate session setup
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        parent::init(self::SESSION_KEY);
        parent::__construct();
    }

    /**
     * Sets the JavaScript
     *
     * @param string $scriptHtml
     */
    public function setScript($scriptHtml)
    {
        $this->setData('shopgate_header', $scriptHtml);
    }

    /**
     * Retrieves the stored JavaScript header
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        return $script . $this->getData('shopgate_header');
    }

    /**
     * Unset the JavaScript header from session
     */
    public function unsetScript()
    {
        $this->getData('shopgate_header', true);
    }
}
