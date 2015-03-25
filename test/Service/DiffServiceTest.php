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
}
