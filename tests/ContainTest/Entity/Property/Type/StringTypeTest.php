<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;
use ContainTest\Entity\SampleEntityToString;

class StringTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('string');
        $this->type = $property->getType();
    }

    public function testParseNoValue()
    {
        $this->assertEquals('', $this->type->parse(''));
        $this->assertNull($this->type->parse(false));
        $this->assertNull($this->type->parse(null));
    }

    public function testParseString()
    {
        $this->assertEquals('test', $this->type->parse('test'));
    }

    public function testParseNumeric()
    {
        $this->assertEquals('0', $this->type->parse(0));
        $this->assertEquals('123', $this->type->parse(123));
        $this->assertEquals('123', $this->type->parse(123.0));
        $this->assertEquals('123', $this->type->parse((double) 123.0));

        $entity = new SampleEntityToString();
        $this->assertEquals('test', $entity);
    }

    public function testParseInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value is invalid for string type'
        );

        $this->assertEquals('test', $this->type->export(new \stdclass()));
    }

    public function testExportNoValue()
    {
        $this->assertEquals('', $this->type->export(''));
        $this->assertEquals('', $this->type->export(false));
        $this->assertEquals('', $this->type->export(null));
    }

    public function testExportString()
    {
        $this->assertEquals('test', $this->type->export('test'));
    }

    public function testExportNumeric()
    {
        $this->assertEquals('0', $this->type->export(0));
        $this->assertEquals('123', $this->type->export(123));
        $this->assertEquals('123', $this->type->export(123.0));
        $this->assertEquals('123', $this->type->export((double) 123.0));

        $entity = new SampleEntityToString();
        $this->assertEquals('test', $entity);
    }

    public function testExportInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value is invalid for string type'
        );

        $this->assertEquals('test', $this->type->export(new \stdclass()));
    }

    public function testGetEmptyValue()
    {
        $this->assertTrue('' === $this->type->getEmptyValue());
    }

    public function testGetUnsetValue()
    {
        $this->assertNull($this->type->getUnsetValue());
    }

    public function testCantSetArbitraryOptions()
    {
        $this->type->setOptions(array('testKey1' => 'test'));
        $this->type->setOption('testKey2', 'test');
        $this->assertNull($this->type->getOption('testKey1'));
        $this->assertNull($this->type->getOption('testKey2'));
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
