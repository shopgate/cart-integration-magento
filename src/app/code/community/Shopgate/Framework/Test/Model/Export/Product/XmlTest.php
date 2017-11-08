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
 * @coversDefaultClass Shopgate_Framework_Model_Export_Product_Xml
 */
class Shopgate_Framework_Test_Model_Export_Product_XmlTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Framework_Model_Export_Product_Xml $class */
    protected $class;
    /** @var Shopgate_Framework_Test_Model_ProductUtility */
    protected $utility;

    /**
     * Set up of product creation utility and class to test against
     */
    public function setUp()
    {
        $this->class   = Mage::getModel('shopgate/export_product_xml');
        $this->utility = new Shopgate_Framework_Test_Model_ProductUtility();
    }

    /**
     * Tests the standard product group price export
     *
     * @uses Shopgate_Framework_Test_Model_ProductUtility
     * @uses Shopgate_Model_Catalog_Price
     * @uses ReflectionClass
     *
     * @covers ::_createGroupPriceNode
     * @covers ::_calculateCatalogGroupPriceRules
     * @covers ::_adjustGroupPrice
     */
    public function testGroupPriceNodeNoRules()
    {
        $customerGroup = 3;
        $groupDiscount = 10.00;
        $salePrice     = 100;
        $final         = $salePrice - $groupDiscount;
        $product       = $this->utility->createSimpleProduct();

        $product->setData(
            'group_price',
            array(
                array(
                    'website_id'    => 0,
                    'cust_group'    => $customerGroup,
                    'website_price' => $groupDiscount,
                ),
            )
        );
        $product->save();
        $this->class->setItem($product);
        $this->utility->updatePriceIndexTable($product, $salePrice, $final, $customerGroup);
        $priceModel = new Shopgate_Model_Catalog_Price();
        $priceModel->setSalePrice($salePrice);

        $reflection = new ReflectionClass($this->class);
        $method     = $reflection->getMethod('_createGroupPriceNode');
        $method->setAccessible(true);
        $method->invoke($this->class, $priceModel);

        /** @var Shopgate_Model_Catalog_TierPrice[] $tier */
        $tier = $priceModel->getTierPricesGroup();

        $this->assertEquals($groupDiscount, $tier[0]->getData('reduction'));
    }

    /**
     * Remove data entries
     */
    public function tearDown()
    {
        $this->utility->deleteProducts();
    }
}
