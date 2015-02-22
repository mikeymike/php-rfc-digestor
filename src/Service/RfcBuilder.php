<?php

namespace MikeyMike\RfcDigestor\Service;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
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
     * @var Crawler
     */
    private $crawler;

    /**
     * @var string
     */
    private $storagePath = '';

    /**
     * @var Rfc
     */
    private $rfc;

    /**
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler, $storagePath)
    {
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            throw new InvalidArgumentException('Storage path does not exist!');
        }

        $this->crawler      = $crawler;
        $this->storagePath  = rtrim($storagePath, '/');
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
        $content = file_get_contents($wikiUrl);

        if (!$content) {
            throw new InvalidArgumentException(sprintf('No content found at URL %s', $wikiUrl));
        }

        $this->buildRfc($content, $buildAll);

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
     * @return Rfc
     */
    private function buildRfc($content, $buildAll)
    {
        $this->crawler->clear();
        $this->crawler->addHtmlContent($content);

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
     * @param Rfc $rfc
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

        $this->crawler->filter('.page div:first-of-type li')->each(function ($detail, $i) use (&$details) {
            $text      = trim($detail->text());
            $details[] = explode(':', $text, 2);
        });

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

        $this->crawler->filter('h2#changelog + div li')->each(function ($change, $i) use (&$changeLog) {
            $text        = trim($change->text());
            $changeLog[] = explode('-', $text, 2);
        });

        return $changeLog;
    }

    /**
     * @return string
     */
    private function parseVoteDescription()
    {
        try {
            $description = $this->crawler->filter('#vote + div p:first-child')->text();
        } catch (\InvalidArgumentException $e) {
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

        $this->crawler->filter('form[name="doodle__form"] table')->each(function ($table, $i) use (&$votes) {

            $title = trim($table->filter('tr.row0 th')->text());

            // Build array for votes table
            $votes[$title]            = [];
            $votes[$title]['headers'] = [];
            $votes[$title]['votes']   = [];
            $votes[$title]['counts']  = [];
            $votes[$title]['closed']  = false;

            $statusText = $table->filter('tr:last-child td:first-child, tr:last-child th:first-child')->text();

            if (strpos($statusText, 'closed') !== false) {
                $votes[$title]['closed'] = true;
            }

            $table->filter('tr.row1 > *')->each(function ($header, $i) use ($title, &$votes) {
                $votes[$title]['headers'][] = trim($header->text());
            });

            // Exclude count && status rows
            $rows = $votes[$title]['closed']
                ? $table->filter('tr:not(.row0):not(.row1):not(:last-child):not(:nth-last-child(2))')
                : $table->filter('tr:not(.row0):not(.row1):not(:last-child)');

            $rows->each(function ($vote, $i) use ($title, &$votes) {

                $row = [];

                $vote->children()->each(function ($cell, $i) use (&$row, $title) {

                    // Cell is a name
                    if (count($cell->filter('a')) > 0) {
                        $row[] = trim($cell->text());

                        return;
                    }

                    // Cell is a yes vote
                    if (count($cell->filter('img')) > 0) {
                        $row[] = "\xE2\x9C\x93";

                        return;
                    }

                    // Cell is a no vote
                    $row[] = "";
                });

                $votes[$title]['votes'][] = $row;
            });

            // Counts will either be last or second to last
            // Depending on whether the voting is completed
            $counts = $votes[$title]['closed']
                ? $table->filter('tr:nth-last-child(2) > *')
                : $table->filter('tr:last-child > *');

            $counts->each(function ($header, $i) use ($title, &$votes) {
                $votes[$title]['counts'][] = trim($header->text());
            });

        });

        return $votes;
    }
}