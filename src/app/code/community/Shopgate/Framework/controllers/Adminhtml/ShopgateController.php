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
 * Shopgate admin UI related action controller
 */
class Shopgate_Framework_Adminhtml_ShopgateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Redirect to the shopgate backend for oauth registration
     *
     * @return void
     */
    public function registerAction()
    {
        $storeViewId = Mage::app()->getRequest()->getParam('store_view_id');

        if ($storeViewId && !$this->_isStoreViewAlreadyRegisterdToConnection($storeViewId)) {
            $redirect = $this->_buildShopgateOAuthUrl($storeViewId);
        } else {
            $redirect = $this->getUrl('*/*/connect');
        }

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Unregisters a given shopgate connection collection
     *
     * @return void
     */
    public function unregisterAction()
    {
        $connectionIds = Mage::app()->getRequest()->getParam('shopgate_connection_ids');
        if ($connectionIds && !is_array($connectionIds)) {
            $connectionIds = array($connectionIds);
        }

        $results = array();
        foreach ($connectionIds as $connection_id) {
            $results[] = Mage::getModel('shopgate/shopgate_connection')
                             ->load($connection_id)
                             ->unregister();
        }

        $hasErrors = false;
        foreach ($results as $result) {
            if (count($result->getErrors())) {
                $hasErrors = true;
                foreach ($result->getErrors() as $msg) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shopgate')->__($msg));
                }
                ShopgateLogger::getInstance()->log(
                    "Unregister OAuth Shop Connection has failed \"" . (string)$connectionIds . "\"",
                    ShopgateLogger::LOGTYPE_ERROR
                );
            }
        }

        if (!$hasErrors) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('shopgate')->__('The connection/s have been removed successfully')
            );
        }

        $redirect = $this->getUrl('*/*/manage');

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Handles configure action
     *
     * @return void
     */
    public function configureAction()
    {
        $connectionId = Mage::app()->getRequest()->getParam('shopgate_connection_ids');
        $redirect     = Mage::helper('shopgate/config')->getConfigureUrl($connectionId);

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Handles edit action
     *
     * @return void
     */
    public function editAction()
    {
        $connectionIds = Mage::app()->getRequest()->getParam('shopgate_connection_ids');
        $action        = Mage::app()->getRequest()->getParam('action');

        $affected = 0;
        foreach ($connectionIds as $connection_id) {
            $result = Mage::getModel('shopgate/shopgate_connection')
                          ->load($connection_id)
                          ->{$action}();

            if ($result) {
                $affected++;
            }
        }

        if ($affected == count($connectionIds)) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('shopgate')->__('Connection/s have been updated successfully')
            );
        } elseif ($affected > 0) {
            Mage::getSingleton('adminhtml/session')->addWarning(
                Mage::helper('shopgate')->__(
                    'Some connections were not updated. Please update them manually in the configuration section.'
                )
            );
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('shopgate')->__(
                    'Connection/s could not be updated. Please update them manually in the configuration section.'
                )
            );
        }

        $redirect = $this->getUrl('*/*/manage');

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Action for establishing an automated connection to shopgate
     *
     * @return void
     */
    public function connectAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action to manage available shopgate connections
     *
     * @return void
     */
    public function manageAction()
    {
        if (!Mage::helper('shopgate/config')->hasShopgateConnections()) {
            $redirect = $this->getUrl('*/*/connect');

            $this->getResponse()->setRedirect($redirect);
            $this->getResponse()->sendResponse();
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action to disconnect all connections from shopgate
     *
     * @return void
     */
    public function disconnectAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Renders support block
     *
     * @return void
     */
    public function supportAction()
    {
        $redirect = 'https://support.shopgate.com';

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Renders info block
     *
     * @return void
     */
    public function shopgateAction()
    {
        $redirect = 'https://www.shopgate.com';

        $this->getResponse()->setRedirect($redirect);
        $this->getResponse()->sendResponse();
    }

    /**
     * Unregisters a shopgate connection by storeViewId
     *
     * @return void
     */
    public function ajax_unregisterAction()
    {
        $storeViewId = Mage::app()->getRequest()->getParam('storeviewid');

        $result = Mage::getModel('shopgate/shopgate_connection')
                      ->loadByStoreViewId($storeViewId)
                      ->unregister();

        $responseData = array();

        $hasErrors = false;
        if (count($result->getErrors())) {
            $hasErrors = true;
            foreach ($result->getErrors() as $msg) {
                $responseData['errors'] = Mage::helper('shopgate')->__($msg);
            }
            ShopgateLogger::getInstance()->log(
                "Unregister OAuth Shop Connection with store View Id \"" . (string)$storeViewId
                . "\" could not get loaded",
                ShopgateLogger::LOGTYPE_ERROR
            );
        }

        if (!$hasErrors) {
            $responseData['success'] = true;
        } else {
            $responseData['success'] = false;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
        $this->getResponse()->sendResponse();
    }

    /**
     * Helper Method to generate oauth redirect url for registration
     *
     * @param $storeViewId
     *
     * @return string
     */
    protected function _buildShopgateOAuthUrl($storeViewId)
    {
        $config  = Mage::helper('shopgate')->getConfig($storeViewId);
        $builder = new ShopgateBuilder($config);
        $plugin  = Mage::getModel('shopgate/shopgate_plugin', $builder);
        $url     = $plugin->buildShopgateOAuthUrl('authorize');

        $merchantName = explode("\n", trim(Mage::getStoreConfig('general/store_information/address', $storeViewId)));

        $queryData = array(
            'response_type'    => 'code',
            'client_id'        => 'ShopgatePlugin',
            'redirect_uri'     => Mage::helper('shopgate')->getOAuthRedirectUri($storeViewId),
            'abort_return_uri' => $this->getUrl('*/*/connect'),
            'shoppingsystem'   => Mage::helper('shopgate')->isEnterPrise() ? 'magento_ee' : 'magento',
            'shop_url'         => Mage::getStoreConfig('web/unsecure/base_url', $storeViewId),
            'shop_name'        => count($merchantName) ? $merchantName[0] : "",
            'shop_mail'        => Mage::getStoreConfig('trans_email/ident_general/email', $storeViewId),
            'shop_phone'       => Mage::getStoreConfig('general/store_information/phone', $storeViewId),
            'shop_country'     => Mage::getStoreConfig('general/country/default', $storeViewId)
        );

        return $url . '?' . http_build_query(array_merge($queryData, $plugin->getEnabledPluginActions()));
    }

    /**
     * Helper method to check if a storeViewId is already in use by a shopgate connection
     *
     * @param int $storeViewId
     *
     * @return boolean
     */
    protected function _isStoreViewAlreadyRegisterdToConnection($storeViewId)
    {
        if (in_array($storeViewId, Mage::helper('shopgate')->getConnectionDefaultStoreViewCollection())) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('shopgate')->__("The given storeview is already in use by a shopgate connection.")
            );

            return true;
        }

        return false;
    }

    /**
     * ACL fix for SUPEE-6285 changes
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('shopgate_menu/manage');
    }
}
