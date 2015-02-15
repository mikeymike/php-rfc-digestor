<?php

namespace MikeyMike\RfcDigestor\Entity;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <michael@wearejh.com>
 */
class Rfc
{
    /**
     * Prefix for URLs
     *
     * @var string
     */
    private $urlPrefix = 'https://wiki.php.net/rfc';

    /**
     * @var string
     */
    private $content;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var array
     */
    public $details = [];

    /**
     * @var array
     */
    public $votes = [];

    /**
     * @var array
     */
    public $changeLog = [];

    /**
     * @param string  $rfcPath
     */
    public function __construct($rfcPath)
    {
        $url            = sprintf('%s/%s', $this->urlPrefix, $rfcPath);
        $this->content  = file_get_contents($url);

        if (!$this->content) {
            throw new \InvalidArgumentException('No page content found');
        }

        $this->crawler = new Crawler($this->content);
    }

    /**
     * Parse RFC details
     */
    public function getDetails()
    {
        if (!$this->details) {
            $this->crawler->filter('.page div:first-of-type li')->each(function ($detail, $i) {
                $text            = trim($detail->text());
                $this->details[] = explode(':', $text, 2);
            });
        }

        return $this->details;
    }

    /**
     * Parse RFC votes
     */
    public function getVotes()
    {
        if (!$this->votes) {
            $this->crawler->filter('form[name="doodle__form"] table')->each(function ($table, $i) {

                $title = trim($table->filter('tr.row0 th')->text());

                // Build array for votes table
                $this->votes[$title]            = [];
                $this->votes[$title]['headers'] = [];
                $this->votes[$title]['votes']   = [];
                $this->votes[$title]['counts']  = [];

                $table->filter('tr.row1 > *')->each(function ($header, $i) use ($title) {
                    $this->votes[$title]['headers'][] = trim($header->text());
                });

                // TODO: Resolve performance issue here
                // Needs to be on a row by row basis
                $table->filter('tr:not(.row0):not(.row1):not(:last-child)')->each(function ($vote, $i) use ($title) {

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
                        $row[] = "\xE2\x95\xB3";
                    });

                    $this->votes[$title]['votes'][] = $row;
                });

                $table->filter('tr:last-child > *')->each(function ($header, $i) use ($title) {
                    $this->votes[$title]['counts'][] = trim($header->text());
                });

            });
        }

        return $this->votes;
    }

    /**
     * Parse RFC change log
     */
    public function getChangeLog()
    {
        if (!$this->changeLog) {
            $this->crawler->filter('h2#changelog + div ul li')->each(function ($change, $i) {
                $text              = trim($change->text());
                $this->changeLog[] = explode('-', $text, 2);
            });
        }

        return $this->changeLog;
    }
}