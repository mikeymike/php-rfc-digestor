<?php

namespace MikeyMike\RfcDigestor\Entity;

use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor\Test\Entity
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Rfc
     */
    protected $rfc;

    public function setUp()
    {
        $this->rfc = new Rfc();
    }

    public function testSetCodeAndGetCode()
    {
        $this->assertSame('', $this->rfc->getCode());

        $this->rfc->setCode('code');

        $this->assertSame('code', $this->rfc->getCode());
    }

    public function testSetNameAndGetName()
    {
        $this->assertSame('', $this->rfc->getName());

        $this->rfc->setName('name');

        $this->assertSame('name', $this->rfc->getName());
    }

    public function testSetDetailsAndGetDetails()
    {
        $this->assertSame([], $this->rfc->getDetails());

        $details = [
            'version' => '0.1.0',
            'author'  => 'Some dude'
        ];

        $this->rfc->setDetails($details);

        $this->assertSame($details, $this->rfc->getDetails());
    }

    public function testSetChangeLogAndGetChangeLog()
    {
        $this->assertSame([], $this->rfc->getChangeLog());

        $changeLog = [
            '0.2.0' => 'Second version',
            '0.1.0' => 'First version'
        ];

        $this->rfc->setChangeLog($changeLog);

        $this->assertSame($changeLog, $this->rfc->getChangeLog());
    }

    public function testSetVotesAndGetVotes()
    {
        $this->assertSame([], $this->rfc->getVotes());

        $votes = [
            'Introduce the in operator?' => [
                'headers' => ['Real name', 'Yes', 'No'],
                'votes'   => [
                    'aharvey (aharvey)' => ['Yes' => false, 'No' => true],
                    'ajf (ajf)'         => ['Yes' => false, 'No' => true]
                ],
                'counts'  => ['Real name' => 'Count:', 'Yes' => 0, 'No' => 2],
                'closed'  => false
            ]
        ];

        $this->rfc->setVotes($votes);

        $this->assertSame($votes, $this->rfc->getVotes());
    }

    public function testSetVoteDescriptionAndGetVoteDescription()
    {
        $this->assertSame('', $this->rfc->getVoteDescription());

        $this->rfc->setVoteDescription('Vote description');

        $this->assertSame('Vote description', $this->rfc->getVoteDescription());
    }

    public function testSetRawContentAndGetRawContent()
    {
        $this->assertSame('', $this->rfc->getRawContent());

        $html = '<html><head><title>Testing :)</title></head><body></body></html>';

        $this->rfc->setRawContent($html);

        $this->assertSame($html, $this->rfc->getRawContent());
    }
}
