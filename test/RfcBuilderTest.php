<?php

namespace MikeyMike\RfcDigestor;

/**
 * Class RfcBuilder
 * @package MikeyMike\RfcDigestor\Test
 */
class RfcBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RfcBuilder
     */
    protected $rfcBuilder;

    public function setUp()
    {
        $this->rfcBuilder = new RfcBuilder(realpath(__DIR__ . '/../storage'));
    }

    public function testConstructSetsStoragePath()
    {
        $reflectionClass    = new \ReflectionClass($this->rfcBuilder);
        $reflectionProperty = $reflectionClass->getProperty('storagePath');
        $reflectionProperty->setAccessible(true);

        $this->assertSame(realpath(__DIR__ . '/../storage'), $reflectionProperty->getValue($this->rfcBuilder));
    }

    public function testConstructTrimsEndSlashes()
    {
        $rfcBuilder = new RfcBuilder(realpath(__DIR__ . '/../storage/'));

        $reflectionClass    = new \ReflectionClass($rfcBuilder);
        $reflectionProperty = $reflectionClass->getProperty('storagePath');
        $reflectionProperty->setAccessible(true);

        $this->assertSame(realpath(__DIR__ . '/../storage'), $reflectionProperty->getValue($rfcBuilder));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructThrowErrorWhenNoDirExists()
    {
        new RfcBuilder(realpath(__DIR__ . '../nonExistentStorage'));
    }
}
