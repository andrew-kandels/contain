<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;
use DateTime;

class DateTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        // silence errors if they're php isn't configured with a timezone
        date_default_timezone_set('America/Chicago');

        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('date');
        $this->type = $property->getType();
    }

    public function testExport()
    {
        $this->assertEquals($value = '2012-01-01', $this->type->export($value));
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
