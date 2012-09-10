<?php
namespace ContainTest\Entity\Property;

use Contain\Entity\Property\Property;
use ContainTest\Entity\SampleEntity;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    protected $firstName;
    protected $sampleEntity;

    public function setUp()
    {
        $this->firstName = new Property('string');
        $this->entity    = new Property('\ContainTest\Entity\SampleEntity');
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Contain\Entity\Property\Property', $this->firstName);
    }

    public function testSetValue()
    {
        $this->assertEquals($name = 'andrew', $this->firstName->setValue('andrew')->getValue());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->firstName->getType()->getUnsetValue(), $this->firstName->getValue());
    }

    public function testIsUnset()
    {
        $this->assertEquals($this->firstName->getType()->getUnsetValue(), $this->firstName->getValue());
        $this->assertTrue($this->firstName->isUnset());
        $this->firstName->setValue('andrew');
        $this->assertFalse($this->firstName->isUnset());
    }

    public function testIsEmpty()
    {
        $this->firstName->setEmpty();
        $this->assertTrue($this->firstName->isEmpty());
        $this->firstName->setValue('andrew');
        $this->assertFalse($this->firstName->isEmpty());
    }

    public function testClean()
    {
        $this->assertFalse($this->firstName->isDirty());
        $this->firstName->setValue('andrew');
        $this->assertTrue($this->firstName->isDirty());
        $this->firstName->clean();
        $this->assertFalse($this->firstName->isDirty());
    }

    public function testExport()
    {
        $this->firstName->setValue($name = 'andrew');
        $this->assertEquals($name, $this->firstName->export());
    }

    public function testIsDirty()
    {
        $this->assertFalse($this->firstName->isDirty());
        $this->firstName->setValue('andrew');
        $this->assertTrue($this->firstName->isDirty());
    }

    public function testGetPersistedValue()
    {
        $this->firstName->setValue($name = 'Andrew');
        $this->assertEquals($this->firstName->getType()->getUnsetValue(), $this->firstName->getPersistedValue());
        $this->firstName->clean();
        $this->assertEquals($name, $this->firstName->getPersistedValue());
    }

    public function testSetTypeWithInstance()
    {
        $type = new \Contain\Entity\Property\Type\IntegerType;
        $this->firstName->setType($type);
        $this->assertEquals($type, $this->firstName->getType());
    }

    public function testSetTypeWithString()
    {
        $this->firstName->setType('integer');
        $this->assertInstanceOf('Contain\Entity\Property\Type\IntegerType', $this->firstName->getType());
    }

    public function testSetTypeWithInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$type does not implement Contain\Entity\Property\Type\TypeInterface.'
        );

        $this->firstName->setType('\stdclass');
    }

    public function testGetType()
    {
        $this->firstName->setType('integer');
        $this->assertInstanceOf('Contain\Entity\Property\Type\IntegerType', $this->firstName->getType());
    }

    public function testSetOptionsWithInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$options must be an instance of Traversable or an array.'
        );

        $this->firstName->setOptions(1);
    }

    public function testSetOptionsWithArray()
    {
        $this->firstName->setOptions(array(
            'primary' => true,
        ));

        $this->assertTrue($this->firstName->getOption('primary'));
    }

    public function testSetOptionWithInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$name is not a valid option.'
        );

        $this->firstName->setOption('invalidOptionName', 'something');
    }

    public function testSetOption()
    {
        $this->firstName->setOption('primary', true);
        $this->assertTrue($this->firstName->getOption('primary'));
    }

    public function testGetOptions()
    {
        $this->firstName->setOption('primary', true);
        $this->assertEquals(
            array('primary' => true),
            $this->firstName->getOptions()
        );
    }

    public function testGetOptionWithInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$name is not a valid option.'
        );

        $this->firstName->getOption('invalidOptionName');
    }

    public function testEntityIsOfEntityType()
    {
        $this->assertInstanceOf('Contain\Entity\Property\Type\EntityType', $this->entity->getType());
    }

    public function testIsUnsetWithEntity()
    {
        $this->assertTrue($this->entity->isUnset());
        $this->entity->getValue()->setFirstName('somethingelse');
        $this->assertFalse($this->entity->isUnset());
        $this->entity->clean('firstName');
        $this->assertFalse($this->entity->isDirty());
    }

    public function testIsEmptyWithEntity()
    {
        $this->entity->setEmpty();
        $this->assertTrue($this->entity->isEmpty());
        $this->entity->getValue()->setFirstName('somethingelse');
        $this->assertFalse($this->entity->isEmpty());
        $this->entity->setEmpty();
        $this->assertTrue($this->entity->isEmpty());
    }

    public function testGetPersistedValueWithEntity()
    {
        $this->entity->getValue()->fromArray($values = array(
            'firstName' => 'Andrew',
        ));

        $this->assertEquals(array(), $this->entity->getPersistedValue()->export());
        $this->entity->clean();
        $this->assertEquals($values, $this->entity->getPersistedValue()->export());
    }
}
