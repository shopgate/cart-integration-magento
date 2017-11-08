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
class Shopgate_Framework_Helper_Debug extends Mage_Core_Helper_Abstract
{
    /**
     * Return all info data according to installed magento version
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'Magento-rewrites'  => Mage::getConfig()->getNode()->xpath('//global//rewrite'),
            'Magento-conflicts' => $this->_getRewrites(),
            'Magento-modules'   => $this->_getModules(),
        );
    }

    /**
     * Retrieve a collection of all modules registered
     *
     * @return array|mixed[]
     */
    protected function _getModules()
    {
        $modules        = Mage::getConfig()->getNode('modules')->children();
        $arrayOfModules = array();
        foreach ($modules as $modName => $module) {
            $arrayOfModules[$modName] = $module->asArray();
        }
        return $arrayOfModules;
    }

    /**
     * Retrieve a collection of all rewrites
     *
     * @return array
     */
    protected function _getRewrites()
    {
        $collection = array();
        $modules    = Mage::getConfig()->getNode('modules')->children();
        $rewrites   = array();

        foreach ($modules as $modName => $module) {
            if ($module->is('active')) {
                $configFile = Mage::getConfig()->getModuleDir('etc', $modName) . DS . 'config.xml';
                if (file_exists($configFile)) {
                    $xml = file_get_contents($configFile);
                    $xml = simplexml_load_string($xml);

                    if ($xml instanceof SimpleXMLElement) {
                        $rewrites[$modName] = $xml->xpath('//rewrite');
                    }
                }
            }
        }

        foreach ($rewrites as $rewriteNodes) {
            foreach ($rewriteNodes as $n) {
                $nParent    = $n->xpath('..');
                $module     = (string)$nParent[0]->getName();
                $nSubParent = $nParent[0]->xpath('..');
                $component  = (string)$nSubParent[0]->getName();

                if (!in_array($component, array('blocks', 'helpers', 'models'))) {
                    continue;
                }

                $pathNodes = $n->children();
                foreach ($pathNodes as $pathNode) {
                    $path             = (string)$pathNode->getName();
                    $completePath     = $module . '/' . $path;
                    $rewriteClassName = (string)$pathNode;
                    $instance         = Mage::getConfig()->getGroupedClassName(
                        substr($component, 0, -1),
                        $completePath
                    );

                    if (($instance != $rewriteClassName)) {
                        array_push(
                            $collection,
                            array(
                                'type'          => $component,
                                'path'          => $completePath,
                                'rewrite_class' => $rewriteClassName,
                                'active_class'  => $instance,
                                'conflict'      => ($instance == $rewriteClassName) ? "NO" : "YES"
                            )
                        );
                    }
                }
            }
        }

        return $collection;
    }
}
