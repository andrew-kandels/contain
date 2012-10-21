<?php
namespace ContainTest\Service;

use ContainTest\Service\SampleService;
use ContainTest\SampleQuery;

class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new SampleService();
    }

    public function testGetEventManager()
    {
        $this->assertInstanceOf('Zend\EventManager\EventManager', $this->service->getEventManager());
    }

    public function testSetEventManager()
    {
        $em = new \Zend\EventManager\EventManager();
        $this->assertSame($em, $this->service->setEventManager($em)->getEventManager());
    }

    public function testPrepare()
    {
        $query = new SampleQuery();
        $this->service
            ->limit(1)
            ->skip(1)
            ->sort(array('one' => 'two'))
            ->setOptions(array('test' => 'orig'))
            ->properties('one')
            ->prepare($query);

        $this->assertEquals(1, $query->getLimit());
        $this->assertEquals(1, $query->getSkip());
        $this->assertEquals(array('one'), $query->getProperties());
        $this->assertEquals(array('one' => 'two'), $query->getSort());

        $this->assertNull($this->service->getLimit());
        $this->assertNull($this->service->getSkip());
        $this->assertNull($this->service->getSort());
    }
}
