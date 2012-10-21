<?php
namespace ContainTest;

use ContainTest\SampleQuery;

class AbstractQueryTest extends \PHPUnit_Framework_TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new SampleQuery();
    }

    public function testLimit()
    {
        $this->assertNull($this->query->getLimit());
        $this->assertEquals(1, $this->query->setDefaultLimit(1)->getLimit());
        $this->assertEquals(2, $this->query->limit(2)->getLimit());
    }

    public function testSkip()
    {
        $this->assertNull($this->query->getSkip());
        $this->assertEquals(1, $this->query->skip(1)->getSkip());
    }

    public function testSort()
    {
        $this->assertNull($this->query->getSort());
        $this->assertEquals(array('one' => 'two'), $this->query->setDefaultSort(array('one' => 'two'))->getSort());
        $this->assertEquals(array('two' => 'three'), $this->query->sort(array('two' => 'three'))->getSort());
    }

    public function testOptions()
    {
        $this->query->setOption('one', 'two');
        $this->query->setOptions(array('two' => 'three'));
        $this->assertEquals(
            array(
                'one' => 'two',
                'two' => 'three',
                'three' => 'four',
            ),
            $this->query->getOptions(array(
                'one' => 'ignored',
                'two' => 'ignored',
                'three' => 'four',
            ))
        );
    }

    public function testClear()
    {
        $this->query->setOption('one', 'two')->limit(1)->skip(1)->sort(array('one' => 'two'))->clear();
        $this->assertNull($this->query->getLimit());
        $this->assertNull($this->query->getSort());
        $this->assertNull($this->query->getSkip());
        $this->assertEquals(array('one' => 'invalid'), $this->query->getOptions(array('one' => 'invalid')));
    }

    public function testPropertiesString()
    {
        $this->assertEquals(array('one'), $this->query->properties('one')->getProperties());
    }

    public function testPropertiesArray()
    {
        $this->assertEquals(array('one'), $this->query->properties(array('one'))->getProperties());
    }

    public function testPropertiesInvalid()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            ''
        );
        $this->query->properties(new \stdclass());
    }
}
