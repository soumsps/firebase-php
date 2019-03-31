<?php

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EndAt;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

class EndAtTest extends UnitTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new EndAt(null);
    }

    /**
     * @param $given
     * @param $expected
     *
     * @dataProvider valueProvider
     */
    public function testModifyUri($given, $expected)
    {
        $filter = new EndAt($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    public function valueProvider()
    {
        return [
            [1, 'endAt=1'],
            ['value', 'endAt=%22value%22'],
        ];
    }
}
