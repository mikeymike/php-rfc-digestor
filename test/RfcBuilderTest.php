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

    /**
     * @var string
     */
    protected $resourcesPath;

    public function setUp()
    {
        $this->resourcesPath = realpath(__DIR__ . '/resources');
        $this->rfcBuilder    = new RfcBuilder($this->resourcesPath);
    }

    public function testConstructSetsStoragePath()
    {
        $reflectionClass    = new \ReflectionClass($this->rfcBuilder);
        $reflectionProperty = $reflectionClass->getProperty('storagePath');
        $reflectionProperty->setAccessible(true);

        $this->assertSame(realpath(__DIR__ . '/resources'), $reflectionProperty->getValue($this->rfcBuilder));
    }

    public function testConstructTrimsEndSlashes()
    {
        $rfcBuilder = new RfcBuilder(realpath(__DIR__ . '/resources/'));

        $reflectionClass    = new \ReflectionClass($rfcBuilder);
        $reflectionProperty = $reflectionClass->getProperty('storagePath');
        $reflectionProperty->setAccessible(true);

        $this->assertSame(realpath(__DIR__ . '/resources'), $reflectionProperty->getValue($rfcBuilder));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructThrowErrorWhenNoDirExists()
    {
        new RfcBuilder(realpath(__DIR__ . '../nonExistentStorage'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadFromWikiThrowsExceptionOnInvalidRfcCode()
    {
        $this->rfcBuilder->loadFromWiki('some_crazy_non_existing_rfc_code', 'https://wiki.php.net/rfc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadFromStorageThrowsExceptionWhenNoStoredRfc()
    {
        $this->rfcBuilder->loadFromStorage('some_crazy_non_existing_rfc_code');
    }

    public function testLoadFromStorageGetsCorrectRfc()
    {
        $rfc = $this->rfcBuilder->loadFromStorage('scalar_type_hints')->getRfc();

        $rfcPath = $this->resourcesPath . '/scalar_type_hints.html';

        // Suppress HTML5 errors
        libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTMLFile($rfcPath);

        // Turn errors back on
        libxml_use_internal_errors(false);

        $this->assertSame('scalar_type_hints', $rfc->getCode());
        $this->assertSame($document->saveHTML(), $rfc->getRawContent());
    }

    /**
     * @dataProvider loadNameLoadsCorrectlyDataProvider
     */
    public function testLoadNameLoadsCorrectly($rfcCode, $expectedName)
    {
        $this->rfcBuilder
            ->loadFromStorage($rfcCode)
            ->loadName();

        $rfc = $this->rfcBuilder->getRfc();

        $this->assertSame($expectedName, $rfc->getName());
    }

    /**
     * @return array
     */
    public function loadNameLoadsCorrectlyDataProvider()
    {
        return [
            ['scalar_type_hints', 'Scalar Type Hints'],
            ['reserve_even_more_types_in_php_7', 'Reserve Even More Types in PHP 7'],
            ['php6', 'Name of Next Release of PHP']
        ];
    }
}
