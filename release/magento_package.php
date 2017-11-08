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

echo "include\n";
require_once 'app/Mage.php';
echo "Mage init\n";
Mage::app();

try {
    echo "Mage loadLocalPackage\n";
    $data = Mage::helper('connect')->loadLocalPackage('shopgate_module');
} catch (Exception $e) {
    echo "loading the module failed. " . $e->getMessage() . "\n";
    return false;
}
$extension = Mage::getModel('connect/extension');
$extension->setData($data);

echo "Create Package pear...\n";
$extension->createPackageV1x();
echo "Create Package connect...\n";
$extension->createPackage();


$shopgateModuleName = '';
echo "Looking for Package\n";
$files = scandir('./var/connect/');
foreach ($files as $file) {
    $pathInfo = pathinfo($file);
    echo "checking Package " . $file . "\n";
    if (strpos($file, "shopgate_module") !== false && $pathInfo['extension'] !== 'xml') {
        $shopgateModuleName = $file;
        echo "Found Package " . $shopgateModuleName . "\n";
        echo "size: " . filesize('./var/connect/' . $shopgateModuleName) . "\n";
        break;
    }
}

if (empty($shopgateModuleName)) {
    echo "no package found\n";
    return false;
}

echo "Shopgate Module: " . $shopgateModuleName . " is moved\n";
rename('./var/connect/' . $shopgateModuleName, './../../shopgate-magento-integration.tgz');

echo "done!\n";