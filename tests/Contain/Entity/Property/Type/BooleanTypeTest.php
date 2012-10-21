<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;

class BooleanTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    public function setUp()
    {
        $this->entity = new SampleMultiTypeEntity();
    }

    public function testParse()
    {
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->parse(true));
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->parse(1));
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->parse('1'));
        $this->assertTrue('1' === $this->entity->property('boolean')->getType()->parse(true));

        $this->assertEquals('0', $this->entity->property('boolean')->getType()->parse(false));
        $this->assertEquals('0', $this->entity->property('boolean')->getType()->parse(0));
        $this->assertEquals('0', $this->entity->property('boolean')->getType()->parse('0'));
        $this->assertTrue('0' === $this->entity->property('boolean')->getType()->parse(false));
    }

    public function testExport()
    {
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->export(true));
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->export(1));
        $this->assertEquals('1', $this->entity->property('boolean')->getType()->export('1'));
        $this->assertTrue('1' === $this->entity->property('boolean')->getType()->export(true));

        $this->assertEquals('0', $this->entity->property('boolean')->getType()->export(false));
        $this->assertEquals('0', $this->entity->property('boolean')->getType()->export(0));
        $this->assertEquals('0', $this->entity->property('boolean')->getType()->export('0'));
        $this->assertTrue('0' === $this->entity->property('boolean')->getType()->export(false));
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
