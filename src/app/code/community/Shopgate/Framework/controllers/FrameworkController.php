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

/** @noinspection PhpIncludeInspection */
include_once Mage::getBaseDir('lib') . '/Shopgate/cart-integration-sdk/shopgate.php';

/**
 * Main shopgate API controller
 */
class Shopgate_Framework_FrameworkController extends Mage_Core_Controller_Front_Action
{
    const RECEIVE_AUTH_ACTION = 'receive_authorization';

    /**
     * load the module and do api-request
     */
    public function preDispatch()
    {
        if (Mage::app()->getRequest()->getActionName() == self::RECEIVE_AUTH_ACTION) {
            Mage::app()->getRequest()->setParam('action', self::RECEIVE_AUTH_ACTION);
        }

        $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        $this->_run();
    }

    /**
     * placeholder action, needs to be defined for router
     */
    public function receive_authorizationAction()
    {
        $this->_run();
    }

    /**
     * index action -> call run
     */
    public function indexAction()
    {
        $this->_run();
    }

    /**
     * run
     */
    protected function _run()
    {
        define('_SHOPGATE_API', true);
        define('_SHOPGATE_ACTION', Mage::app()->getRequest()->getParam('action'));
        define('SHOPGATE_PLUGIN_VERSION', Mage::helper('shopgate')->getModuleVersion());

        try {
            $this->getResponse()->clearHeaders();
            $config = Mage::helper('shopgate/config')->getConfig();
            if (!Mage::getStoreConfig(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_ACTIVE)
                && Mage::app()->getRequest()->getParam('action') != self::RECEIVE_AUTH_ACTION
            ) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::CONFIG_PLUGIN_NOT_ACTIVE,
                    'plugin not active',
                    true
                );
            }
            Mage::app()->loadArea('adminhtml');
            Mage::app()->getTranslator()->init('adminhtml', true);
            $builder = new ShopgateBuilder($config);
            $plugin  = Mage::getModel('shopgate/shopgate_plugin', $builder);
            $plugin->handleRequest(Mage::app()->getRequest()->getParams());
        } catch (ShopgateLibraryException $e) {
            $traceId = Mage::app()->getRequest()->getParam('trace_id');
            $response = new ShopgatePluginApiResponseAppJson(
                (isset($traceId) ? $traceId : '')
            );
            $response->markError($e->getCode(), $e->getMessage());
            $response->setData(array());
            $response->send();
        } catch (Exception $e) {
            Mage::logException($e);
            $response = Mage::helper('core')->jsonEncode(array(
                'error' => ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                'error_text' => $e->getMessage()
            ));
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody($response);
        }
    }
}
