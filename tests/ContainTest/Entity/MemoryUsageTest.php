<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleEntity;
use ContainTest\Entity\SampleMultiTypeEntity;
use ContainTest\Entity\SampleMultiEntityEntity;
use ContainTest\Entity\SampleChildEntity;

class MemoryUsage extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SampleChildEntity::$instanceCount = 0;
        SampleChildEntity::$initCount = 0;
    }

    public function testNewEntityInitsAndInstantiates()
    {
        $entity = new SampleChildEntity();
        $this->assertEquals(1, SampleChildEntity::$initCount);
        $this->assertEquals(1, SampleChildEntity::$instanceCount);

        $entity = null;
        $this->assertEquals(0, SampleChildEntity::$instanceCount);
    }

    public function testCreateEntityWithSubEntityDoesntInstantiateSubEntityObjects()
    {
        $entity = new SampleMultiEntityEntity();
        $this->assertEquals(0, SampleChildEntity::$initCount);
        $this->assertEquals(0, SampleChildEntity::$instanceCount);

        $child1 = $entity->getEntity1();
        $child2 = $entity->getEntity2();
        $this->assertInstanceOf('ContainTest\Entity\SampleChildEntity', $child1);
        $this->assertEquals(2, SampleChildEntity::$instanceCount);
        $this->assertEquals(2, SampleChildEntity::$initCount);

        $child1 = $child2 = $entity = null;
        $this->assertEquals(2, SampleChildEntity::$instanceCount);
    }
}
