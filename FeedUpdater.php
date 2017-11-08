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
class FeedUpdater
{
    const CHANGELOG_PATH = './app/code/community/Shopgate/Framework/changelog.txt';
    const FEED_URL = 'files.shopgate.com/plugins/magento/magento.xml';
    const LOCAL_FEED_URL = './temp_feed.xml';

    /**
     * Recent most version of the plugin cached from changelog.txt
     *
     * @var string
     */
    protected $recentVersion;

    /**
     * @param string $changeLogPath - location of changelog.txt to pull version number & latest comments from
     * @param string $feedLocation  - location of the remote feed to get the latest version from
     * @param string $saveLocation  - location to save the feed XML locally, default - ./tmp_feed.xml
     *
     * @return false|string - returns the location it saved the feed at
     */
    public function generateFeed(
        $changeLogPath = self::CHANGELOG_PATH,
        $feedLocation = self::FEED_URL,
        $saveLocation = self::LOCAL_FEED_URL
    ) {
        try {
            $feed = $this->getFeedFile($feedLocation);
            $this->addNewItemToXmlFeed($feed, $changeLogPath);

            return $this->saveFeed($feed, $saveLocation);
        } catch (FeedException $e) {
            echo $e->getMessage();

            return false;
        } catch (Exception $e) {
            echo $e->getMessage();

            return false;
        }
    }

    /**
     * @param string $feedLocation - location of feed
     *
     * @return DOMDocument|false
     * @throws FeedException
     */
    protected function getFeedFile($feedLocation)
    {
        if (!class_exists('Mage')) {
            throw new FeedException('Class "Mage" not found');
        }

        $curl = new Mage_HTTP_Client_Curl();
        $curl->get($feedLocation);
        $feedContents = $curl->getBody();
        $feedXml      = new DOMDocument();
        $feedXml->loadXML($feedContents);

        if (!($feedXml && $feedXml->getElementsByTagName('item'))) {
            throw new FeedException('Could not load feed or find node "item" in the feed');
        }

        return $feedXml;
    }

    /**
     * @param DOMDocument $feed
     * @param string      $changeLogPath
     *
     * @return DOMDocument|false
     * @throws FeedException
     */
    protected function addNewItemToXmlFeed(DOMDocument $feed, $changeLogPath)
    {
        if ($feed
            && version_compare($this->getVersionFromChangelog($changeLogPath), $this->getVersionFromFeed($feed), '<=')
        ) {
            throw new FeedException('No new version found');
        }

        $updateXmlNode = $this->buildUpdateXmlItem($feed);

        return $this->insertNodeToFeed($feed, $updateXmlNode);
    }

    /**
     * Gets the recent most version of the plugins
     *
     * @param string $changeLogPath - location of the changelog.txt
     *
     * @return string
     * @throws FeedException
     */
    protected function getVersionFromChangelog($changeLogPath)
    {
        if (is_null($this->recentVersion)) {
            $changeLog = file_get_contents($changeLogPath);

            if (preg_match_all("/(?:\d+\.)?(?:\d+\.\d+)/", $changeLog, $output_array) && isset($output_array[0][0])) {
                $this->recentVersion = $output_array[0][0];
            } else {
                throw new FeedException('Could not retrieve change log version');
            }
        }

        return $this->recentVersion;
    }

    /**
     * @param DOMDocument $feed
     *
     * @return string
     * @throws FeedException
     */
    protected function getVersionFromFeed(DOMDocument $feed)
    {
        $items = $feed->getElementsByTagName('item');
        /** @var DOMDocument $firstItem */
        $firstItem = $items->item(0);
        $version   = $firstItem->getElementsByTagName('version')->item(0)->nodeValue;
        if (!$version) {
            throw new FeedException('Could not load the version from XML feed');
        }

        return $version;
    }

    /**
     * @param DOMDocument $feed
     *
     * @return DOMElement
     */
    protected function buildUpdateXmlItem(DOMDocument $feed)
    {
        $item     = $feed->createElement('item');
        $title    = $feed->createElement('title');
        $version  = $feed->createElement('version', $this->getVersionFromChangelog());
        $severity = $feed->createElement('severity', '4');
        $date     = $feed->createElement('pubDate', date(DATE_RSS, time()));

        $titleContent = $feed->createCDATASection(
            sprintf('Shopgate Plugin Update %s Released', $this->getVersionFromChangelog())
        );
        $title->appendChild($titleContent);

        $link        = $feed->createElement('link');
        $linkContent = $feed->createCDATASection(
            'http://www.magentocommerce.com/magento-connect/shopgate-mobile-commerce-mobile-website-and-shopping-app-for-magento.html'
        );
        $link->appendChild($linkContent);

        $description = $feed->createElement('description');
        $desrContent = $feed->createCDATASection('Changes: ' . $this->getChangeLogLastChanges());
        $description->appendChild($desrContent);

        $item->appendChild($title);
        $item->appendChild($link);
        $item->appendChild($version);
        $item->appendChild($severity);
        $item->appendChild($description);
        $item->appendChild($date);

        return $item;
    }

    /**
     * @return string
     */
    protected function getChangeLogLastChanges()
    {
        $changes   = '';
        $changelog = file_get_contents(self::CHANGELOG_PATH);
        preg_match("/(\*.+?)'''/s", $changelog, $matches);
        if (isset($matches[1])) {
            $changes = $matches[1];
        }

        $changes = ltrim($changes, '*');
        $changes = str_replace('*', '|', $changes);

        return trim($changes);
    }

    /**
     * @param DOMElement $nodeToInsert
     *
     * @return DOMDocument
     */
    protected function insertNodeToFeed(DOMDocument $feed, DOMElement $nodeToInsert)
    {
        $xpath  = new DOMXPath($feed);
        $parent = $xpath->query('//channel');
        $next   = $xpath->query('//channel/item[1]');
        $parent->item(0)->insertBefore($nodeToInsert, $next->item(0));

        return $feed;
    }

    /**
     * @param DOMDocument $feed
     * @param string      $saveLocation
     *
     * @return string
     * @throws FeedException
     */
    protected function saveFeed(DOMDocument $feed, $saveLocation)
    {
        $feed->save($saveLocation);

        if (!$feed) {
            throw new FeedException('Could not save feed to the desired location: ' . $saveLocation);
        }

        return $saveLocation;
    }
}

/**
 * Custom exception class
 */
class FeedException extends Exception
{
}
