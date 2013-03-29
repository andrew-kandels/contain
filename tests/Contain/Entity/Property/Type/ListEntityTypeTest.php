<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleListEntityEntity;

class ListEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    public function setUp()
    {
        $this->entity = new SampleListEntityEntity();
    }

    public function testSettingListCreatesCursor()
    {
        $this->entity->setListEntity(array(
            array('firstName' => 'Mr.'),
            array('firstName' => 'Mrs.'),
        ));

        $this->assertInstanceOf('ContainMapper\Cursor', $this->entity->getListEntity());
    }

    public function testListEntityExport()
    {
        $values = array(
            array('firstName' => 'Mr.'),
            array('firstName' => 'Mrs.'),
        );

        $this->entity->setListEntity($values);

        $this->assertEquals(
            $values,
            $this->entity->getListEntity()->export()
        );
    }

    public function testListEntityToArray()
    {
        $values = array(
            array('firstName' => 'Mr.'),
            array('firstName' => 'Mrs.'),
        );

        $this->entity->setListEntity($values);

        foreach ($this->entity->getListEntity()->toArray() as $index => $item) {
            $this->assertEquals($values[$index], $item->export());
        }
    }

    public function testListEntity()
    {
        $values = array(
            array('firstName' => 'Mr.'),
            array('firstName' => 'Mrs.'),
        );

        $this->entity->setListEntity($values);

        foreach ($this->entity->getListEntity() as $index => $item) {
            $this->assertEquals($values[$index], $item->export());
        }
    }

    public function testUnsetValue()
    {
        $this->assertEquals($this->entity->type('listEntity')->getUnsetValue(), $this->entity->getListEntity());
        $this->assertEquals(array(), $this->entity->type('listEntity')->getUnsetValue());
    }

    public function testEmptyValue()
    {
        $this->entity->property('listEntity')->setEmpty();
        $this->assertEquals($this->entity->type('listEntity')->getEmptyValue(), $this->entity->getListEntity());
        $this->assertEquals(array(), $this->entity->type('listEntity')->getEmptyValue());
    }

    public function testParseEmptyGivesUnsetValue()
    {
        $this->assertEquals(
            $this->entity->type('listEntity')->getUnsetValue(),
            $this->entity->setListEntity(false)->getListEntity()
        );
    }

    public function testIteratingDifferentValuesCreatesDifferentEntities()
    {
        $this->entity->setListEntity($values = array(
            array('firstName' => 'Mr.'),
            array('firstName' => 'Mrs.'),
        ));

        foreach ($this->entity->getListEntity() as $index => $item) {
            $this->assertEquals($values[$index]['firstName'], $item->getFirstName());
        }
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
