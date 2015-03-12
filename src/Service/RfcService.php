<?php

namespace MikeyMike\RfcDigestor\Service;

use MikeyMike\RfcDigestor\RfcBuilder;
use Symfony\Component\CssSelector\CssSelector;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class RfcService
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcService
{
    /**
     * PHP Wiki URL
     */
    const RFC_URL = 'https://wiki.php.net/rfc';

    /**
     * Voting sections
     */
    const IN_VOTING     = 'in_voting_phase';
    const DISCUSSION    = 'under_discussion';
    const DRAFT         = 'in_draft';
    const ACCEPTED      = 'accepted';
    const DECLINED      = 'declined';
    const WITHDRAWN     = 'withdrawn';
    const INACTIVE      = 'inactive';

    /**
     * @var RfcBuilder
     */
    private $rfcBuilder;

    /**
     * @param RfcBuilder $rfcBuilder
     */
    public function __construct(RfcBuilder $rfcBuilder)
    {
        $this->rfcBuilder = $rfcBuilder;
    }

    /**
     * Quick RFC building
     *
     * @param string $rfcCode
     * @param bool   $loadDetails
     * @param bool   $loadChangeLog
     * @param bool   $loadVotes
     */
    public function getRfc($rfcCode, $loadDetails = true, $loadChangeLog = true, $loadVotes = true)
    {
        $this->rfcBuilder->loadFromWiki($rfcCode, self::RFC_URL);

        $this->loadRfc($loadDetails, $loadChangeLog, $loadVotes);

        return $this->rfcBuilder->getRfc();
    }

    /**
     * Quick RFC building from storage
     *
     * @param $rfcCode
     * @return RfcBuilder
     */
    public function getRfcFromStorage($rfcCode, $loadDetails = true, $loadChangeLog = true, $loadVotes = true)
    {
        $this->rfcBuilder->loadFromStorage($rfcCode);

        $this->loadRfc($loadDetails, $loadChangeLog, $loadVotes);

        return $this->rfcBuilder->getRfc();
    }

    /**
     * Load specific areas of RFC through builder
     *
     * @param bool $loadDetails
     * @param bool $loadChangeLog
     * @param bool $loadVotes
     */
    private function loadRfc($loadDetails, $loadChangeLog, $loadVotes)
    {
        $this->rfcBuilder->loadName();

        if ($loadDetails) {
            $this->rfcBuilder->loadDetails();
        }

        if ($loadChangeLog) {
            $this->rfcBuilder->loadChangeLog();
        }

        if ($loadVotes) {
            $this->rfcBuilder->loadVotes();
        }
    }

    /**
     * Save an RFC to storage in json
     *
     * @param Rfc $rfc
     * @return bool|void
     */
    public function saveRfcToStorage(Rfc $rfc)
    {
        return;
    }

    /**
     * Get list from sections
     *
     * @param array $sections
     * @return array
     */
    public function getLists($sections = [])
    {
        // Default lets get all
        if (empty($sections)) {
            $sections = [
                self::IN_VOTING,
                self::DISCUSSION,
                self::DRAFT,
                self::ACCEPTED,
                self::DECLINED,
                self::WITHDRAWN,
                self::INACTIVE
            ];
        }

        $lists = [];

        foreach ($sections as $section) {
            $list  = $this->getSectionList($section);

            if ($list) {
                $lists = array_merge($lists, $list);
            }
        }

        return $lists;
    }

    /**
     * @param string $section
     * @return array|bool
     */
    private function getSectionList($section)
    {
        // Suppress HTML5 errors
        libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTMLFile(self::RFC_URL);

        // Turn errors back on
        libxml_use_internal_errors(false);

        $xPath       = new \DOMXPath($document);
        $headingNode = $xPath->query(CssSelector::toXPath(sprintf('#%s', $section)))->item(0);

        if (!$headingNode) {
            return false;
        }

        $listKey = $headingNode->textContent;

        $list = [
            $listKey => []
        ];

        foreach ($xPath->query(CssSelector::toXPath(sprintf('#%s + .level2 .li', $section))) as $listing) {
            /** @var \DOMNode $listing */

            /** @var \DOMElement $link */
            $link = $xPath->query(CssSelector::toXPath('a'), $listing)->item(0);

            $row = [
                $link->textContent,
                basename($link->getAttribute('href'))
            ];

            $list[$listKey][$link->textContent] = $row;
        }

        return $list;
    }

    /**
     * Get the details in a flat format
     *
     * @param Rfc $rfc
     * @return array
     */
    public function getDetailsAsTableRows(Rfc $rfc)
    {
        return $this->assocArrayToRows($rfc->getDetails());
    }

    /**
     * Get the change log in a flat format
     *
     * @param Rfc $rfc
     * @return array
     */
    public function getChangeLogsAsTableRows(Rfc $rfc)
    {
        return $this->assocArrayToRows($rfc->getChangeLog());
    }

    /**
     * Get the votes in a flat format
     *
     * @param Rfc    $rfc
     * @param string $voteKey
     * @return array
     */
    public function getVotesAsTableRows(Rfc $rfc, $voteKey)
    {
        $votes = $rfc->getVotes();

        return $this->assocArrayToRows($votes[$voteKey]['votes']);
    }

    /**
     * @param array $array
     * @return array
     */
    protected function assocArrayToRows($array)
    {
        $rows = [];

        foreach ($array as $key => $value) {
            $row = [$key];

            if (is_array($value)) {
                $row = array_merge($row, $value);
                $rows[] = $row;
                continue;
            }

            $row[]  = $value;
            $rows[] = $row;
        }

        return $rows;
    }
}
