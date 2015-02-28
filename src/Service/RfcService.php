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
     * @param string $storagePath
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

        return $this->rfcBuilder->getRfc();
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
            $lists = array_merge($lists, $list);
        }

        return $lists;
    }

    /**
     * @param string $section
     * @return array
     */
    private function getSectionList($section)
    {
        // Suppress HTML5 errors
        libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTMLFile(self::RFC_URL);

        // Turn errors back on
        libxml_use_internal_errors(false);

        $xPath    = new \DOMXPath($document);
        $listKey  = $xPath->query(CssSelector::toXPath(sprintf('#%s', $section)))->item(0)->textContent;

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

            $list[$listKey][] = $row;
        }

        return $list;
    }
}
