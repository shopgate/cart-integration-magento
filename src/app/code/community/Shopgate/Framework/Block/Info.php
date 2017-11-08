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
class Shopgate_Framework_Block_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $content = '
        <div class="shopgate">
            <div class="info">
                <a href="http://www.shopgate.com/" target="_blank"><img src="' .
                   $this->getSkinUrl('shopgate/logo_big.png') . '" alt="www.shopgate.com"/></a><br /><p>';
        $content .= $this->__(
            'The settings for Shopgate can be found under the Store Settings.<br /><br />Please change the view to a single Store View.'
        );
        $content .= '</p><p><ul class="nested-content">';

        foreach (Mage::app()->getWebsites() as $website) {
            /* @var $website Mage_Core_Model_Website */
            $urlKey  = '/system_config/edit/section/shopgate/website/' . $website->getCode();
            $content .= "<li><li><a href='" . Mage::helper("adminhtml")
                                                  ->getUrl($urlKey) . "'>{$website->getName()}</a>";
            $content .= "<ul>";

            foreach ($website->getGroups() as $group) {
                /** @var Mage_Core_Model_Store_Group $group */
                $content .= '<li><strong>' . $group->getName() . '</strong>';
                $content .= '<ul>';

                foreach ($group->getStores() as $store) {
                    /** @var Mage_Core_Model_Store $store */
                    $urlKey  = '/system_config/edit/section/shopgate/website/' . $website->getCode(
                        ) . '/store/' . $store->getCode();
                    $content .= '<li class="entry-edit"><a href="' . Mage::helper("adminhtml")->getUrl($urlKey) . '">';
                    $content .= $store->getName() . '</a></li>';
                }
                $content .= "</ul></li>";
            }
            $content .= "</ul></li>";
        }

        $content .= '
            </ul></p>
            <h3>Shopgate GmbH</h3>
            <span class="contact-type">Mail</span> <a href="mailto:technik@shopgate.com">support@shopgate.com</a><br />
            </div>
            </div>';

        return $content;
    }
}
