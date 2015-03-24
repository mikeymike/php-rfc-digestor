<?php

namespace MikeyMike\RfcDigestor\Service;

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
}
