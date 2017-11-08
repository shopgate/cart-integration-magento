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
class Shopgate_Framework_Block_Adminhtml_OAuth_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Setup table configurations
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('shopgate_oauth_connections');
        $this->setDefaultSort('scope_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepares the collection & pre-filter.
     * Added filtration here as mage 1.4.0.0 does not
     * have _beforeLoad() functionality
     *
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('shopgate/shopgate_connection_grid_collection');
        $collection
            ->addFieldToFilter(
                'path',
                array('in' => array(Shopgate_Framework_Model_Config::XML_PATH_SHOPGATE_OAUTH_ACCESS_TOKEN))
            )
            ->addFieldToFilter('value', array('nin' => array(null, '')));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Register grid columns
     *
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('shopgate');
        $this->addColumn(
            'default_store_view',
            array(
                'header' => $helper->__('Default Store View'),
                'index'  => 'default_store_view',
                'type'   => 'store',
                'width'  => '100px',
            )
        );

        $this->addColumn(
            'shop_number',
            array(
                'header' => $helper->__('Shop Number #'),
                'width'  => '40px',
                'type'   => 'text',
                'index'  => 'shop_number',
            )
        );

        $this->addColumn(
            'mobile_alias',
            array(
                'header' => $helper->__('Mobile Url'),
                'index'  => 'mobile_alias',
                'type'   => 'text',
                'width'  => '120px',
            )
        );

        $this->addColumn(
            'related_store_views',
            array(
                'header' => $helper->__('Related Store Views'),
                'index'  => 'related_store_views',
                'type'   => 'store',
                'width'  => '100px',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header'  => $helper->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'width'   => '70px',
                'options' => Mage::getSingleton('shopgate/system_config_source_enabledisable')->getOptionArray(),
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('Configure'),
                        'url'     => array('base' => '*/*/configure'),
                        'field'   => 'shopgate_connection_ids'
                    ),
                    array(
                        'caption' => $helper->__('Disconnect'),
                        'url'     => array('base' => '*/*/unregister'),
                        'field'   => 'shopgate_connection_ids'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Registers the mass actions
     *
     * @inheritdoc
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shopgate_connection_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem(
            'activate_connection',
            array(
                'label' => Mage::helper('shopgate')->__('activate'),
                'url'   => $this->getUrl('*/*/edit/action/activate'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'deactivate_connection',
            array(
                'label' => Mage::helper('shopgate')->__('deactivate'),
                'url'   => $this->getUrl('*/*/edit/action/deactivate'),
            )
        );

        return $this;
    }

    /**
     * Disables the row javascript link
     *
     * @param Mage_Core_Model_Config_Data $row
     *
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return Mage::helper('shopgate/config')->getConfigureUrl($row->getData('config_id'));
    }
}

