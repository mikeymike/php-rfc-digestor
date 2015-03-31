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

        $this->rfcService = new RfcService($this->rfcBuilderMock, 'https://wiki.php.net/rfc');
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

    public function testGetDetailsAsTableRows()
    {
        $rfc = new Rfc();

        $rfc->setDetails([
            'version'            => '0.5.1',
            'name'               => 'In Operator',
            'authors'            => 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com',
            'status'             => 'Voting',
            'First Published at' => 'http://wiki.php.net/rfc/in_operator'
        ]);

        $result = $this->rfcService->getDetailsAsTableRows($rfc);

        $expected = [
            ['version', '0.5.1'],
            ['name', 'In Operator'],
            ['authors', 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com'],
            ['status', 'Voting'],
            ['First Published at', 'http://wiki.php.net/rfc/in_operator']
        ];

        $this->assertSame($expected, $result);
    }

    public function testGetChangeLogsAsTableRows()
    {
        $rfc = new Rfc();

        $rfc->setChangelog([
            'v0.5:' => 'Removed integer support, so the strictness is consistent.',
            'v0.4:' => 'Removed possibility to check multiple values using an array.'
        ]);

        $result = $this->rfcService->getChangeLogsAsTableRows($rfc);

        $expected = [
            ['v0.5:', 'Removed integer support, so the strictness is consistent.'],
            ['v0.4:', 'Removed possibility to check multiple values using an array.']
        ];

        $this->assertSame($expected, $result);
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

    /**
     * @dataProvider getListsDataProvider
     */
    public function testGetLists($sections, $expected)
    {
        // Use reflection to replace the RFC URL path
        $rfcFilePath = realpath(__DIR__ . '/../resources/rfcListings.html');

        $reflectionClass    = new \ReflectionClass($this->rfcService);
        $reflectionProperty = $reflectionClass->getProperty('rfcUrl');

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->rfcService, $rfcFilePath);

        $this->assertSame($expected, $this->rfcService->getLists($sections));
    }

    /**
     * @return array
     */
    public function getListsDataProvider()
    {
        $votingSection = [
            'In voting phase' => [
                'Reserve Even More Types in PHP 7' => [
                    0 => 'Reserve Even More Types in PHP 7',
                    1 => 'reserve_even_more_types_in_php_7',
                ],
            ]
        ];

        $discussionSectoin = [
            'Under Discussion' => [
                'Integrate voting polls in PHP.net' => [
                    0 => 'Integrate voting polls in PHP.net',
                    1 => 'site_voting_poll',
                ],
                'Escaping RFC for PHP Core' => [
                    0 => 'Escaping RFC for PHP Core',
                    1 => 'escaper',
                ],
                'Add is_cacheable() stream-wrapper operation' => [
                    0 => 'Add is_cacheable() stream-wrapper operation',
                    1 => 'streams-is-cacheable',
                ],
                'Add cyclic string replace to str_[i]replace() functions' => [
                    0 => 'Add cyclic string replace to str_[i]replace() functions',
                    1 => 'cyclic-replace',
                ],
            ]
        ];

        $draftSection = [
            'In Draft' => [
                'Loop+Else control structure' => [
                    0 => 'Loop+Else control structure',
                    1 => 'loop_else',
                ],
                'Deprecate MCRYPT_RAND' => [
                    0 => 'Deprecate MCRYPT_RAND',
                    1 => 'deprecate_mcrypt_rand',
                ],
                'Make the PHP core case-sensitive' => [
                    0 => 'Make the PHP core case-sensitive',
                    1 => 'case-sensitivity',
                ],
                'Restrict parameter parsing rules' => [
                    0 => 'Restrict parameter parsing rules',
                    1 => 'zpp-conversion-rules',
                ],
            ]
        ];

        $acceptedSection = [
            'Accepted' => [
                'PHP Version Control System' => [
                    0 => 'PHP Version Control System',
                    1 => 'phpvcs',
                ],
                'Name of Next Release of PHP' => [
                    0 => 'Name of Next Release of PHP',
                    1 => 'php6',
                ],
                'Remove deprecated functionality in PHP 7' => [
                    0 => 'Remove deprecated functionality in PHP 7',
                    1 => 'remove_deprecated_functionality_in_php7',
                ],
                'Remove PHP 4 Constructors' => [
                    0 => 'Remove PHP 4 Constructors',
                    1 => 'remove_php4_constructors',
                ],
            ]
        ];

        $declinedSection = [
            'Declined' => [
                'In Operator' => [
                    0 => 'In Operator',
                    1 => 'in_operator',
                ],
                'Coercive Scalar Type Hints' => [
                    0 => 'Coercive Scalar Type Hints',
                    1 => 'coercive_sth',
                ],
                'Make empty() a Variadic' => [
                    0 => 'Make empty() a Variadic',
                    1 => 'variadic_empty',
                ],
                'Allow error_handler callback parameters to be passed by reference' => [
                    0 => 'Allow error_handler callback parameters to be passed by reference',
                    1 => 'error_handler_callback_parameters_passed_by_reference',
                ],
                'pecl_http' => [
                    0 => 'pecl_http',
                    1 => 'pecl_http',
                ],
            ]
        ];

        $withdrawnSection = [
            'Withdrawn' => [
                ' ReflectionParameter::getClassName()' => [
                    0 => ' ReflectionParameter::getClassName()',
                    1 => 'reflectionparameter-getclassname',
                ],
                'Big Integer Support' => [
                    0 => 'Big Integer Support',
                    1 => 'bigint',
                ],
                'Scalar Type Hints' => [
                    0 => 'Scalar Type Hints',
                    1 => 'scalar_type_hints',
                ],
                'Void Return Type' => [
                    0 => 'Void Return Type',
                    1 => 'void_return_type',
                ],
                'Deprecate function that modify INI' => [
                    0 => 'Deprecate function that modify INI',
                    1 => 'deprecate-ini-functions',
                ],
            ]
        ];

        return [
            [ // Test gets all
                [],
                array_merge(
                    $votingSection,
                    $discussionSectoin,
                    $draftSection,
                    $acceptedSection,
                    $declinedSection,
                    $withdrawnSection
                )
            ],
            [ // Voting section only
                ['in_voting_phase'],
                $votingSection
            ],
            [ // Voting section and accepted
                ['in_voting_phase', 'accepted'],
                array_merge($votingSection, $acceptedSection)
            ],
            [ // Accepted, declined and withdrawn
                ['accepted', 'declined', 'withdrawn'],
                array_merge($acceptedSection, $declinedSection, $withdrawnSection)
            ]
        ];
    }
}