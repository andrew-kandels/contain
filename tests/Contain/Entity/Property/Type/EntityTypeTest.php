<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;
use ContainTest\Entity\SampleChildEntity;

class EntityTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        $entity = new SampleMultiTypeEntity();
        $this->type = $entity->type('entity');
    }

    public function testAbleToDistinguishSingleDirtyProperty()
    {
        $entity = new SampleMultiTypeEntity();
        $entity->getEntity()->define('lastName', 'string');
        $entity->clean();

        $this->assertEquals(array(), $entity->dirty());
        $entity->getEntity()->setFirstName('Mr.');
        $this->assertEquals(array('entity'), $entity->dirty());
        $this->assertEquals(array('firstName'), $entity->getEntity()->dirty());

        $entity->reset();
        $this->assertEquals(array(), $entity->dirty());

        $entity->getEntity()->fromArray(array(
            'firstName' => 'Mrs.',
            'lastName'  => 'Smith',
        ));

        $this->assertEquals(array('firstName', 'lastName'), $entity->getEntity()->dirty());
        $entity->getEntity()->clean('lastName');
        $this->assertEquals(array('firstName'), $entity->getEntity()->dirty());

        $entity->getEntity()->markDirty('firstName');
        $this->assertEquals(array('firstName'), $entity->getEntity()->dirty());
    }

    public function testGetInstanceNoClass()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\RuntimeException',
            '$value is invalid because no type has been set for type entity'
        );
        $this->type->removeOption('className');
        $this->type->getInstance();
    }

    public function testGetInstanceInterface()
    {
        $this->type->setOption('className', 'Contain\Entity\EntityInterface');
        $this->assertNull($this->type->getInstance());
    }

    public function testGetInstanceInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            'getInstance attempting to create non-existing object "InvalidClassName"'
        );

        $this->type->setOption('className', 'InvalidClassName');
        $this->type->getInstance();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $this->type->getInstance());
    }

    public function testGetInstanceProperties()
    {
        $entity = $this->type->getInstance(array('firstName' => 'Test'));
        $this->assertEquals('Test', $entity->getFirstName());
    }

    public function testParseEmpty()
    {
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $this->type->parse(false));
    }

    public function testParseNoClassName()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\RuntimeException',
            '$value is invalid because no type has been set for type entity'
        );
        $this->type->removeOption('className');
        $this->type->parse(false);
    }

    public function testParseEntity()
    {
        $entity = new SampleChildEntity();
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $stored = $this->type->parse($entity));
        $this->assertNotSame($entity, $stored);
    }

    public function testParseArrayCreatesEntity()
    {
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $stored = $this->type->parse(array(
            'firstName' => 'Test',
        )));
        $this->assertEquals(array('firstName' => 'Test'), $stored->export());
    }

    public function testParseInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value is not of type Contain\Property\Type\EntityType, an array, or an instance of Traversable.'
        );
        $this->type->parse(new \stdclass());
    }

    public function testExport()
    {
        $this->assertEquals($val = array('firstName' => 'Test'), $this->type->export($this->type->getInstance($val)));
    }

    public function testGetEmptyValue()
    {
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $this->type->getEmptyValue());
    }

    public function testGetUnsetValue()
    {
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $this->type->getUnsetValue());
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
