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
class Shopgate_Framework_Model_Compiler_Process extends Mage_Compiler_Model_Process
{

    /**
     * Compile classes code to files
     *
     * @return Mage_Compiler_Model_Process
     */
    protected function _compileFiles()
    {
        $classesInfo = $this->getCompileClassList();

        foreach ($classesInfo as $code => $classes) {
            //Hotfix for double declaration of Currency class on checkout
            if (Mage::helper("shopgate/config")->getIsMagentoVersionLower('1.7.0.2')
                && $code === 'checkout'
                && (($key = array_search('Mage_Directory_Model_Currency', $classes)) !== false)
            ) {
                unset($classes[$key]);
            }
            $classesSorce = $this->_getClassesSourceCode($classes, $code);
            file_put_contents(
                $this->_includeDir . DS . Varien_Autoload::SCOPE_FILE_PREFIX . $code . '.php',
                $classesSorce
            );
        }

        return $this;
    }

}
