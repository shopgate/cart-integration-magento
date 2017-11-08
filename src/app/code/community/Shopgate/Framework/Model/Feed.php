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
 * Shopgate_Framework_Model_Feed to inform merchants about updates
 */
class Shopgate_Framework_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_PATH_SHOPGATE_RSS_URL = 'shopgate/rss/url';

    /**
     * @inheritdoc
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                              . Mage::getStoreConfig(self::XML_PATH_SHOPGATE_RSS_URL);
        }

        return $this->_feedUrl;
    }

    /**
     * @inheritdoc
     */
    public function checkUpdate()
    {
        $notificationModel = Mage::getModel('adminnotification/inbox');
        if (!is_object($notificationModel)) {
            return $this;
        }

        return parent::checkUpdate();
    }
}
