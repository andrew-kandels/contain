<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;

class IntegerTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('integer');
        $this->type = $property->getType();
    }

    public function testParseZero()
    {
        $this->assertTrue(0 === $this->type->parse(0));
        $this->assertTrue(0 === $this->type->parse('0'));
    }

    public function testParseEmpty()
    {
        $this->assertNull($this->type->parse(false));
        $this->assertNull($this->type->parse(''));
    }

    public function testParseString()
    {
        $this->assertTrue(1 === $this->type->parse('1'));
    }

    public function testExport()
    {
        $this->assertNull($this->type->export(null));
        $this->assertTrue(1 === $this->type->export(1));
    }

    public function testGetEmptyValue()
    {
        $this->assertFalse($this->type->getEmptyValue());
    }

    public function getValidators()
    {
        $this->assertEquals(array(array('name' => 'Digits')), $this->type->getValidators());
    }
}
