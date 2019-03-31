<?php

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\LimitToFirst;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

class LimitToFirstTest extends UnitTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToFirst(0);
    }

    public function testModifyUri()
    {
        $filter = new LimitToFirst(3);

        $this->assertStringContainsString('limitToFirst=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
