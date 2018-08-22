#!/bin/sh

rm -f shopgate_module-*.tgz
rm -rf vendor

composer install -vvv --no-dev

cd vendor/shopgate/cart-integration-sdk/
composer install -vvv --no-dev
cd ../../../

rm -f release/magento1702.zip
wget -O release/magento1702.zip http://files.shopgate.com/magento/magento1702.zip

rm -rf release/magento
unzip release/magento1702.zip -d release/magento > /dev/null

rsync -av src/ release/magento
rsync -av CHANGELOG.md release/magento/app/code/community/Shopgate/Framework/CHANGELOG.md
mkdir release/magento/lib/Shopgate
rsync -av --exclude-from './release/exclude-filelist.txt' vendor/shopgate/cart-integration-sdk/ release/magento/lib/Shopgate/cart-integration-sdk
rsync -av release/magento_package.php release/magento/magento_package.php

cd release/magento/
chmod -R 777 var
php magento_package.php

cd ../../
rm -rf release/magento
rm -rf vendor
