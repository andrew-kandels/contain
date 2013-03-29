<?php
namespace ContainTest\Performance;

use ContainTest\Entity\SampleEntity;
use ContainTest\Entity\SampleMultiTypeEntity;

require(__DIR__ . '/../Bootstrap.php');

class Suite
{
    protected $iterations = 500;

    public function testFromArrayHydration()
    {
        $entity = new SampleMultiTypeEntity();

        $this->start(__METHOD__);

        for ($i = 0; $i < $this->iterations; $i++) {
            $entity->fromArray(array(
                'string' => 'StringValue',
                'entity' => array(
                    'firstName' => 'Mr.',
                ),
                'boolean' => true,
                'dateTime' => '2013-01-01 00:00:00',
                'date' => '2013-01-01',
                'double' => 1.1,
                'enum' => 'one',
                'integer' => 1,
                'list' => array(1, 2, 3),
                'listEntity' => array(
                    array('firstName' => 'Mr.'),
                    array('firstName' => 'Mrs.'),
                ),
                'mixed' => 'test',
            ));
        }

        $this->end();
    }

    public function testSetterHydration()
    {
        $entity = new SampleMultiTypeEntity();

        $this->start(__METHOD__);

        for ($i = 0; $i < $this->iterations; $i++) {
            $entity->setString('StringValue');
            $entity->setEntity(array(
                'firstName' => 'Mr.',
            ));
            $entity->setBoolean(true);
            $entity->setDateTime('2013-01-01 00:00:00');
            $entity->setDate('2013-01-01');
            $entity->setDouble(1.1);
            $entity->setEnum('one');
            $entity->setInteger(1);
            $entity->setList(array(1, 2, 3));
            $entity->setListEntity(array(
                array('firstName' => 'Mr.'),
                array('firstName' => 'Mrs.'),
            ));
            $entity->setMixed('test');
        }

        $this->end();
    }

    public function testGetters()
    {
        $entity = new SampleMultiTypeEntity();

        $this->start(__METHOD__);

        for ($i = 0; $i < $this->iterations; $i++) {
            $entity->getString();
            $entity->getEntity();
            $entity->getBoolean();
            $entity->getDateTime();
            $entity->getDate();
            $entity->getDouble();
            $entity->getEnum();
            $entity->getInteger();
            $entity->getList();
            $entity->getListEntity();
            $entity->getMixed();
        }

        $this->end();
    }

    public function testToArray()
    {
        $entity = new SampleMultiTypeEntity();

        $this->start(__METHOD__);

        for ($i = 0; $i < $this->iterations; $i++) {
            $entity->toArray();
        }

        $this->end();
    }

    public function testExport()
    {
        $entity = new SampleMultiTypeEntity();

        $this->start(__METHOD__);

        for ($i = 0; $i < $this->iterations; $i++) {
            $entity->export();
        }

        $this->end();
    }

    public function testOneHundredEntities()
    {
        $this->start(__METHOD__);

        $entities = array();
        for ($i = 0; $i < 100; $i++) {
            $entities[] = $this->getHydratedEntity();
        }

        $this->end();
    }

    protected function getHydratedEntity()
    {
        return new SampleMultiTypeEntity(array(
            'string' => 'StringValue',
            'entity' => array(
                'firstName' => 'Mr.',
            ),
            'boolean' => true,
            'dateTime' => '2013-01-01 00:00:00',
            'date' => '2013-01-01',
            'double' => 1.1,
            'enum' => 'one',
            'integer' => 1,
            'list' => array(1, 2, 3),
            'listEntity' => array(
                array('firstName' => 'Mr.'),
                array('firstName' => 'Mrs.'),
            ),
            'mixed' => 'test',
        ));
    }

    protected function start($name)
    {
        $this->memory    = memory_get_usage();
        $this->startTime = microtime(true);
        printf('%-60s ... ', $name);
    }

    protected function end()
    {
        printf("[ Ok: %.4f, mem: %s ]\n", 
            microtime(true) - $this->startTime, 
            $this->memSize(memory_get_usage() - $this->memory)
        );
    }

    protected function memSize($bytes)
    {
        if ($bytes <= 0) {
            return '';
        }

        if ($bytes < 1024) {
            return sprintf('%s byte%s',
                number_format($bytes, 0),
                $bytes != 1 ? 's' : ''
            );
        }

        $ranges = array('KB', 'MB', 'GB', 'TB', 'PB');
        foreach ($ranges as $range) {
            $bytes = (int) $bytes / 1024;
            if ($bytes < 1024) {
                return sprintf('%s %s%s',
                    number_format($bytes, 0),
                    $range,
                    $bytes != 1 ? 's' : ''
                );
            }
        }

        return 'huge';
    }
}

$test = new Suite();
$test->testFromArrayHydration();
$test->testSetterHydration();
$test->testGetters();
$test->testToArray();
$test->testExport();
$test->testOneHundredEntities();
