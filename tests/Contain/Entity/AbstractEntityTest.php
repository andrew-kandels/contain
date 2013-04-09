<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleEntity;
use ContainTest\Entity\SampleMultiTypeEntity;
use ContainTest\Entity\SampleMultiEntityEntity;
use ContainTest\Entity\SampleChildEntity;

class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    public function setUp()
    {
        $this->entity = new SampleEntity();
    }

    public function testConstructWithArray()
    {
        $entity = new SampleEntity($values = array(
            'firstName' => 'Andrew',
            'child'     => array(),
        ));
        $this->assertEquals($values, $entity->export());
    }

    public function testUnsetSubEntityStillReturnsObject()
    {
        $entity = new SampleEntity();
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $entity->getChild());
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Contain\Entity\EntityInterface', $this->entity);
    }

    public function testConstructWithEntity()
    {
        $entity = new SampleEntity($this->entity);
        $this->assertEquals($this->entity->export(), $entity->export());
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
        $arr = $this->entity->toArray();
        $this->assertTrue(isset($arr['child']));

        $this->assertEquals($arr['child']->export(), $this->entity->getChild()->export());

        $this->entity->setFirstName('Andrew');
        $arr = $this->entity->toArray();
        unset($arr['child']);
        $this->assertEquals(array('firstName' => 'Andrew'), $arr);
    }

    public function testFromArray()
    {
        $arr = $this->entity->toArray();
        unset($arr['child']);
        $this->assertEquals(array(), $arr);
        $this->entity->fromArray(array(
            'firstName' => 'Andrew',
        ));

        $arr = $this->entity->toArray();
        unset($arr['child']);
        $this->assertEquals(array('firstName' => 'Andrew'), $arr);
    }

    public function testExport()
    {
        $this->assertEquals(array(), $this->entity->export());
        $this->assertEquals(
            array(
                'firstName' => $this->entity->property('firstName')->getType()->getUnsetValue(),
                'child'     => null,
            ),
            $this->entity->export(null, true)
        );
        $this->entity->fromArray(array('firstName' => 'Andrew'));
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
        $this->assertFalse($this->entity->property('invalidPropertyName'));
    }

    public function testOnEventGetter()
    {
        $this->assertEquals('old', $this->entity->onEventGetter('firstName', 'old'));
    }

    public function testOnEventGetterWithReplace()
    {
        $this->entity->attach('property.get', function ($e) {
            $e->setParam('value', 'newvalue');
        });

        $this->assertEquals('newvalue', $this->entity->onEventGetter('firstName', 'old'));
    }

    public function testOnEventSetter()
    {
        $this->entity->attach('property.set', function ($e) {
            $e->setParam('value', 'newvalue');
        });

        $this->assertEquals(
            'newvalue',
            $this->entity->onEventSetter(
                'firstName',
                'old',
                'new'
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
                'new'
            )
        );
    }

    public function testChildEntityInstantiated()
    {
        $this->assertEquals(array(), $this->entity->export('child'));
        $this->assertEquals(array('child' => null), $this->entity->export('child', true));
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

    public function testGetProperty()
    {
        $this->assertEquals(
            $this->entity->type('firstName')->getUnsetValue(),
            $this->entity->getFirstName()
        );
    }

    public function testSetProperty()
    {
        $this->assertEquals(
            $name = 'Andrew',
            $this->entity->setFirstName($name)->getFirstName()
        );
    }

    public function testHasProperty()
    {
        $this->assertFalse($this->entity->hasFirstName());
        $this->entity->setFirstName('Andrew');
        $this->assertTrue($this->entity->hasFirstName());
    }

    public function testInvalidMethod()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '\'invalidMethod\' is not a valid method for ContainTest\Entity\SampleEntity.'
        );

        $this->entity->invalidMethod();
    }

    public function testInvalidProperty()
    {
         $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '\'invalidProperty\' is not a valid property of ContainTest\Entity\SampleEntity.'
        );

        $this->entity->setInvalidProperty();
    }

    public function testSettingBooleanFalseShowsDirty()
    {
        $entity = new SampleMultiTypeEntity();
        $entity->setBoolean(true)->clean();
        $this->assertFalse($entity->property('boolean')->isDirty());
        $entity->setBoolean(false);
        $this->assertTrue($entity->property('boolean')->isDirty());
        $this->assertFalse($entity->property('boolean')->isEmpty());
        $this->assertFalse($entity->property('boolean')->isUnset());
    }

    public function testCleanParentCleansChildren()
    {
        $entity = new SampleMultiTypeEntity();
        $entity->getEntity()->setFirstName('hi');
        $this->assertEquals(array('entity'), $entity->dirty());
        $entity->clean();
        $this->assertEquals(array(), $entity->dirty());
        $this->assertEquals(array(), $entity->getEntity()->dirty());
    }

    public function testIsValidWhenItIs()
    {
        $entity = new SampleMultiTypeEntity(array('string' => '1234'));
        $this->assertTrue($entity->isValid());
    }

    public function testIsValid()
    {
        $entity = new SampleMultiTypeEntity();

        $this->assertEquals(array(), $entity->messages());
        $this->assertFalse($entity->setString('thisvalueiswaytoolongandwillfail')->isValid());
        $this->assertEquals(array('string' => array('notDigits' => 'The input must contain only digits')), $entity->messages());
    }

    public function testIsValidString()
    {
        $entity = new SampleMultiTypeEntity();

        $this->assertFalse($entity->setString('thisvalueiswaytoolongandwillfail')->isValid('string'));
        $this->assertEquals(array('string' => array('notDigits' => 'The input must contain only digits')), $entity->messages());
    }

    public function testIsValidArray()
    {
        $entity = new SampleMultiTypeEntity();

        $this->assertFalse($entity->setString('thisvalueiswaytoolongandwillfail')->isValid(array('string', 'test')));
        $this->assertEquals(array('string' => array('notDigits' => 'The input must contain only digits')), $entity->messages());
    }

    public function testIsValidNotSpecified()
    {
        $entity = new SampleMultiTypeEntity();

        $this->assertTrue($entity->setString('thisvalueiswaytoolongandwillfail')->isValid(array('test')));
    }

    public function testIsValidWhenFiltered()
    {
        $entity = new SampleMultiTypeEntity(array('string' => '   1234   '));
        $this->assertEquals('   1234   ', $entity->getString());
        $this->assertTrue($entity->isValid());
        $this->assertEquals('1234', $entity->getString());
    }

    public function testIsPersisted()
    {
        $entity = new SampleMultiTypeEntity();
        $this->assertFalse($entity->isPersisted());
    }

    public function testPersisted()
    {
        $entity = new SampleMultiTypeEntity();
        $this->assertFalse($entity->isPersisted());
        $entity->persisted();
        $this->assertTrue($entity->isPersisted());
        $entity->persisted(false);
        $this->assertFalse($entity->isPersisted());
        $entity->persisted(true);
        $this->assertTrue($entity->isPersisted());
    }

    public function testClearingSubEntityPropertyDirtiesParent()
    {
        $entity = new SampleEntity();
        $entity->setExtendedProperty('fuck', true);
        $entity->fromArray(array(
            'child' => array('firstName' => 'Mr.'),
        ));
        $entity->clean();

        $entity->getChild()->clear('firstName');
        $this->assertEquals(array('firstName'), $entity->getChild()->dirty());
        $this->assertEquals(array('child'), $entity->dirty());
    }

    public function testSettingUnsetSubEntityPropertyDirtiesParent()
    {
        $entity = new SampleEntity();
        $entity->fromArray(array(
            'child' => array('firstName' => 'Mr.'),
        ));
        $entity->clean();
        $entity->getChild()->setFirstName(null);
        $this->assertEquals(array('child'), $entity->dirty());
        $this->assertEquals(array('firstName'), $entity->getChild()->dirty());
    }

    public function testSettingEmptySubEntityPropertyDirtiesParent()
    {
        $entity = new SampleEntity();
        $entity->fromArray(array(
            'child' => array('firstName' => 'Mr.'),
        ));
        $entity->clean();
        $entity->getChild()->setFirstName(false);
        $this->assertEquals(array('child'), $entity->dirty());
        $this->assertEquals(array('firstName'), $entity->getChild()->dirty());
    }

    public function testSettingEmptyStringSubEntityPropertyDirtiesParent()
    {
        $entity = new SampleEntity();
        $entity->fromArray(array(
            'child' => array('firstName' => 'Mr.'),
        ));
        $entity->clean();
        $entity->getChild()->setFirstName('');
        $this->assertEquals(array('child'), $entity->dirty());
        $this->assertEquals(array('firstName'), $entity->getChild()->dirty());
    }

    public function testMarkingDirtyStringSubEntityPropertyDirtiesParent()
    {
        $entity = new SampleEntity();
        $entity->fromArray(array(
            'child' => array('firstName' => 'Mr.'),
        ));
        $entity->clean();
        $entity->getChild()->markDirty('firstName');
        $this->assertEquals(array('child'), $entity->dirty());
        $this->assertEquals(array('firstName'), $entity->getChild()->dirty());
    }

    public function testChangeTriggersEventsInOrder()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $rs->order = array();
        $entity->attach('change', function($e) use ($test, $entity, $rs) {
            $test->assertInstanceOf('Contain\Event', $e);
            $test->assertSame($entity, $e->getTarget());
            $rs->order[] = 'second';
        }, 100);

        $entity->attach('change', function($e) use ($test, $entity, $rs) {
            $test->assertInstanceOf('Contain\Event', $e);
            $test->assertSame($entity, $e->getTarget());
            $rs->order[] = 'first';
        }, 200);

        $entity->setFirstName('Mr.');
        $this->assertEquals(array('first', 'second'), $rs->order);

        $entity->clean('firstName');
        $this->assertEquals(array('first', 'second', 'first', 'second'), $rs->order);

        $entity->clear('firstName');
        $this->assertEquals(array('first', 'second', 'first', 'second', 'first', 'second'), $rs->order);
    }

    public function testCleanTriggersEvents()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $entity->attach('clean', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);

        $entity->clean();
        $this->assertTrue($rs->called);
    }

    public function testDirtyTriggersEvents()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $entity->attach('dirty', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);

        $entity->markDirty('firstName');
        $this->assertTrue($rs->called);
    }

    public function testChildFiresParentChangeEvent()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $entity->attach('change', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);

        $entity->getChild()->setFirstName('Mr.');
        $this->assertTrue($rs->called);
    }

    public function testChildFiresParentDirtyEvent()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $entity->attach('dirty', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);

        $entity->getChild()->markDirty('firstName');
        $this->assertTrue($rs->called);
    }

    public function testChildFiresParentCleanEvent()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $entity->attach('clean', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);

        $entity->getChild()->clean('firstName');
        $this->assertTrue($rs->called);
    }

    public function testClearListenersClearsEvent()
    {
        $entity = new SampleEntity();
        $test = $this;
        $rs = new \stdclass();
        $rs->called = false;
        $entity->attach('change', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);
        $entity->clearListeners('change');

        $entity->setFirstName('Andrew');
        $this->assertFalse($rs->called);
    }

    public function testResetClearsEverything()
    {
        $entity = new SampleEntity();
        $entity->setExtendedProperty('test', true);
        $test = $this;
        $rs = new \stdclass();
        $rs->called = false;
        $entity->setFirstName('Mr.');
        $entity->attach('change', function($e) use ($test, $rs) {
            $rs->called = true;
        }, 100);
        $entity->reset();

        $this->assertEquals(array(), $entity->dirty());

        $this->assertNull($entity->getFirstName());
        $entity->setFirstName('Mr.');
        $this->assertFalse($rs->called);
        $this->assertNull($entity->getExtendedProperty('test'));
    }

    public function testPutAndAt()
    {
        $entity = new SampleMultiTypeEntity();
        $entity->setList(array(3, 2, 3));
        $entity->put('list', 0, 1);
        $this->assertEquals(array(1, 2, 3), $entity->getList());
        $this->assertEquals(1, $entity->at('list', 0));
    }

    public function testSetSubDocumentEmpty()
    {
        $entity = new SampleMultiTypeEntity();
        $this->assertEquals(array(), $entity->dirty());
        $entity->property('entity')->setDirty();
        $this->assertEquals(array('entity'), $entity->dirty());
    }
}
