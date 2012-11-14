<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;

class ListTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    public function setUp()
    {
        $this->entity = new SampleMultiTypeEntity();
    }

    public function testUnsetValue()
    {
        $this->assertEquals($this->entity->type('list')->getUnsetValue(), $this->entity->getList());
        $this->assertEquals(array(), $this->entity->type('list')->getUnsetValue());
    }

    public function testEmptyValue()
    {
        $this->entity->property('list')->setEmpty();
        $this->assertEquals($this->entity->type('list')->getEmptyValue(), $this->entity->getList());
        $this->assertEquals(array(), $this->entity->type('list')->getEmptyValue());
    }

    public function testSetEntityWithInvalidEntity()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value is not of type Contain\Property\Type\EntityType, an array, or an instance of Traversable.'
        );

        $this->entity->setListEntity(array(1));
    }

    public function testSetTypeString()
    {
        $this->entity->type('list')->setOption('type', 'string');
        $this->assertEquals(array('test'), $this->entity->setList(array('test'))->getList());
    }

    public function testSetTypeListThrowsException()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$type may not be a nested instance of Contain\Entity\Property\Type\ListType.'
        );

        $this->entity->type('list')->setOption('type', 'list');
        $this->entity->setList(array(array('1')));
    }

    public function testSetTypeEntityWithoutClassName()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$type of entity must specify a className option that points to a class that implements Contain\Entity\EntityInterface.'
        );

        $this->entity->type('list')->setOption('type', 'entity');
        $this->entity->setList(array('firstName' => ''));
    }

    public function testParseEmptyGivesUnsetValue()
    {
        $this->assertEquals(
            $this->entity->type('list')->getUnsetValue(),
            $this->entity->setList(false)->getList()
        );
    }

    public function testParseSingleImpliesArray()
    {
        $this->assertEquals(
            array(1),
            $this->entity->setList(1)->getList()
        );
    }

    public function testParseArrayCreatesEntity()
    {
        $values = array('firstName' => 'Andrew');
        $this->entity->setListEntity(array($values));
        $value = $this->entity->getListEntity();
        $value = $value[0];
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $value);
        $this->assertEquals($value->export(), $values);
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
