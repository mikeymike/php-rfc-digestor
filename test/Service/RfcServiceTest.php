<?php

namespace MikeyMike\RfcDigestor\Service;

use MikeyMike\RfcDigestor\Entity\Rfc;
use MikeyMike\RfcDigestor\RfcBuilder;

/**
 * Class RfcServiceTest
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RfcService
     */
    protected $rfcService;

    /**
     * @var RfcBuilder
     */
    protected $rfcBuilderMock;

    public function setUp()
    {
        $this->rfcBuilderMock = $this->getMockBuilder('MikeyMike\RfcDigestor\RfcBuilder')
            ->disableOriginalConstructor()
            ->setMethods([
                'loadFromWiki',
                'getRfc',
                'loadFromStorage',
                'loadName',
                'loadDetails',
                'loadChangeLog',
                'loadVotes'
            ])
            ->getMock();

        $this->rfcService = new RfcService($this->rfcBuilderMock);
    }

    /**
     * @param $details
     * @param $changeLog
     * @param $votes
     *
     * @dataProvider getRfcTestsDataProvider
     */
    public function testGetRfcCorrectlyBuildsRfc($details, $changeLog, $votes)
    {
        $rfc = new Rfc();

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('loadFromWiki')
            ->with('generator-delegation', 'https://wiki.php.net/rfc');

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('loadName');

        if ($details) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadDetails');
        }

        if ($changeLog) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadChangeLog');
        }

        if ($votes) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadVotes');
        }

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('getRfc')
            ->will($this->returnValue($rfc));

        $this->assertSame(
            $rfc,
            $this->rfcService->getRfc('generator-delegation', $details, $changeLog, $votes)
        );
    }

    /**
     * @param $details
     * @param $changeLog
     * @param $votes
     *
     * @dataProvider getRfcTestsDataProvider
     */
    public function testGetRfcFromStorageCorrectlyBuildsRfc($details, $changeLog, $votes)
    {
        $rfc = new Rfc();

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('loadFromStorage')
            ->with('generator-delegation');

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('loadName');

        if ($details) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadDetails');
        }

        if ($changeLog) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadChangeLog');
        }

        if ($votes) {
            $this->rfcBuilderMock
                ->expects($this->once())
                ->method('loadVotes');
        }

        $this->rfcBuilderMock
            ->expects($this->once())
            ->method('getRfc')
            ->will($this->returnValue($rfc));

        $this->assertSame(
            $rfc,
            $this->rfcService->getRfcFromStorage('generator-delegation', $details, $changeLog, $votes)
        );
    }

    /**
     * testGetRfcCorrectlyBuildsRfc Data Provider
     *
     * @return array
     */
    public function getRfcTestsDataProvider()
    {
        return [
            [true,  true,   true],
            [false, false,  false],
            [true,  false,  true]
        ];
    }

    public function testGetVotesAsTableRows()
    {
        $rfc = new Rfc();

        $rfc->setVotes([
            'Introduce the in operator?' => [
                'headers' => ['Real name', 'Yes', 'No'],
                'votes'   => [
                    'aharvey (aharvey)' => ['Yes' => false, 'No' => true],
                    'ajf (ajf)'         => ['Yes' => false, 'No' => true],
                    'ben (ben)'         => ['Yes' => true,  'No' => false],
                ],
                'counts'  => ['Real name' => 'Count:', 'Yes' => 0, 'No' => 2],
                'closed'  => false
            ]
        ]);

        $result = $this->rfcService->getVotesAsTableRows($rfc, 'Introduce the in operator?');

        $expected = [
            ['aharvey (aharvey)', false, true],
            ['ajf (ajf)', false, true],
            ['ben (ben)', true, false]
        ];

        $this->assertSame($expected, $result);
    }
}