<?php

namespace MikeyMike\RfcDigestor\Entity;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Rfc
{
    /**
     * @var string
     */
    protected $code = '';

    /**
     * @var string
     */
    protected $name = '';
    /**
     * @var array
     */
    protected $details = [];

    /**
     * @var array
     */
    protected $changeLog = [];

    /**
     * @var array
     */
    protected $votes = [];

    /**
     * @var string
     */
    protected $voteDescription = '';

    /**
     * @var string
     */
    protected $rawContent = '';

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $changLog
     */
    public function setChangeLog($changLog)
    {
        $this->changeLog = $changLog;
    }

    /**
     * @return array
     */
    public function getChangeLog()
    {
        return $this->changeLog;
    }

    /**
     * @param array $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @return array
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param string $description
     */
    public function setVoteDescription($description)
    {
        $this->voteDescription = $description;
    }

    /**
     * @return string
     */
    public function getVoteDescription()
    {
        return $this->voteDescription;
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * @param string $rawContent
     */
    public function setRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
    }
}
