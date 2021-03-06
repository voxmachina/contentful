<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Entry;
use Markup\Contentful\Exception\LinkUnresolvableException;
use Mockery as m;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->fields = [
            'foo' => 'bar',
        ];
        $this->metadata = m::mock('Markup\Contentful\MetadataInterface');
        $this->entry = new Entry($this->fields, $this->metadata);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $this->entry);
    }

    public function testGetFieldsUsingArrayAccess()
    {
        $this->assertTrue(isset($this->entry['foo']));
        $this->assertFalse(isset($this->entry['unknown']));
        $this->assertEquals('bar', $this->entry['foo']);
        $this->assertNull($this->entry['unknown']);
    }

    public function testGetField()
    {
        $this->assertEquals('bar', $this->entry->getField('foo'));
        $this->assertNull($this->entry->getField('unknown'));
    }

    public function testResolveLink()
    {
        $link = m::mock('Markup\Contentful\Link');
        $asset = m::mock('Markup\Contentful\AssetInterface');
        $callback = function ($link) use ($asset) {
            return $asset;
        };
        $fields = [
            'asset' => $link,
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $this->assertSame($asset, $entry['asset']);
    }

    public function testUnknownMethodFallsBackToFieldLookup()
    {
        $this->assertEquals('bar', $this->entry->foo());
        $this->assertEquals(null, $this->entry->baz());
    }

    public function testUnresolvedLinkFiltersOutFromList()
    {
        $link = m::mock('Markup\Contentful\Link')->shouldIgnoreMissing();
        $callback = function ($link) {
            throw new LinkUnresolvableException($link);
        };
        $fields = [
            'assets' => [$link],
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $assets = $entry['assets'];
        $this->assertCount(0, $assets);
    }

    public function testUnresolvedSingleLinkEmitsNull()
    {
        $link = m::mock('Markup\Contentful\Link')->shouldIgnoreMissing();
        $callback = function ($link) {
            throw new LinkUnresolvableException($link);
        };
        $fields = [
            'asset' => $link,
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $this->assertNull($entry['asset']);
    }
}
