<?php

namespace MikeyMike\RfcDigestor\Service;

use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class DiffServiceTest
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class DiffServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiffService
     */
    protected $diffService;

    public function setUp()
    {
        $this->diffService = new DiffService();
    }

    public function testListDiffReturnsEmptyArrayWhenNoDiff()
    {
        $list1 = [
            'In voting phase' => [
                'In Operator'          => ['In Operator', 'in_operator'],
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation']
            ]
        ];

        $list2 = $list1;

        $this->assertEmpty($this->diffService->listDiff($list1, $list2));
    }

    public function testListDiffReturnsCorrectDiff()
    {
        $list1 = [
            'In voting phase' => [
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation']
            ],
            'Accepted' => [
                'In Operator' => ['In Operator', 'in_operator']
            ]
        ];

        $list2 = [
            'In voting phase' => [
                'In Operator'          => ['In Operator', 'in_operator'],
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation']
            ],
            'Accepted' => []
        ];

        $expected = [
            'In Operator' => [
                'to'   => 'Accepted',
                'from' => 'In voting phase'
            ]
        ];

        $actual = $this->diffService->listDiff($list1, $list2);

        $this->assertSame($expected, $actual);
    }

    public function testListDiffFromIsEmptyWhenNewListing()
    {
        $list1 = [
            'In voting phase' => [
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation'],
                'In Operator' => ['In Operator', 'in_operator']
            ]
        ];

        $list2 = [
            'In voting phase' => [
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation']
            ]
        ];

        $expected = [
            'In Operator' => [
                'to'   => 'In voting phase',
                'from' => ''
            ]
        ];

        $actual = $this->diffService->listDiff($list1, $list2);

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider recursiveArrayDiffProvider
     */
    public function testRecursiveArrayDiff($arr1, $arr2, $expectedDiff)
    {
        // Private func use reflection
        $reflectionClass  = new \ReflectionClass($this->diffService);
        $reflectionMethod = $reflectionClass->getMethod('recursiveArrayDiff');
        $reflectionMethod->setAccessible(true);

        $actualDiff = $reflectionMethod->invoke($this->diffService, $arr1, $arr2);

        $this->assertSame($expectedDiff, $actualDiff);
    }

    public function recursiveArrayDiffProvider()
    {
        return [
            [ // Empty
                [], [], []
            ],
            [ // Simple assoc
                ['foo' => 'bar'],
                ['foo' => 'not_bar'],
                ['foo' => 'bar']
            ],
            [ // Multidimensional
                ['foo' => ['inner' => 'bar']],
                ['foo' => 'bar'],
                ['foo' => ['inner' => 'bar']]
            ],
            [ // Multidimensional new value
                ['foo' => ['inner' => 'bar', 'new' => 'value']],
                ['foo' => ['inner' => 'bar']],
                ['foo' => ['new' => 'value']]
            ],
            [ // Multidimensional new array
                ['foo' => ['inner' => 'bar']],
                [],
                ['foo' => ['inner' => 'bar']]
            ],
            [ // Only diffs changes in first array
                ['bar' => 'foo'],
                ['foo' => 'bar'],
                ['bar' => 'foo']
            ]
        ];
    }

    public function testFindRfcListKeyReturnsCorrectKey()
    {
        $list = [
            'In voting phase' => [
                'Generator Delegation' => ['Generator Delegation', 'generator_delegation'],
                'In Operator' => ['In Operator', 'in_operator']
            ],
            'Accepted' => [
                'Context Sensitive Lexer' => ['Context Sensitive Lexer', 'context_sensitive_lexer']
            ]
        ];

        $reflectionClass  = new \ReflectionClass($this->diffService);
        $reflectionMethod = $reflectionClass->getMethod('findRfcListKey');
        $reflectionMethod->setAccessible(true);

        $this->assertSame(
            'In voting phase',
            $reflectionMethod->invoke($this->diffService, $list, 'In Operator')
        );

        $this->assertSame(
            '',
            $reflectionMethod->invoke($this->diffService, $list, 'Magic Quotes')
        );
    }

    public function testRfcDiffCallsRequiredMethodsOnBothRfcObjects()
    {
        $rfc = $this->getMockBuilder('MikeyMike\RfcDigestor\Entity\Rfc')
            ->setMethods(['getDetails', 'getChangeLog', 'getVotes'])
            ->getMock();

        $rfc->expects($this->exactly(2))
            ->method('getDetails')
            ->will($this->returnValue([]));

        $rfc->expects($this->exactly(2))
            ->method('getChangeLog')
            ->will($this->returnValue([]));

        $rfc->expects($this->exactly(3))
            ->method('getVotes')
            ->will($this->returnValue([]));

        $this->diffService->rfcDiff($rfc, $rfc);
    }

    /**
     * @dataProvider rfcDiffDataProvider
     */
    public function testRfcDiffReturnsCorrectDiff($details, $changeLogs, $votes, $expectedDiff)
    {
        $rfc1 = new Rfc();
        $rfc2 = new Rfc();

        $rfc1->setDetails($details[0]);
        $rfc1->setChangeLog($changeLogs[0]);
        $rfc1->setVotes($votes[0]);

        $rfc2->setDetails($details[1]);
        $rfc2->setChangeLog($changeLogs[1]);
        $rfc2->setVotes($votes[1]);

        $this->assertSame($expectedDiff, $this->diffService->rfcDiff($rfc1, $rfc2));
    }

    public function rfcDiffDataProvider()
    {
        return [
            [ // Testing Empty
                [ // Details
                    [], []
                ],
                [ // ChangeLogs
                    [], []
                ],
                [ // Votes
                    [], []
                ],
                [ // Expected Diff
                    'details'   => [],
                    'changeLog' => [],
                    'votes'     => []
                ]
            ],
            [ // Testing Same
                [ // Details
                    [
                        'version'            => '0.5.1',
                        'name'               => 'In Operator',
                        'authors'            => 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com',
                        'status'             => 'Voting',
                        'First Published at' => 'http://wiki.php.net/rfc/in_operator'
                    ],
                    [
                        'version'            => '0.5.1',
                        'name'               => 'In Operator',
                        'authors'            => 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com',
                        'status'             => 'Voting',
                        'First Published at' => 'http://wiki.php.net/rfc/in_operator'
                    ]
                ],
                [ // ChangeLogs
                    [
                        'v0.5:' => 'Removed integer support, so the strictness is consistent.',
                        'v0.4:' => 'Removed possibility to check multiple values using an array.'
                    ],
                    [
                        'v0.5:' => 'Removed integer support, so the strictness is consistent.',
                        'v0.4:' => 'Removed possibility to check multiple values using an array.'
                    ]
                ],
                [ // Votes
                    [
                        'Introduce the in operator?' => [
                            'headers' => ['Real name', 'Yes', 'No'],
                            'votes'   => [
                                'aharvey (aharvey)' => ['Yes' => false, 'No' => true],
                                'ajf (ajf)'         => ['Yes' => false, 'No' => true]
                            ],
                            'counts'  => ['Real name' => 'Count:', 'Yes' => 0, 'No' => 2],
                            'closed'  => false
                        ]
                    ],
                    [
                        'Introduce the in operator?' => [
                            'headers' => ['Real name', 'Yes', 'No'],
                            'votes'   => [
                                'aharvey (aharvey)' => ['Yes' => false, 'No' => true],
                                'ajf (ajf)'         => ['Yes' => false, 'No' => true]
                            ],
                            'counts'  => ['Real name' => 'Count:', 'Yes' => 0, 'No' => 2],
                            'closed'  => false
                        ]
                    ]
                ],
                [ // Expected Diff
                    'details'   => [],
                    'changeLog' => [],
                    'votes'     => []
                ]
            ],
            [ // Testing Different
                [ // Details
                    [
                        'version'            => '0.6.0',
                        'name'               => 'In Operator',
                        'authors'            => 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com',
                        'status'             => 'Voting',
                        'First Published at' => 'http://wiki.php.net/rfc/in_operator'
                    ],
                    [
                        'version'            => '0.5.1',
                        'name'               => 'In Operator',
                        'authors'            => 'Niklas Keller me@kelunik.com, Bob Weinand bobwei9@hotmail.com',
                        'status'             => 'Voting',
                        'First Published at' => 'http://wiki.php.net/rfc/in_operator'
                    ]
                ],
                [ // ChangeLogs
                    [
                        'v0.6:' => 'Updated for some reason',
                        'v0.5:' => 'Removed integer support, so the strictness is consistent.',
                        'v0.4:' => 'Removed possibility to check multiple values using an array.'
                    ],
                    [
                        'v0.5:' => 'Removed integer support, so the strictness is consistent.',
                        'v0.4:' => 'Removed possibility to check multiple values using an array.'
                    ]
                ],
                [ // Votes
                    [
                        'Introduce the in operator?' => [
                            'headers' => ['Real name', 'Yes', 'No'],
                            'votes'   => [
                                'aharvey (aharvey)' => ['Yes' => true,  'No' => false],
                                'ajf (ajf)'         => ['Yes' => false, 'No' => true],
                                'bwoebi (bwoebi)'   => ['Yes' => true,  'No' => false]
                            ],
                            'counts'  => ['Real name' => 'Count:', 'Yes' => 2, 'No' => 1],
                            'closed'  => false
                        ]
                    ],
                    [
                        'Introduce the in operator?' => [
                            'headers' => ['Real name', 'Yes', 'No'],
                            'votes'   => [
                                'aharvey (aharvey)' => ['Yes' => false, 'No' => true],
                                'ajf (ajf)'         => ['Yes' => false, 'No' => true]
                            ],
                            'counts'  => ['Real name' => 'Count:', 'Yes' => 0, 'No' => 2],
                            'closed'  => false
                        ]
                    ]
                ],
                [ // Expected Diff
                    'details' => [
                        'version' => '0.6.0',
                    ],
                    'changeLog' => [
                        'v0.6:' => 'Updated for some reason'
                    ],
                    'votes' => [
                        'Introduce the in operator?' => [
                            'new' => [
                                'bwoebi (bwoebi)' => 'Yes'
                            ],
                            'updated' => [
                                'aharvey (aharvey)' => 'Yes'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
