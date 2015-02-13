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
    public $changelog = [];

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
    public function parseVotes()
    {

    }

    /**
     * Parse RFC changelog
     */
    public function parseChangelog()
    {

    }
}