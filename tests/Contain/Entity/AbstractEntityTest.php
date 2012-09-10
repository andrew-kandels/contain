<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleEntity;

class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    public function setUp()
    {
        $this->entity = new SampleEntity();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Contain\Entity\EntityInterface', $this->entity);
    }

    public function testConstructWithArray()
    {
        $entity = new SampleEntity($values = array(
            'firstName' => 'Andrew',
        ));
        $this->assertEquals($values, $entity->export());
    }

    public function testConstructWithEntity()
    {
        $entity = new SampleEntity($this->entity);
        $this->assertEquals($this->entity->export(), $entity->export());
    }

    public function testGetEventManager()
    {
        $this->assertInstanceOf('Zend\EventManager\EventManager', $this->entity->getEventManager());
    }

    public function testSetEventManager()
    {
        $eventManager = new \Zend\EventManager\EventManager();
        $this->entity->setEventManager($eventManager);
        $this->assertSame($eventManager, $this->entity->getEventManager());
    }

    public function testGetExtendedProperty()
    {
        $this->assertEquals(1, $this->entity->setExtendedProperty('num', 1)
                                            ->getExtendedProperty('num'));
    }

    public function testSetExtendedProperty()
    {
        $testValues = array(
            1,
            'test',
            array('one', 'two'),
            new \stdclass(),
        );

        foreach ($testValues as $testValue) {
            $this->entity->setExtendedProperty('name', $testValue);
            $this->assertSame($testValue, $this->entity->getExtendedProperty('name'));
        }
    }

    public function testGetExtendedProperties()
    {
        $this->entity->setExtendedProperty('one', 1)
                     ->setExtendedProperty('two', 2);

        $this->assertEquals(
            array('one' => 1, 'two' => 2),
            $this->entity->getExtendedProperties()
        );
    }

    public function testPrimary()
    {
        $this->entity->property('firstName')->setOption('primary', true);

        $this->entity->fromArray($values = array(
            'firstName' => 'Andrew',
        ));

        $this->assertEquals($values, $this->entity->primary());
    }

    public function testCleanOne()
    {
        $this->assertFalse((boolean) $this->entity->dirty());
        $this->entity->setFirstName('Andrew');
        $this->assertTrue((boolean) $this->entity->dirty());
        $this->entity->clean('firstName');
        $this->assertFalse((boolean) $this->entity->dirty());
    }

    public function testCleanMany()
    {
        $this->assertFalse((boolean) $this->entity->dirty());
        $this->entity->setFirstName('Andrew');
        $this->assertTrue((boolean) $this->entity->dirty());
        $this->entity->clean(array('firstName'));
        $this->assertFalse((boolean) $this->entity->dirty());
    }

    public function testCleanAll()
    {
        $this->assertFalse((boolean) $this->entity->dirty());
        $this->entity->setFirstName('Andrew');
        $this->assertTrue((boolean) $this->entity->dirty());
        $this->entity->clean();
        $this->assertFalse((boolean) $this->entity->dirty());
    }

    public function testClearOne()
    {
        $this->entity->setFirstName($name = 'Andrew');
        $this->assertEquals($name, $this->entity->getFirstName());
        $this->entity->clear('firstName');
        $this->assertTrue($this->entity->property('firstName')->isUnset());
    }

    public function testClearMany()
    {
        $this->entity->setFirstName($name = 'Andrew');
        $this->assertEquals($name, $this->entity->getFirstName());
        $this->entity->clear(array('firstName'));
        $this->assertTrue($this->entity->property('firstName')->isUnset());
    }

    public function testClearAll()
    {
        $this->assertFalse((boolean) $this->entity->dirty());
        $this->entity->setFirstName('Andrew');
        $this->assertTrue((boolean) $this->entity->dirty());
        $this->entity->clear();
        $this->assertFalse((boolean) $this->entity->dirty());
    }

    public function testDirty()
    {
        $this->assertEquals(array(), $this->entity->dirty());
        $this->entity->setFirstName('Andrew');
        $this->assertEquals(array('firstName'), $this->entity->dirty());
    }

    public function testMarkDirty()
    {
        $this->assertEquals(array(), $this->entity->dirty());
        $this->entity->markDirty('firstName');
        $this->assertEquals(array('firstName'), $this->entity->dirty());
    }

    public function testType()
    {
        $this->assertInstanceOf('Contain\Entity\Property\Type\StringType', $this->entity->type('firstName'));
        $this->assertNull($this->entity->type('invalidPropertyName'));
    }

    public function testProperties()
    {
        $this->assertEquals(array(), $this->entity->properties());
        $this->assertEquals(array('firstName', 'child'), $this->entity->properties(true));
        $this->entity->setFirstName('Andrew');
        $this->assertEquals(array('firstName'), $this->entity->properties());
    }

    public function testToArray()
    {
        $this->assertEquals(array(), $this->entity->toArray());
        $this->assertEquals(
            array(
                'firstName' => $this->entity->property('firstName')->getType()->getUnsetValue(),
                'child'     => $this->entity->getChild(),
            ), 
            $this->entity->toArray(true)
        );
        $this->entity->setFirstName('Andrew');
        $this->assertEquals(array('firstName' => 'Andrew'), $this->entity->toArray());
    }

    public function testFromArray()
    {
        $this->assertEquals(array(), $this->entity->toArray());
        $this->entity->fromArray(array(
            'firstName' => 'Andrew',
        ));
        $this->assertEquals(array('firstName' => 'Andrew'), $this->entity->toArray());
    }

    public function testExport()
    {
        $this->assertEquals(array(), $this->entity->export());
        $this->assertEquals(
            array(
                'firstName' => $this->entity->property('firstName')->getType()->getUnsetValue(),
                'child'     => array(),
            ), 
            $this->entity->export(null, true)
        );
        $this->entity->fromArray(array(
            'firstName' => 'Andrew',
        ));
        $this->assertEquals(array('firstName' => 'Andrew'), $this->entity->export());
    }

    public function testExportWithString()
    {
        $this->entity->setFirstName('Andrew');
        $this->assertEquals(array('firstName' => 'Andrew'), $this->entity->export('firstName'));
    }

    public function testExportWithArray()
    {
        $this->entity->setFirstName('Andrew');
        $this->assertEquals(array('firstName' => 'Andrew'), $this->entity->export(array('firstName')));
    }

    public function testProperty()
    {
        $this->assertInstanceOf('Contain\Entity\Property\Property', $this->entity->property('firstName'));
        $this->assertNull($this->entity->property('invalidPropertyName'));
    }

    public function testOnEventGetter()
    {
        $this->assertEquals('old', $this->entity->onEventGetter('firstName', 'old', false));
    }

    public function testOnEventGetterWithReplace()
    {
        $this->entity->getEventManager()->attach('property.get', function ($e) {
            $property = $e->getParam('property');
            $property['value'] = 'newvalue';
            $e->setParam('property', $property);
        });

        $this->assertEquals('newvalue', $this->entity->onEventGetter('firstName', 'old', false));
    }

    public function testOnEventSetter()
    {
        $this->entity->getEventManager()->attach('property.set', function ($e) {
            $property = $e->getParam('property');
            $property['value'] = 'newvalue';
            $e->setParam('property', $property);
        });

        $this->assertEquals(
            'newvalue', 
            $this->entity->onEventSetter(
                'firstName', 
                'old', 
                'new',
                true
            )
        );
    }

    public function testOnEventSetterWithReplace()
    {
        $this->assertEquals(
            'new', 
            $this->entity->onEventSetter(
                'firstName', 
                'old', 
                'new',
                true
            )
        );
    }

    public function testChildEntityInstantiated()
    {
        $this->assertEquals(array(), $this->entity->export('child'));
        $this->assertEquals(array('child' => array()), $this->entity->export('child', true));
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $this->entity->getChild());
    }

    public function testChildEntityDirty()
    {
        $this->entity->getChild()->setFirstName('Samantha');
        $this->assertEquals(array('child'), $this->entity->dirty());
    }

    public function testChildEntityClean()
    {
        $this->entity->getChild()->setFirstName('Samantha');
        $this->entity->clean('child');
        $this->assertEquals(array(), $this->entity->dirty());
    }
}
