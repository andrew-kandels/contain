<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;

class EnumTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('enum');
        $this->type = $property->getType();
    }

    public function testParseNoValue()
    {
        $this->assertNull($this->type->parse(false));
        $this->assertNull($this->type->parse('invalid'));
        $this->assertEquals('one', $this->type->parse('one'));
    }

    public function testOptions()
    {
        $this->type->setOption('options', array('test'));
        $this->assertNull($this->type->parse('one'));
        $this->assertEquals('test', $this->type->parse('test'));
    }

    public function getValidators()
    {
        $this->assertEquals(array(array('name' => 'InArray', 'options' => array('haystack' => $this->type->getOption('options')))), $this->type->getValidators());
    }
}
