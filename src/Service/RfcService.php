<?php

namespace MikeyMike\RfcDigestor\Service;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
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
     * PHP Wiki URLs
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
     * @var \DomDocument
     */
    private $document;

    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var Rfc
     */
    private $rfc;

    /**
     * @param string $storagePath
     */
    public function __construct($storagePath)
    {
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            throw new InvalidArgumentException('Storage path does not exist!');
        }

        $this->storagePath = rtrim($storagePath, '/');
    }

    /**
     * Quick RFC building
     *
     * @param string $rfcCode
     * @param bool   $loadDetails
     * @param bool   $loadChangeLog
     * @param bool   $loadVotes
     */
    public function buildRfc($rfcCode, $loadDetails = true, $loadChangeLog = true, $loadVotes = true)
    {
        $this->loadFromWiki($rfcCode);

        $this->rfc = new Rfc();

        if ($loadDetails) {
            $this->loadDetails();
        }

        if ($loadChangeLog) {
            $this->loadChangeLog();
        }

        if ($loadVotes) {
            $this->loadVotes();
        }

        return $this->rfc;
    }

    /**
     * Load content for RFC from PHP Wiki
     *
     * @param string $rfcCode
     * @return self
     */
    public function loadFromWiki($rfcCode)
    {
        $wikiUrl = sprintf('%s/%s', self::RFC_URL, $rfcCode);

        // Suppress HTML5 errors
        libxml_use_internal_errors(true);

        $this->document = new \DOMDocument();
        $this->document->loadHTMLFile($wikiUrl);

        // Turn errors back on
        libxml_use_internal_errors(false);

        return $this;
    }

    /**
     * Load content for RFC from app storage
     *
     * TODO: Refactor service to allow build RFC from storage
     * @param $rfcCode
     * @return self
     */
    public function loadFromStorage($rfcCode)
    {
        $filePath = sprintf('%s/%s.html', $this->storagePath, $rfcCode);

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('No application storage for RFC');
        }

        return $this;
    }

    /**
     * Add details to RFC
     *
     * @return self
     */
    public function loadDetails()
    {
        $this->rfc->setDetails($this->parseDetails());

        return $this;
    }

    /**
     * Add votes and vote description to RFC
     *
     * @return self
     */
    public function loadVotes()
    {
        $this->rfc->setVoteDescription($this->parseVoteDescription());
        $this->rfc->setVotes($this->parseVotes());

        return $this;
    }

    /**
     * Add Change Log to RFC
     *
     * @return self
     */
    public function loadChangeLog()
    {
        $this->rfc->setChangeLog($this->parseChangeLog());

        return $this;
    }

    /**
     * @param Rfc $rfc
     */
    public function setRfc(Rfc $rfc)
    {
        $this->rfc = $rfc;
    }

    /**
     * @return Rfc
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * Parse RFC content
     *
     * @return array
     */
    private function parseDetails()
    {
        $details = [];

        $xPath = new \DOMXPath($this->document);
        foreach ($xPath->query(CssSelector::toXPath('.page div:first-of-type li')) as $node) {
            $text      = trim($node->textContent);
            $details[] = explode(':', $text, 2);
        }

        return $details;
    }

    /**
     * Parse RFC ChangeLog
     *
     * @return array
     */
    private function parseChangeLog()
    {
        $changeLog = [];

        $xPath = new \DOMXPath($this->document);
        foreach ($xPath->query(CssSelector::toXPath('h2#changelog + div li')) as $node) {
            $text        = trim($node->textContent);
            $changeLog[] = explode('-', $text, 2);
        }

        return $changeLog;
    }

    /**
     * Parse vote description
     *
     * @return string
     */
    private function parseVoteDescription()
    {
        $xPath    = new \DOMXPath($this->document);
        $nodeList = $xPath->query(CssSelector::toXPath('#vote + div p:first-child'));

        if ($nodeList->length > 0) {
            $description = $nodeList->item(0)->textContent;
        } else {
            $description = '';
        }

        return trim($description);
    }

    /**
     * Parse the votes table
     *
     * @return array
     */
    private function parseVotes()
    {
        $votes = [];

        $xPath = new \DOMXPath($this->document);
        $nodeList = $xPath->query(CssSelector::toXPath('form[name="doodle__form"] table'));

        foreach ($nodeList as $node) {
            /** @var \DOMNode $node */
            $title = trim($xPath->query(CssSelector::toXPath('tr.row0 th'), $node)->item(0)->textContent);

            // Build array for votes table
            $votes[$title]              = [];
            $votes[$title]['headers']   = [];
            $votes[$title]['votes']     = [];
            $votes[$title]['counts']    = [];
            $votes[$title]['closed']    = false;

            // Get the status text field
            $statusSelect   = CssSelector::toXPath('tr:last-child td:first-child, tr:last-child th:first-child');
            $statusText     = $xPath->query($statusSelect, $node)->item(0)->textContent;

            if (strpos($statusText, 'closed') !== false) {
                $votes[$title]['closed'] = true;
            }

            $headersXPath = CssSelector::toXPath('tr.row1 > *');

            foreach ($xPath->query($headersXPath, $node) as $headerNode) {
                /** @var \DOMNode $headerNode */
                $votes[$title]['headers'][] = trim($headerNode->textContent);
            }

            // An extra final row occurs if the the vote is closed
            $rowXPath = ($votes[$title]['closed'])
                ? CssSelector::toXPath('tr:not(.row0):not(.row1):not(:last-child):not(:nth-last-child(2))')
                : CssSelector::toXPath('tr:not(.row0):not(.row1):not(:last-child)');

            foreach ($xPath->query($rowXPath, $node) as $rowNode) {
                /** @var \DOMNode $rowNode */

                $row = [];
                foreach ($xPath->query(CssSelector::toXPath('td'), $rowNode) as $cell) {
                    /** @var \DOMNode $cell */

                    // Cell is a name
                    $list = $xPath->query(CssSelector::toXPath('a'), $cell);
                    if ($list->length > 0) {
                        $row[] = trim($cell->textContent);
                        continue;
                    }

                    // Cell is a yes vote
                    $list = $xPath->query(CssSelector::toXPath('img'), $cell);
                    if ($list->length > 0) {
                        $row[] = true;
                        continue;
                    }

                    // Cell is a no vote
                    $row[] = false;
                }

                $votes[$title]['votes'][] = $row;
            }

            $countXPath = $votes[$title]['closed']
                ? $countXPath = 'tr:nth-last-child(2) > *'
                : $countXPath = 'tr:last-child > *';

            $counts = $xPath->query(CssSelector::toXPath($countXPath), $node);

            foreach ($counts as $count) {
                $votes[$title]['counts'][] = trim($count->textContent);
            }
        }

        return $votes;
    }

    /**
     * Get list from sections
     *
     * @param array $sections
     * @return array
     */
    public function getListsBySections($sections = [])
    {
        if (empty($sections)) {
            $sections = [self::IN_VOTING];
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
