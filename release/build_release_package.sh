#!/bin/sh

rm shopgate-magento-integration.tgz
rm -r vendor

composer install -vvv --no-dev

cd vendor/shopgate/cart-integration-sdk/
composer install -vvv --no-dev
cd ../../../

rm release/magento1702.zip
wget -O release/magento1702.zip http://files.shopgate.com/magento/magento1702.zip

rm -r release/magento
unzip release/magento1702.zip -d release/magento

rsync -av src/ release/magento
rsync -av CHANGELOG.md release/magento/app/code/community/Shopgate/Framework/CHANGELOG.md
mkdir release/magento/lib/Shopgate
rsync -av vendor/shopgate/cart-integration-sdk/ release/magento/lib/Shopgate/cart-integration-sdk
rsync -av release/magento_package.php release/magento/magento_package.php

cd release/magento/
chmod -R 777 var
php magento_package.php
