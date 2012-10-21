<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;

class MixedTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('mixed');
        $this->type = $property->getType();
    }

    public function testParseObject()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value cannot be an object for type mixed'
        );
        $this->type->parse(new \stdclass());
    }

    public function testParse()
    {
        $this->assertTrue('val' === $this->type->parse('val'));
        $this->assertTrue(array(1) === $this->type->parse(array(1)));
        $this->assertTrue(1 === $this->type->parse(1));
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
