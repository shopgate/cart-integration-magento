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
class Shopgate_Framework_Model_Export_Review_Csv extends Shopgate_Framework_Model_Export_Review
{
    /**
     * @var null
     */
    protected $_defaultRow = null;

    /**
     * @var null
     */
    protected $_actionCache = null;

    /**
     * @param Mage_Review_Model_Review $review
     * @return array
     */
    public function generateData($review)
    {
        foreach (array_keys($this->_defaultRow) as $key) {
            $action = '_set' . uc_words($key, '', '_');
            if (empty($this->_actionCache[$action])) {
                $this->_actionCache[$action] = true;
            }
        }

        foreach (array_keys($this->_actionCache) as $_action) {
            if (method_exists($this, $_action)) {
                $this->{$_action}($review);
            }
        }

        return $this->_defaultRow;
    }

    /**
     * @param $defaultRow
     * @return Shopgate_Framework_Model_Export_Review_Csv
     */
    public function setDefaultRow($defaultRow)
    {
        $this->_defaultRow = $defaultRow;
        return $this;
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setItemNumber($review)
    {
        $this->_defaultRow['item_number'] = $this->getItemNumber($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setUpdateReviewId($review)
    {
        $this->_defaultRow['update_review_id'] = $this->getUpdateReviewId($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setScore($review)
    {
        $this->_defaultRow['score'] = $this->getScore($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setName($review)
    {
        $this->_defaultRow['name'] = $this->getName($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setDate($review)
    {
        $this->_defaultRow['date'] = $this->getDate($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setTitle($review)
    {
        $this->_defaultRow['title'] = $this->getTitle($review);
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _setText($review)
    {
        $this->_defaultRow['text'] = $this->getText($review);
    }
}
