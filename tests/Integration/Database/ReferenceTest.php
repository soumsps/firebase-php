<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

class ReferenceTest extends DatabaseTestCase
{
    /**
     * @var Reference
     */
    private $ref;

    protected function setUp(): void
    {
        $this->ref = self::$db->getReference(self::$refPrefix);
    }

    /**
     * @param $key
     * @param $value
     *
     * @dataProvider validValues
     */
    public function testSetAndGet($key, $value)
    {
        $ref = $this->ref->getChild(__FUNCTION__.'/'.$key);
        $ref->set($value);

        $this->assertSame($value, $ref->getValue());
    }

    public function testUpdate()
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);

        $ref->update([
            'first' => 'updated',
            'third' => 'new',
        ]);

        $expected = [
            'first' => 'updated',
            'second' => 'value',
            'third' => 'new',
        ];

        $this->assertEquals($expected, $ref->getValue());
    }

    public function testPush()
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $value = 'a value';

        $newRef = $ref->push($value);

        $this->assertSame(1, $ref->getSnapshot()->numChildren());
        $this->assertSame($value, $newRef->getValue());
    }

    public function testRemove()
    {
        $ref = $this->ref->getChild(__FUNCTION__);

        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);

        $ref->getChild('first')->remove();

        $this->assertEquals(['second' => 'value'], $ref->getValue());
    }

    public function testPushToGetKey()
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $key = $ref->push()->getKey();

        $this->assertIsString($key);
        $this->assertSame(0, $ref->getSnapshot()->numChildren());
    }

    public function testSetWithNullIsSameAsRemove()
    {
        $ref = $this->ref->getChild(__FUNCTION__);

        $key = $ref->push('foo')->getKey();

        $this->assertSame(1, $ref->getSnapshot()->numChildren());

        $ref->getChild($key)->set(null);

        $this->assertSame(0, $ref->getSnapshot()->numChildren());
    }

    public function validValues()
    {
        return [
            'string' => ['string', 'value'],
            'int' => ['int', 1],
            'bool_true' => ['true', true],
            'bool_false' => ['false', false],
            'array' => ['array', ['first' => 'value', 'second' => 'value']],
        ];
    }
}
