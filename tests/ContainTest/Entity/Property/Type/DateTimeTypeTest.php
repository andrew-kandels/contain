<?php
namespace ContainTest\Entity;

use ContainTest\Entity\SampleMultiTypeEntity;
use DateTime;

class DateTimeTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    public function setUp()
    {
        // silence errors if they're php isn't configured with a timezone
        date_default_timezone_set('America/Chicago');

        $entity = new SampleMultiTypeEntity();
        $property = $entity->property('dateTime');
        $this->type = $property->getType();
    }

    public function testParseUnset()
    {
        $this->assertNull($this->type->parse(false));
    }

    public function testParseDateTime()
    {
        $when = new DateTime($val = '2012-01-01 00:00:00');
        $this->assertInstanceOf('DateTime', $stored = $this->type->parse($when));
        $this->assertNotSame($when, $stored);
        $this->assertEquals($when->format($fmt = 'Y-m-d H:i:s'), $stored->format($fmt));
    }

    public function testParseMongoDate()
    {
        if (class_exists('MongoDate')) {
            define('FUCK', true);
            $this->assertInstanceOf('DateTime', $this->type->parse(new \MongoDate()));
        }
    }

    public function testParseString()
    {
        $this->assertInstanceOf('DateTime', $stored = $this->type->parse($value = '2012-01-01 00:00:00'));
        $this->assertEquals($value, $stored->format('Y-m-d H:i:s'));
    }

    public function testParseInteger()
    {
        $when = new DateTime();
        $this->assertInstanceOf('DateTime', $stored = $this->type->parse($value = $when->getTimestamp()));
        $this->assertEquals($when->getTimestamp(), $stored->getTimestamp());
    }

    public function testParseInvalid()
    {
        $this->setExpectedException(
            'Contain\Entity\Exception\InvalidArgumentException',
            '$value is invalid for date type'
        );

        $this->type->parse(new \stdclass());
    }

    public function testGetEmptyValue()
    {
        $this->assertFalse($this->type->getEmptyValue());
    }

    public function testGetUnsetValue()
    {
        $this->assertNull($this->type->getUnsetValue());
    }

    public function testExport()
    {
        $this->assertEquals($value = '2012-01-01 00:00:00', $this->type->export($value));
        $this->assertNull($this->type->export(false));
    }

    public function testDateFormatOption()
    {
        $this->type->setOption('dateFormat', 'Y');
        $this->assertEquals(date('Y'), $this->type->export(date('Y-m-d H:i:s')));
    }

    public function getValidators()
    {
        $this->assertEquals(array(), $this->type->getValidators());
    }
}
