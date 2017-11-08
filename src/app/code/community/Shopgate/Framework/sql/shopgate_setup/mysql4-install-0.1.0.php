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

$this->startSetup();

$this->run(
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('shopgate_order')}` (
 `shopgate_order_id` int(11) NOT NULL AUTO_INCREMENT,
 `order_id` int(11) NOT NULL,
 `store_id` int(11) NOT NULL,
 `shopgate_order_number` varchar(20) NOT NULL,
 `status` varchar(15) NOT NULL,
 `received_data` text NOT NULL,
 PRIMARY KEY (`shopgate_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
"
);

$this->endSetup();
