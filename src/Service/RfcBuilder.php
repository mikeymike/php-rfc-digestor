<?php

namespace MikeyMike\RfcDigestor\Service;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class RfcBuilder
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcBuilder
{
    /**
     * URL prefix for loadFromWiki
     */
    const URL_PREFIX = 'https://wiki.php.net/rfc';

    /**
     * @var \DomDocument
     */
    private $document;

    /**
     * @var string
     */
    private $storagePath = '';

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
     * Load content for RFC from PHP Wiki
     *
     * @param string $rfcCode
     * @return self
     */
    public function loadFromWiki($rfcCode, $buildAll = false)
    {
        $wikiUrl = sprintf('%s/%s', self::URL_PREFIX, $rfcCode);
        libxml_use_internal_errors(true);
        $this->document = new \DOMDocument();
        $this->document->loadHTMLFile($wikiUrl);
        libxml_use_internal_errors(false);
        $this->buildRfc($buildAll);

        return $this;
    }

    /**
     * Load content for RFC from app storage
     *
     * @param $rfcCode
     * @return self
     */
    public function loadFromStorage($rfcCode, $buildAll = false)
    {
        $filePath = sprintf('%s/%s.html', $this->storagePath, $rfcCode);

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('No application storage for RFC');
        }

        $this->buildRfc(file_get_contents($filePath), $buildAll);

        return $this;
    }

    /**
     * Build RFC entity
     *
     * @param bool $buildAll
     *
     * @return Rfc
     */
    private function buildRfc($buildAll = false)
    {
        $this->rfc = new Rfc();

        if ($buildAll) {
            $this->loadDetails();
            $this->loadVotes();
            $this->loadChangeLog();
        }
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
            $text = trim($node->textContent);
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
            $text = trim($node->textContent);
            $changeLog[] = explode('-', $text, 2);
        }

        return $changeLog;
    }

    /**
     * @return string
     */
    private function parseVoteDescription()
    {
        $xPath = new \DOMXPath($this->document);
        $nodeList = $xPath->query(CssSelector::toXPath('#vote + div p:first-child'));

        if ($nodeList->length > 0) {
            $description = $nodeList->item(0)->textContent;
        } else {
            $description = '';
        }

        return trim($description);
    }

    /**
     * @return array
     * TODO: Refactor, really don't like having to pass $votes everywhere
     * TODO: Resolve major performance hit from DOM Crawler when getting votes
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
            $votes[$title] = [];
            $votes[$title]['headers'] = [];
            $votes[$title]['votes'] = [];
            $votes[$title]['counts'] = [];
            $votes[$title]['closed'] = false;

            $selector = CssSelector::toXPath('tr:last-child td:first-child, tr:last-child th:first-child');
            $statusText = $xPath->query($selector, $node)->item(0)->textContent;
            if (strpos($statusText, 'closed') !== false) {
                $votes[$title]['closed'] = true;
            }

            $headersXPath = CssSelector::toXPath('tr.row1 > *');
            $rowXPath = ($votes[$title]['closed'])
                ? CssSelector::toXPath('tr:not(.row0):not(.row1):not(:last-child):not(:nth-last-child(2))')
                : CssSelector::toXPath('tr:not(.row0):not(.row1):not(:last-child)');


            foreach ($xPath->query($headersXPath, $node) as $headerNode) {
                /** @var \DOMNode $headerNode */
                $votes[$title]['headers'][] = trim($headerNode->textContent);
            }

            foreach ($xPath->query($rowXPath, $node) as $rowNode) {
                /** @var \DOMNode $headerNode */

                $row = [];
                foreach ($xPath->query(CssSelector::toXPath('td'), $rowNode) as $cell) {
                    // Cell is a name
                    $list = $xPath->query(CssSelector::toXPath('a'), $cell);
                    if ($list->length > 0) {
                        $row[] = trim($cell->textContent);
                        continue;
                    }

                    // Cell is a yes vote
                    $list = $xPath->query(CssSelector::toXPath('img'), $cell);
                    if ($list->length > 0) {
                        $row[] = "\xE2\x9C\x93";
                        continue;
                    }

                    // Cell is a no vote
                    $row[] = "";
                }

                $votes[$title]['votes'][] = $row;
            }

            if ($votes[$title]['closed']) {
                $countXPath = 'tr:nth-last-child(2) > *';
            } else {
                $countXPath = 'tr:last-child > *';
            }

            $counts = $xPath->query(CssSelector::toXPath($countXPath), $node);

            foreach ($counts as $count) {
                $votes[$title]['counts'][] = trim($count->textContent);
            }
        }

        return $votes;
    }
}
