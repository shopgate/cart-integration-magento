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
class Shopgate_Framework_Model_Export_Review_Xml extends Shopgate_Model_Review
{
    /**
     * @var Mage_Review_Model_Review $item
     */
    protected $item;

    /**
     * set id
     */
    public function setUid()
    {
        parent::setUid($this->item->getId());
    }

    /**
     * set product id for the review
     */
    public function setItemUid()
    {
        parent::setItemUid($this->item->getEntityPkValue());
    }

    /**
     * set score for the review
     */
    public function setScore()
    {
        parent::setScore($this->_getScore());
    }

    /**
     * set username for the review
     */
    public function setReviewerName()
    {
        parent::setReviewerName($this->item->getNickname());
    }

    /**
     * set text for the review
     */
    public function setDate()
    {
        parent::setDate(date('Y-m-d', strtotime($this->item->getCreatedAt())));
    }

    /**
     * set title for the review
     */
    public function setTitle()
    {
        parent::setTitle($this->item->getTitle());
    }

    /**
     * set text for the review
     */
    public function setText()
    {
        parent::setText($this->item->getDetail());
    }

    /**
     * @return float|number
     */
    protected function _getScore()
    {
        $ratings = array();
        foreach ($this->item->getRatingVotes() as $vote) {
            $ratings[] = $vote->getPercent();
        }
        $sum = array_sum($ratings);
        $avg = $sum > 0 ? array_sum($ratings) / count($ratings) : $sum;

        return round($avg / 10);
    }
}
