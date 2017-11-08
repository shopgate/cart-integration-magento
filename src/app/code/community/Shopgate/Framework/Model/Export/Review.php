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
class Shopgate_Framework_Model_Export_Review extends Shopgate_Framework_Model_Export_Abstract
{
    /**
     * @param Mage_Review_Model_Review $review
     * @return int
     */
    public function getItemNumber($review)
    {
        $product = Mage::getModel("catalog/product")->setStoreId($this->_getConfig()->getStoreViewId())->load(
            $review->getEntityPkValue()
        );
        return $product->getId();
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return int
     */
    public function getUpdateReviewId($review)
    {
        return $review->getId();
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return float
     */
    public function getScore($review)
    {
        $ratings = array();
        foreach ($review->getRatingVotes() as $vote) {
            $ratings[] = $vote->getPercent();
        }
        $sum = array_sum($ratings);
        $avg = $sum > 0 ? array_sum($ratings) / count($ratings) : $sum;

        return round($avg / 10);
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return string
     */
    public function getName($review)
    {
        return $review->getNickname();
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return string
     */
    public function getDate($review)
    {
        $date = $review->getCreatedAt();

        if (!empty($date)) {
            $date = date('c', strtotime($date));
        }

        return $date;
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return string
     */
    public function getTitle($review)
    {
        return $review->getTitle();
    }

    /**
     * @param Mage_Review_Model_Review $review
     * @return string
     */
    public function getText($review)
    {
        return $review->getDetail();
    }
}
