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
class Shopgate_Framework_Model_Redirect_Observer
{

    /** @var Shopgate_Framework_Model_Config */
    protected $config;
    /** @var Shopgate_Framework_Model_Storage_Cache */
    protected $cache;
    /** @var Shopgate_Framework_Model_Storage_Session */
    protected $session;
    /** @var Shopgate_Framework_Helper_Redirect */
    protected $redirect;

    /**
     *  Initialize as if we are using DI :)
     */
    public function __construct()
    {
        $this->config   = Mage::helper('shopgate/config')->getConfig();
        $this->session  = Mage::getSingleton('shopgate/storage_session');
        $this->redirect = Mage::helper('shopgate/redirect');
    }

    /**
     * Execute frontend observer
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->redirect->isAllowed($this->config)) {
            $this->session->unsetScript();

            return;
        }

        foreach ($this->getRedirects() as $redirect) {
            $redirect->run();
        }
    }

    /**
     * By default will run HTTP redirect with JS fallback if HTTP
     * does not work. If JS is set as the main redirect, it will
     * just run the JS redirect
     *
     * @return Shopgate_Framework_Model_Redirect_Type_TypeInterface[]
     */
    private function getRedirects()
    {
        $js   = Mage::getModel('shopgate/redirect_type_js');
        $http = Mage::getModel('shopgate/redirect_type_http');

        return $this->redirect->isTypeJavaScript() ? array($js) : array($http, $js);
    }
}
