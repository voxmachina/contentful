<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ContentTypeFilterProvider;
use Mockery as m;

class ContentTypeFilterProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->contentful = m::mock('Markup\Contentful\Contentful');
        $this->provider = new ContentTypeFilterProvider($this->contentful);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testCreateForExistingContentType()
    {
        $contentType = m::mock('Markup\Contentful\ContentTypeInterface');
        $id = 42;
        $contentType
            ->shouldReceive('getId')
            ->andReturn($id);
        $name = 'unique_type';
        $contentType
            ->shouldReceive('getName')
            ->andReturn($name);
        $this->contentful
            ->shouldReceive('getContentTypeByName')
            ->with($name, m::any())
            ->andReturn($contentType);
        $filter = $this->provider->createForContentTypeName($name);
        $this->assertInstanceOf('Markup\Contentful\Filter\ContentTypeFilter', $filter);
        $this->assertEquals($id, $filter->getValue());
    }

    public function testCreateForNotExistingContentType()
    {
        $this->contentful
            ->shouldReceive('getContentTypeByName')
            ->andReturn(null);
        $this->assertNull($this->provider->createForContentTypeName('unknown'));
    }
}
